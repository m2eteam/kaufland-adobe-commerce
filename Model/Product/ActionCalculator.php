<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

class ActionCalculator
{
    private \M2E\Kaufland\Model\Magento\Product\RuleFactory $ruleFactory;
    private Action\Type\ReviseProduct\Checker $reviseChecker;
    private Action\Type\ReviseUnit\Checker $reviseUnitChecker;

    public function __construct(
        \M2E\Kaufland\Model\Magento\Product\RuleFactory $ruleFactory,
        Action\Type\ReviseProduct\Checker $reviseChecker,
        Action\Type\ReviseUnit\Checker  $reviseUnitChecker
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->reviseChecker = $reviseChecker;
        $this->reviseUnitChecker = $reviseUnitChecker;
    }

    /**
     * @param \M2E\Kaufland\Model\Product $product
     * @param bool $force
     *
     * @return \M2E\Kaufland\Model\Product\Action[]
     */
    public function calculate(\M2E\Kaufland\Model\Product $product, bool $force, int $change): array
    {
        if ($product->isStatusNotListed()) {
            return [$this->calculateToList($product)];
        }

        if ($product->isStatusListed()) {
            $result = [];
            if ($product->isRevisableAsProduct()) {
                $result[] = $this->calculateToReviseProduct(
                    $product,
                    true,
                    true,
                    true,
                    true,
                );
            }

            $result[] = $this->calculateToReviseOrStopUnit($product);

            return $result;
        }

        if ($product->isStatusInactive()) {
            return [$this->calculateToRelist($product, $change)];
        }

        return [Action::createNothing($product)];
    }

    public function calculateToList(\M2E\Kaufland\Model\Product $product): Action
    {
        if (
            !$product->isListable()
            || !$product->isStatusNotListed()
        ) {
            return Action::createNothing($product);
        }

        if (!$this->isNeedListProduct($product)) {
            return Action::createNothing($product);
        }

        $configurator = new Action\Configurator();
        $configurator->enableAll();

        return Action::createList($product, $configurator);
    }

    private function isNeedListProduct(\M2E\Kaufland\Model\Product $product): bool
    {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (!$syncPolicy->isListMode()) {
            return false;
        }

        if (
            $syncPolicy->isListStatusEnabled()
            && !$product->getMagentoProduct()->isStatusEnabled()
        ) {
            return false;
        }

        if (
            $syncPolicy->isListIsInStock()
            && !$product->getMagentoProduct()->isStockAvailability()
        ) {
            return false;
        }

        if (
            $syncPolicy->isListWhenQtyCalculatedHasValue()
            && !$this->isProductHasCalculatedQtyForListRevise(
                $product,
                (int)$syncPolicy->getListWhenQtyCalculatedHasValue(),
            )
        ) {
            return false;
        }

        if (
            $syncPolicy->isListAdvancedRulesEnabled()
            && !$this->isListAdvancedRuleMet($product, $syncPolicy)
        ) {
            return false;
        }

        return true;
    }

    // ----------------------------------------

    public function calculateToReviseOrStopUnit(\M2E\Kaufland\Model\Product $product): Action
    {
        if (
            !$product->isRevisable()
            && !$product->isStoppable()
        ) {
            return Action::createNothing($product);
        }

        if ($this->isNeedStopProduct($product)) {
            return Action::createStop($product);
        }

        $configurator = new Action\Configurator();
        $configurator->disableAll();

        if (!$this->needRevise($product)) {
            return Action::createNothing($product);
        }

        $this->updateConfiguratorAddPrice(
            $configurator,
            $product,
        );

        $this->updateConfiguratorAddQty(
            $configurator,
            $product,
        );

        $this->updateConfiguratorAddShipping(
            $configurator,
            $product,
        );

        if (empty($configurator->getAllowedDataTypes())) {
            return Action::createNothing($product);
        }

        return Action::createReviseUnit($product, $configurator);
    }

    public function calculateToReviseProduct(
        \M2E\Kaufland\Model\Product $product,
        bool $isDetectChangeTitle,
        bool $isDetectChangeDescription,
        bool $isDetectChangeImages,
        bool $isDetectChangeCategories
    ): Action {
        if (
            !$product->isRevisable()
            || !$product->isReadyForReviseAsProduct()
        ) {
            return Action::createNothing($product);
        }

        $configurator = new Action\Configurator();
        $configurator->disableAll();

        $this->updateConfiguratorAddTitle(
            $configurator,
            $product,
            $isDetectChangeTitle,
        );
        $this->updateConfiguratorAddDescription(
            $configurator,
            $product,
            $isDetectChangeDescription,
        );
        $this->updateConfiguratorAddImages(
            $configurator,
            $product,
            $isDetectChangeImages,
        );
        $this->updateConfiguratorAddCategories(
            $configurator,
            $product,
            $isDetectChangeCategories,
        );

        if (empty($configurator->getAllowedDataTypes())) {
            return Action::createNothing($product);
        }

        return Action::createReviseProduct($product, $configurator);
    }

