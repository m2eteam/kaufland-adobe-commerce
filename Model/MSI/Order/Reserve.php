<?php

namespace M2E\Kaufland\Model\MSI\Order;

use Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface;

class Reserve
{
    public const EVENT_TYPE_MAGENTO_RESERVATION_PLACED = 'kaufland_reservation_placed';
    public const EVENT_TYPE_MAGENTO_RESERVATION_RELEASED = 'kaufland_reservation_released';

    public const M2E_ORDER_OBJECT_TYPE = 'kaufland_order';

    // ---------------------------------------

    /** @var SalesEventInterfaceFactory $salesEventFactory */
    protected $salesEventFactory;

    /** @var SalesChannelInterfaceFactory $salesChannelFactory */
    protected $salesChannelFactory;

    /** @var ItemToSellInterfaceFactory $itemsToSellFactory */
    protected $itemsToSellFactory;

    /** @var PlaceReservationsForSalesEventInterface $placeReserve */
    protected $placeReserve;
    private \M2E\Core\Helper\Magento\Store $magentoStoreHelper;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \M2E\Core\Helper\Magento\Store $magentoStoreHelper
    ) {
        $this->salesEventFactory = $objectManager->get(SalesEventInterfaceFactory::class);
        $this->salesChannelFactory = $objectManager->get(SalesChannelInterfaceFactory::class);
        $this->itemsToSellFactory = $objectManager->get(ItemToSellInterfaceFactory::class);
        $this->placeReserve = $objectManager->get(PlaceReservationsForSalesEventInterface::class);
        $this->magentoStoreHelper = $magentoStoreHelper;
    }

    public function placeCompensationReservation(array $itemsToSell, $storeId, array $salesEventData)
    {
        $salesChannel = $this->salesChannelFactory->create([
            'data' => [
                'type' => SalesChannelInterface::TYPE_WEBSITE,
                'code' => $this->magentoStoreHelper->getWebsite($storeId)->getCode(),
            ],
        ]);

        $convertedItems = [];
        foreach ($itemsToSell as $itemToSell) {
            $convertedItems[] = $this->itemsToSellFactory->create([
                'sku' => $itemToSell['sku'],
                'qty' => $itemToSell['qty'],
            ]);
        }

        $this->placeReserve->execute(
            $convertedItems,
            $salesChannel,
            $this->salesEventFactory->create($salesEventData)
        );
    }
}
