<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Instruction\SynchronizationTemplate\Checker;

use M2E\Kaufland\Model\Product;
use M2E\Kaufland\Model\Template\Synchronization\ChangeProcessorAbstract as SyncChangeProcessorAbstract;
use M2E\Kaufland\Model\Kaufland\Template\ChangeProcessor\ChangeProcessorAbstract;

class UnitInactive extends \M2E\Kaufland\Model\Instruction\SynchronizationTemplate\Checker\AbstractChecker
{
    private static array $relistInstructionTypes = [
        \M2E\Kaufland\Model\Magento\Product\ChangeAttributeTracker::INSTRUCTION_TYPE_PRODUCT_DATA_POTENTIALLY_CHANGED,
        SyncChangeProcessorAbstract::INSTRUCTION_TYPE_RELIST_MODE_ENABLED,
        SyncChangeProcessorAbstract::INSTRUCTION_TYPE_RELIST_MODE_DISABLED,
        SyncChangeProcessorAbstract::INSTRUCTION_TYPE_RELIST_SETTINGS_CHANGED,
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

    private \M2E\Kaufland\Model\ScheduledAction\CreateService $scheduledActionCreate;
    private \M2E\Kaufland\Model\ScheduledAction\Repository $scheduledActionRepository;
    /** @var \M2E\Kaufland\Model\Product\ActionCalculator */
    private Product\ActionCalculator $actionCalculator;

    public function __construct(
        \M2E\Kaufland\Model\ScheduledAction\CreateService $scheduledActionCreate,
        \M2E\Kaufland\Model\ScheduledAction\Repository $scheduledActionRepository,
        \M2E\Kaufland\Model\Product\ActionCalculator $actionCalculator,
        \M2E\Kaufland\Model\Instruction\Handler\Input $input
    ) {
        parent::__construct($input);
        $this->scheduledActionCreate = $scheduledActionCreate;
        $this->scheduledActionRepository = $scheduledActionRepository;
        $this->actionCalculator = $actionCalculator;
    }

    public function isAllowed(): bool
    {
        if (!parent::isAllowed()) {
            return false;
        }

        if (!$this->input->hasInstructionWithTypes(self::$relistInstructionTypes)) {
            return false;
        }

        $listingProduct = $this->input->getListingProduct();

        if ($listingProduct->isIncomplete()) {
            return false;
        }

        if (!$listingProduct->isRelistable()) {
            return false;
        }

        return true;
    }

    public function process(): void
    {
        $product = $this->getInput()->getListingProduct();

        $calculateResult = $this->actionCalculator->calculateToRelist($product, Product::STATUS_CHANGER_SYNCH);
        if (!$calculateResult->isActionRelist()) {
            $this->tryRemoveExistScheduledAction();

            return;
        }

        if (
            $this->getInput()->getScheduledAction() !== null
            && $this->getInput()->getScheduledAction()->isActionTypeRelist()
        ) {
            return;
        }

        $this->scheduledActionCreate->create(
            $this->getInput()->getListingProduct(),
            \M2E\Kaufland\Model\Product::ACTION_RELIST_UNIT,
            \M2E\Kaufland\Model\Product::STATUS_CHANGER_SYNCH,
            [],
            $calculateResult->getConfigurator()->getAllowedDataTypes(),
            false,
            $calculateResult->getConfigurator(),
        );
    }

    private function tryRemoveExistScheduledAction(): void
    {
        if ($this->getInput()->getScheduledAction() === null) {
            return;
        }

        if ($this->getInput()->getScheduledAction()->isForce()) {
            return;
        }

        $this->scheduledActionRepository->remove($this->getInput()->getScheduledAction());
    }
}
