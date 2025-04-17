<?php

namespace M2E\Kaufland\Plugin\MSI\Magento\InventorySales\Model;

use M2E\Kaufland\Model\MSI\Order\Reserve;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;
use Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface;

class PlaceReservationsForSalesEvent extends \M2E\Kaufland\Plugin\AbstractPlugin
{
    private \M2E\Kaufland\Model\MSI\AffectedProducts $msiAffectedProducts;
    private \Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface $getStockByChannel;
    private \M2E\Kaufland\Model\Magento\Product\ChangeAttributeTrackerFactory $changeAttributeTrackerFactory;
    private \M2E\Kaufland\Model\Listing\LogService $listingLogService;

    public function __construct(
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        \M2E\Kaufland\Model\MSI\AffectedProducts $msiAffectedProducts,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\Kaufland\Model\Magento\Product\ChangeAttributeTrackerFactory $attributeTrackerFactory
    ) {
        $this->msiAffectedProducts = $msiAffectedProducts;
        $this->getStockByChannel = $objectManager->get(GetStockBySalesChannelInterface::class);
        $this->changeAttributeTrackerFactory = $attributeTrackerFactory;
        $this->listingLogService = $listingLogService;
    }

    public function aroundExecute($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('execute', $interceptor, $callback, $arguments);
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function processExecute($interceptor, \Closure $callback, array $arguments)
    {
        /**
         * @var \Magento\InventorySalesApi\Api\Data\ItemToSellInterface[] $items
         * @var \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel
         * @var \Magento\InventorySalesApi\Api\Data\SalesEventInterface $salesEvent
         */
        [$items, $salesChannel, $salesEvent] = $arguments;

        $result = $callback(...$arguments);

        $stock = $this->getStockByChannel->execute($salesChannel);
        foreach ($items as $item) {
            $affected = $this->msiAffectedProducts->getAffectedProductsByStockAndSku(
                $stock->getStockId(),
                $item->getSku()
            );

            if (empty($affected)) {
                continue;
            }

            $this->addListingProductInstructions($affected);

            foreach ($affected as $listingProduct) {
                $this->logListingProductMessage($listingProduct, $salesEvent, $salesChannel, $item);
            }
        }

        return $result;
    }

    private function logListingProductMessage(
        \M2E\Kaufland\Model\Product $listingProduct,
        \Magento\InventorySalesApi\Api\Data\SalesEventInterface $salesEvent,
        \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel,
        \Magento\InventorySalesApi\Api\Data\ItemToSellInterface $item
    ): void {
        $qty = abs($item->getQuantity());
        $stock = $this->getStockByChannel->execute($salesChannel);

        switch ($salesEvent->getType()) {
            case SalesEventInterface::EVENT_ORDER_PLACED:
                $resultMessage = sprintf(
                    'Product Quantity was reserved from the "%s" Stock in the amount of %s
                    because Magento Order was created.',
                    $stock->getName(),
                    $qty
                );
                break;

            case SalesEventInterface::EVENT_SHIPMENT_CREATED:
                $resultMessage = sprintf(
                    'Product Quantity reservation was released from the "%s" Stock ' .
                    'in the amount of %s because Magento Shipment was created.',
                    $stock->getName(),
                    $qty
                );
                break;

            case Reserve::EVENT_TYPE_MAGENTO_RESERVATION_PLACED:
                $resultMessage = __(
                    '%extension_title reserved Product Quantity from the "%stock_name" Stock in the amount of %qty.',
                    [
                        'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
                        'stock_name'      => $stock->getName(),
                        'qty'             => $qty,
                    ]
                );
                break;

            case Reserve::EVENT_TYPE_MAGENTO_RESERVATION_RELEASED:
                $resultMessage = __(
                    '%extension_title released Product Quantity reservation from the "%stock_name" Stock in the amount of %qty.',
                    [
                        'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
                        'stock_name'      => $stock->getName(),
                        'qty'             => $qty,
                    ]
                );
                break;

            default:
                if ($item->getQuantity()) {
                    $message = 'Product Quantity reservation was released ';
                } else {
                    $message = 'Product Quantity was reserved ';
                }
                $message .= 'from the "%s" Stock in the amount of %s because "%s" event occurred.';

                $resultMessage = sprintf(
                    $message,
                    $stock->getName(),
                    $qty,
                    $salesEvent->getType()
                );
        }

        $this->listingLogService->addProduct(
            $listingProduct,
            \M2E\Core\Helper\Data::INITIATOR_EXTENSION,
            \M2E\Kaufland\Model\Listing\Log::ACTION_CHANGE_PRODUCT_QTY,
            null,
            \M2E\Kaufland\Helper\Module\Log::encodeDescription($resultMessage),
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO
        );
    }

    /**
     * @param \M2E\Kaufland\Model\Product[] $affectedProducts
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    private function addListingProductInstructions(array $affectedProducts)
    {
        foreach ($affectedProducts as $listingProduct) {
            $changeAttributeTracker = $this->changeAttributeTrackerFactory->create(
                $listingProduct
            );
            $changeAttributeTracker->addInstructionWithPotentiallyChangedType();
            $changeAttributeTracker->flushInstructions();
        }
    }
}