    private function updateConfiguratorAddPrice(
        Action\Configurator $configurator,
        \M2E\Kaufland\Model\Product $product
    ): void {
        if ($this->reviseUnitChecker->isNeedReviseForPrice($product)) {
            $configurator->allowPrice();

            return;
        }

        $configurator->disallowPrice();
    }

    private function updateConfiguratorAddQty(
        Action\Configurator $configurator,
        \M2E\Kaufland\Model\Product $product
    ): void {
        if ($this->reviseUnitChecker->isNeedReviseForQty($product)) {
            $configurator->allowQty();

            return;
        }

        $configurator->disallowQty();
    }

    private function updateConfiguratorAddShipping(
        Action\Configurator $configurator,
        \M2E\Kaufland\Model\Product $product
    ): void {
        if ($this->reviseUnitChecker->isNeedReviseForShipping($product)) {
            $configurator->allowShipping();

            return;
        }

        $configurator->disallowShipping();
    }

    private function updateConfiguratorAddTitle(
        Action\Configurator $configurator,
        \M2E\Kaufland\Model\Product $product,
        bool $hasInstructionsForUpdateTitle
    ): void {
        if (!$hasInstructionsForUpdateTitle) {
            return;
        }

        if ($this->reviseChecker->isNeedReviseForTitle($product)) {
            $configurator->allowTitle();

            return;
        }

        $configurator->disallowTitle();
    }

    private function updateConfiguratorAddDescription(
        Action\Configurator $configurator,
        \M2E\Kaufland\Model\Product $product,
        bool $hasInstructionsForUpdateDescription
    ): void {
        if (!$hasInstructionsForUpdateDescription) {
            return;
        }

        if ($this->reviseChecker->isNeedReviseForDescription($product)) {
            $configurator->allowDescription();

            return;
        }

        $configurator->disallowDescription();
    }

    private function updateConfiguratorAddImages(
        Action\Configurator $configurator,
        \M2E\Kaufland\Model\Product $product,
        bool $hasInstructionsForUpdateImages
    ): void {
        if (!$hasInstructionsForUpdateImages) {
            return;
        }

        if ($this->reviseChecker->isNeedReviseForImages($product)) {
            $configurator->allowImages();

            return;
        }

        $configurator->disallowImages();
    }

    private function updateConfiguratorAddCategories(
        Action\Configurator $configurator,
        \M2E\Kaufland\Model\Product $product,
        bool $hasInstructionsForUpdateCategories
    ): void {
        if (!$hasInstructionsForUpdateCategories) {
            return;
        }

        if (
            $this->reviseChecker->isNeedReviseForCategories($product)
        ) {
            $configurator->allowCategories();

            return;
        }

        $configurator->disallowCategories();
    }

    private function isNeedStopProduct(\M2E\Kaufland\Model\Product $product): bool
    {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (!$syncPolicy->isStopMode()) {
            return false;
        }

        if (
            $syncPolicy->isStopStatusDisabled()
            && !$product->getMagentoProduct()->isStatusEnabled()
        ) {
            return true;
        }

        if (
            $syncPolicy->isStopOutOfStock()
            && !$product->getMagentoProduct()->isStockAvailability()
        ) {
            return true;
        }

        if (
            $syncPolicy->isStopWhenQtyCalculatedHasValue()
            && $this->isProductHasCalculatedQtyForStop(
                $product,
                (int)$syncPolicy->getStopWhenQtyCalculatedHasValueMin(),
            )
        ) {
            return true;
        }

        if (
            $syncPolicy->isStopAdvancedRulesEnabled()
            && $this->isStopAdvancedRuleMet($product, $syncPolicy)
        ) {
            return true;
        }

        return false;
    }

    // ----------------------------------------

    public function calculateToRelist(\M2E\Kaufland\Model\Product $product, int $changer): Action
    {
        if (!$product->isRelistable()) {
            return Action::createNothing($product);
        }

        if (!$this->isNeedRelistProduct($product, $changer)) {
            return Action::createNothing($product);
        }

        $configurator = new Action\Configurator();
        $configurator->enableAll();

        return Action::createRelist($product, $configurator);
    }

    private function isNeedRelistProduct(\M2E\Kaufland\Model\Product $product, int $changer): bool
    {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (!$syncPolicy->isRelistMode()) {
            return false;
        }

        if (
            $product->isStatusInactive()
            && $syncPolicy->isRelistFilterUserLock()
            && $product->isStatusChangerUser()
            && $changer !== \M2E\Kaufland\Model\Product::STATUS_CHANGER_USER
        ) {
            return false;
        }

        if (
            $syncPolicy->isRelistStatusEnabled()
            && !$product->getMagentoProduct()->isStatusEnabled()
        ) {
            return false;
        }

        if (
            $syncPolicy->isRelistIsInStock()
            && !$product->getMagentoProduct()->isStockAvailability()
        ) {
            return false;
        }

        if (
            $syncPolicy->isRelistWhenQtyCalculatedHasValue()
            && !$this->isProductHasCalculatedQtyForListRevise(
                $product,
                (int)$syncPolicy->getListWhenQtyCalculatedHasValue(),
            )
        ) {
            return false;
        }

        if (
            $syncPolicy->isReviseUpdatePrice()
            && $this->isChangedPrice($product)
        ) {
            return true;
        }

        if (
            $syncPolicy->isRelistAdvancedRulesEnabled()
            && !$this->isRelistAdvancedRuleMet($product, $syncPolicy)
        ) {
            return false;
        }

        return true;
    }

