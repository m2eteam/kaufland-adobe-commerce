<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Instruction\SynchronizationTemplate\Checker;

use M2E\Kaufland\Model\Magento\Product\ChangeAttributeTracker;
use M2E\Kaufland\Model\Product;
use M2E\Kaufland\Model\Product\Action\Configurator;
use M2E\Kaufland\Model\Template\ChangeProcessor\ChangeProcessorAbstract;
use M2E\Kaufland\Model\Template\Description as DescriptionPolicy;
use M2E\Kaufland\Model\Template\Synchronization\ChangeProcessor as SyncChangeProcessor;

class Active extends \M2E\Kaufland\Model\Instruction\SynchronizationTemplate\Checker\AbstractChecker
{
    private \M2E\Kaufland\Model\ScheduledAction\CreateService $scheduledActionCreate;
    private \M2E\Kaufland\Model\ScheduledAction\Repository $scheduledActionRepository;
    private \M2E\Kaufland\Model\Product\ActionCalculator $actionCalculator;

    public function __construct(
        \M2E\Kaufland\Model\Product\ActionCalculator $actionCalculator,
        \M2E\Kaufland\Model\ScheduledAction\CreateService $scheduledActionCreate,
        \M2E\Kaufland\Model\ScheduledAction\Repository $scheduledActionRepository,
        \M2E\Kaufland\Model\Instruction\Handler\Input $input
    ) {
        parent::__construct($input);
        $this->scheduledActionCreate = $scheduledActionCreate;
        $this->scheduledActionRepository = $scheduledActionRepository;
        $this->actionCalculator = $actionCalculator;
    }

    /**
     * @return bool
     */
    public function isAllowed(): bool
    {
        if (!parent::isAllowed()) {
            return false;
        }

        if (
            !$this->input->hasInstructionWithTypes($this->getStopInstructionTypes())
            && !$this->input->hasInstructionWithTypes($this->getUnitReviseInstructionTypes())
            && !$this->input->hasInstructionWithTypes($this->getProductReviseInstructionTypes())
        ) {
            return false;
        }

        $listingProduct = $this->input->getListingProduct();

        if ($listingProduct->isIncomplete()) {
            return false;
        }

        return true;
    }

    public function process(): void
    {
        $product = $this->getInput()->getListingProduct();

        if (
            $product->isRevisable()
            || $product->isStoppable()
        ) {
            $actionResult = $this->processUnit($product);
            if ($actionResult->isActionStop()) {
                return;
            }
        }

        if ($product->isRevisableAsProduct()) {
            $this->processProduct($product);
        }
    }

    private function processUnit(\M2E\Kaufland\Model\Product $product): Product\Action
    {
        $calculateResult = $this->actionCalculator->calculateToReviseOrStopUnit($product);

        if (
            !$calculateResult->isActionStop()
            && !$calculateResult->isActionRevise()
        ) {
            $this->tryRemoveExistScheduledAction();

            return $calculateResult;
        }

        if ($calculateResult->isActionStop()) {
            $this->returnWithStopAction();

            return $calculateResult;
        }

        if (
            $this->getInput()->getScheduledAction() !== null
            && $this->getInput()->getScheduledAction()->isActionTypeRevise()
            && $this->getInput()->getScheduledAction()->isForce()
        ) {
            return $calculateResult;
        }

        $this->createUnitReviseScheduledAction(
            $product,
            $calculateResult->getConfigurator(),
        );

        return $calculateResult;
    }

    private function createUnitReviseScheduledAction(
        Product $product,
        Configurator $configurator
    ): void {
        $this->scheduledActionCreate->create(
            $product,
            \M2E\Kaufland\Model\Product::ACTION_REVISE_UNIT,
            \M2E\Kaufland\Model\Product::STATUS_CHANGER_SYNCH,
            [],
            $configurator->getAllowedDataTypes(),
            false,
            $configurator,
        );
    }

    private function returnWithStopAction(): void
    {
        $scheduledAction = $this->getInput()->getScheduledAction();
        if ($scheduledAction === null) {
            $this->createStopScheduledAction($this->getInput()->getListingProduct());

            return;
        }

        if ($scheduledAction->isActionTypeStop()) {
            return;
        }

        $this->scheduledActionRepository->remove($scheduledAction);

        $this->createStopScheduledAction($this->getInput()->getListingProduct());
    }

    private function createStopScheduledAction(Product $product): void
    {
        $this->scheduledActionCreate->create(
            $product,
            \M2E\Kaufland\Model\Product::ACTION_STOP_UNIT,
            \M2E\Kaufland\Model\Product::STATUS_CHANGER_SYNCH,
            [],
        );
    }

    private function processProduct(\M2E\Kaufland\Model\Product $product): void
    {
        $calculateResult = $this->actionCalculator->calculateToReviseProduct(
            $product,
            $this->getInput()->hasInstructionWithTypes($this->getReviseTitleInstructionTypes()),
            $this->getInput()->hasInstructionWithTypes($this->getReviseDescriptionInstructionTypes()),
            $this->getInput()->hasInstructionWithTypes($this->getReviseImagesInstructionTypes()),
            $this->getInput()->hasInstructionWithTypes($this->getReviseCategoriesInstructionTypes()),
        );

        if (!$calculateResult->isActionReviseProduct()) {
            $this->tryRemoveExistScheduledAction();

            return;
        }

        if (
            $this->getInput()->getScheduledAction() !== null
            && $this->getInput()->getScheduledAction()->isActionTypeReviseProduct()
            && $this->getInput()->getScheduledAction()->isForce()
        ) {
            return;
        }

        $this->createProductReviseScheduledAction(
            $product,
            $calculateResult->getConfigurator(),
        );
    }

