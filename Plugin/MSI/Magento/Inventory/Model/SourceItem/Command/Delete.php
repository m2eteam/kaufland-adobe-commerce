<?php

namespace M2E\Kaufland\Plugin\MSI\Magento\Inventory\Model\SourceItem\Command;

class Delete extends \M2E\Kaufland\Plugin\AbstractPlugin
{
    private \M2E\Kaufland\Model\MSI\AffectedProducts $msiAffectedProducts;
    private \M2E\Kaufland\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory;
    private \M2E\Kaufland\Model\Listing\LogService $listingLogService;

    public function __construct(
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        \M2E\Kaufland\Model\MSI\AffectedProducts $msiAffectedProducts,
        \M2E\Kaufland\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory
    ) {
        $this->msiAffectedProducts = $msiAffectedProducts;
        $this->changeAttributeTrackerFactory = $changeAttributeTrackerFactory;
        $this->listingLogService = $listingLogService;
    }

    public function aroundExecute($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('execute', $interceptor, $callback, $arguments);
    }

    protected function processExecute($interceptor, \Closure $callback, array $arguments)
    {
        /** @var \Magento\InventoryApi\Api\Data\SourceItemInterface[] $sourceItems */
        $sourceItems = $arguments[0];

        $result = $callback(...$arguments);

        foreach ($sourceItems as $sourceItem) {
            $affected = $this->msiAffectedProducts->getAffectedProductsBySourceAndSku(
                $sourceItem->getSourceCode(),
                $sourceItem->getSku()
            );

            if (empty($affected)) {
                continue;
            }

            $this->addListingProductInstructions($affected);

            foreach ($affected as $listingProduct) {
                $this->logListingProductMessage($listingProduct, $sourceItem);
            }
        }

        return $result;
    }

    private function logListingProductMessage(
        \M2E\Kaufland\Model\Product $listingProduct,
        \Magento\InventoryApi\Api\Data\SourceItemInterface $sourceItem
    ): void {
        $this->listingLogService->addProduct(
            $listingProduct,
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
            \M2E\Kaufland\Model\Listing\Log::ACTION_CHANGE_PRODUCT_QTY,
            null,
            \M2E\Kaufland\Helper\Module\Log::encodeDescription(
                'The "%source%" Source was unassigned from product.',
                ['!source' => $sourceItem->getSourceCode()]
            ),
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO
        );
    }

    /**
     * @param \M2E\Kaufland\Model\Product[] $affectedProducts*
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function addListingProductInstructions(array $affectedProducts): void
    {
        foreach ($affectedProducts as $listingProduct) {
            $changeAttributeTracker = $this->changeAttributeTrackerFactory->create(
                $listingProduct,
            );
            $changeAttributeTracker->addInstructionWithPotentiallyChangedType();
            $changeAttributeTracker->flushInstructions();
        }
    }
}