    // ----------------------------------------

    private function isProductHasCalculatedQtyForListRevise(
        \M2E\Kaufland\Model\Product $product,
        int $minQty
    ): bool {
        $productQty = $product->getQty();

        return $productQty >= $minQty;
    }

    private function isProductHasCalculatedQtyForStop(
        \M2E\Kaufland\Model\Product $product,
        int $minQty
    ): bool {
        $productQty = $product->getQty();

        return $productQty <= $minQty;
    }

    private function isListAdvancedRuleMet(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Template\Synchronization $syncPolicy
    ): bool {
        $ruleModel = $this->ruleFactory
            ->create()
            ->setData(
                [
                    'store_id' => $product->getListing()->getStoreId(),
                    'prefix' => \M2E\Kaufland\Model\Template\Synchronization::LIST_ADVANCED_RULES_PREFIX,
                ],
            );
        $ruleModel->loadFromSerialized($syncPolicy->getListAdvancedRulesFilters());

        if ($ruleModel->validate($product->getMagentoProduct()->getProduct())) {
            return true;
        }

        return false;
    }

    private function isStopAdvancedRuleMet(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Template\Synchronization $syncPolicy
    ): bool {
        $ruleModel = $this->ruleFactory
            ->create()
            ->setData(
                [
                    'store_id' => $product->getListing()->getStoreId(),
                    'prefix' => \M2E\Kaufland\Model\Template\Synchronization::STOP_ADVANCED_RULES_PREFIX,
                ],
            );
        $ruleModel->loadFromSerialized($syncPolicy->getStopAdvancedRulesFilters());

        if ($ruleModel->validate($product->getMagentoProduct()->getProduct())) {
            return true;
        }

        return false;
    }

    private function isRelistAdvancedRuleMet(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Template\Synchronization $syncPolicy
    ): bool {
        $ruleModel = $this->ruleFactory
            ->create()
            ->setData(
                [
                    'store_id' => $product->getListing()->getStoreId(),
                    'prefix' => \M2E\Kaufland\Model\Template\Synchronization::RELIST_ADVANCED_RULES_PREFIX,
                ],
            );
        $ruleModel->loadFromSerialized($syncPolicy->getRelistAdvancedRulesFilters());

        if ($ruleModel->validate($product->getMagentoProduct()->getProduct())) {
            return true;
        }

        return false;
    }

    private function isChangedPrice(
        \M2E\Kaufland\Model\Product $product
    ): bool {
        return $product->getOnlineCurrentPrice() !== $product->getFixedPrice();
    }

    private function needRevise(
        \M2E\Kaufland\Model\Product $product
    ): bool {
        $syncPolicy = $product->getSynchronizationTemplate();

        if (
            $syncPolicy->isReviseUpdateQty()
            && $this->isChangedQty($product, $syncPolicy)
        ) {
            return true;
        }

        if (
            $syncPolicy->isReviseUpdatePrice()
            && $this->isChangedPrice($product)
        ) {
            return true;
        }

        if ($this->isChangedShipping($product)) {
            return true;
        }

        return false;
    }

    private function isChangedQty(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Template\Synchronization $syncPolicy
    ): bool {
        $maxAppliedValue = $syncPolicy->getReviseUpdateQtyMaxAppliedValue();

        $productQty = $product->getQty();
        $channelQty = $product->getOnlineQty();

        if (
            $syncPolicy->isReviseUpdateQtyMaxAppliedValueModeOn()
            && $productQty > $maxAppliedValue
            && $channelQty > $maxAppliedValue
        ) {
            return false;
        }

        if ($productQty === $channelQty) {
            return false;
        }

        return true;
    }

    private function isChangedShipping(
        \M2E\Kaufland\Model\Product $product
    ): bool {
        $shippingData = $product->getShippingPolicyDataProvider();
        $kauflandShippingGroupId = $shippingData->getKauflandShippingGroupId();
        $kauflandWarehouseId = $shippingData->getKauflandWarehouseId();

        return (
            $kauflandShippingGroupId !== $product->getOnlineShippingGroupId()
            || $shippingData->getHandlingTime() !== $product->getOnlineHandlingTime()
            || $kauflandWarehouseId !== $product->getOnlineWarehouseId()
        );
    }
}