    private function createProductReviseScheduledAction(
        Product $product,
        Configurator $configurator
    ): void {
        $this->scheduledActionCreate->create(
            $product,
            \M2E\Kaufland\Model\Product::ACTION_REVISE_PRODUCT,
            \M2E\Kaufland\Model\Product::STATUS_CHANGER_SYNCH,
            [],
            $configurator->getAllowedDataTypes(),
            false,
            $configurator
        );
    }

    private function tryRemoveExistScheduledAction(): void
    {
        if ($this->getInput()->getScheduledAction() === null) {
            return;
        }

        if (
            $this->getInput()->getScheduledAction()->isActionTypeStop()
            && $this->getInput()->getScheduledAction()->isForce()
        ) {
            return;
        }

        $this->scheduledActionRepository->remove($this->getInput()->getScheduledAction());
    }

    // ----------------------------------------

    private function getUnitReviseInstructionTypes(): array
    {
        return array_unique(
            array_merge(
                $this->getReviseQtyInstructionTypes(),
                $this->getRevisePriceInstructionTypes(),
                $this->getReviseShippingInstructionTypes(),
            ),
        );
    }

    private function getReviseQtyInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
            ChangeProcessorAbstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_ENABLED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_DISABLED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_QTY_SETTINGS_CHANGED,
            Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\Kaufland\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\Kaufland\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_STATUS_CHANGED,
            \M2E\Kaufland\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_QTY_CHANGED,
            \M2E\Kaufland\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
        ];
    }

    private function getReviseShippingInstructionTypes(): array
    {
        return [
            ChangeProcessorAbstract::INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED,
        ];
    }

    private function getRevisePriceInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRICE_DATA_CHANGED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_ENABLED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_PRICE_DISABLED,
            Product::INSTRUCTION_TYPE_CHANNEL_PRICE_CHANGED,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\Kaufland\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\Kaufland\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRICE_CHANGED,
            \M2E\Kaufland\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
        ];
    }

    // ----------------------------------------

    /**
     * @return array
     */
    private function getStopInstructionTypes(): array
    {
        return [
            ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
            SyncChangeProcessor::INSTRUCTION_TYPE_STOP_MODE_ENABLED,
            SyncChangeProcessor::INSTRUCTION_TYPE_STOP_MODE_DISABLED,
            SyncChangeProcessor::INSTRUCTION_TYPE_STOP_SETTINGS_CHANGED,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_REMAP_FROM_LISTING,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
            ChangeProcessorAbstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
            \M2E\Kaufland\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\Kaufland\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_STATUS_CHANGED,
            \M2E\Kaufland\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_QTY_CHANGED,
            \M2E\Kaufland\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
        ];
    }

    // ----------------------------------------

    private function getProductReviseInstructionTypes(): array
    {
        return array_unique(
            array_merge(
                $this->getReviseTitleInstructionTypes(),
                $this->getReviseDescriptionInstructionTypes(),
                $this->getReviseImagesInstructionTypes(),
                $this->getReviseCategoriesInstructionTypes(),
            ),
        );
    }

    protected function getReviseTitleInstructionTypes(): array
    {
        return [
            Product::INSTRUCTION_TYPE_PRODUCT_ACTIVE,
            ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
            ChangeAttributeTracker::INSTRUCTION_TYPE_TITLE_DATA_CHANGED,
            \M2E\Kaufland\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_TITLE_DATA_CHANGED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_TITLE_ENABLED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_TITLE_DISABLED,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\Kaufland\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\Kaufland\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
        ];
    }

    protected function getReviseDescriptionInstructionTypes(): array
    {
        return [
            Product::INSTRUCTION_TYPE_PRODUCT_ACTIVE,
            ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
            ChangeAttributeTracker::INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED,
            \M2E\Kaufland\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_DESCRIPTION_ENABLED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_DESCRIPTION_DISABLED,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\Kaufland\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\Kaufland\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
            DescriptionPolicy::INSTRUCTION_TYPE_MAGENTO_STATIC_BLOCK_IN_DESCRIPTION_CHANGED,
        ];
    }

    protected function getReviseImagesInstructionTypes(): array
    {
        return [
            Product::INSTRUCTION_TYPE_PRODUCT_ACTIVE,
            ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
            ChangeAttributeTracker::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
            \M2E\Kaufland\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_IMAGES_ENABLED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_IMAGES_DISABLED,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\Kaufland\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\Kaufland\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
        ];
    }

    protected function getReviseCategoriesInstructionTypes(): array
    {
        return [
            Product::INSTRUCTION_TYPE_PRODUCT_ACTIVE,
            ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
            ChangeAttributeTracker::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
            \M2E\Kaufland\Model\Template\ChangeProcessorAbstract::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_CATEGORIES_ENABLED,
            SyncChangeProcessor::INSTRUCTION_TYPE_REVISE_CATEGORIES_DISABLED,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
            \M2E\Kaufland\PublicServices\Product\SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            \M2E\Kaufland\Model\Product\InspectDirectChanges::INSTRUCTION_TYPE,
        ];
    }
}
