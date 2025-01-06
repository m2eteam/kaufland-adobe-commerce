<?php

namespace M2E\Kaufland\Model\Magento\Order;

class Shipment
{
    /** @var \Magento\Sales\Model\Order */
    protected $magentoOrder;

    /** @var \Magento\Sales\Model\Order\Shipment[] */
    protected $shipments = [];

    // ---------------------------------------

    /** @var \Magento\Framework\DB\TransactionFactory */
    protected $transactionFactory;

    /** @var \M2E\Kaufland\Model\Magento\Order\Shipment\DocumentFactory */
    protected $shipmentDocumentFactory;

    private \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper;

    public function __construct(
        \M2E\Kaufland\Model\Magento\Order\Shipment\DocumentFactory $shipmentDocumentFactory,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper
    ) {
        $this->shipmentDocumentFactory = $shipmentDocumentFactory;
        $this->transactionFactory = $transactionFactory;
        $this->globalDataHelper = $globalDataHelper;
    }

    //########################################

    /**
     * @param \Magento\Sales\Model\Order $magentoOrder
     *
     * @return $this
     */
    public function setMagentoOrder(\Magento\Sales\Model\Order $magentoOrder)
    {
        $this->magentoOrder = $magentoOrder;

        return $this;
    }

    public function getShipments()
    {
        return $this->shipments;
    }

    public function buildShipments()
    {
        $this->prepareShipments();

        $this->globalDataHelper->unsetValue('skip_shipment_observer');
        $this->globalDataHelper->setValue('skip_shipment_observer', true);

        /** @var \Magento\Framework\DB\Transaction $transaction */
        $transaction = $this->transactionFactory->create();
        foreach ($this->shipments as $shipment) {
            // it is necessary for updating qty_shipped field in sales_flat_order_item table
            $shipment->getOrder()->setIsInProcess(true);

            $transaction->addObject($shipment);
            $transaction->addObject($shipment->getOrder());

            $this->magentoOrder->getShipmentsCollection()->addItem($shipment);
        }

        try {
            $transaction->save();
        } catch (\Exception $e) {
            $this->magentoOrder->getShipmentsCollection()->clear();
            throw $e;
        }

        $this->globalDataHelper->unsetValue('skip_shipment_observer');
    }

    protected function prepareShipments()
    {
        /** @var \Magento\Sales\Model\Order\Shipment $shipment */
        $shipment = $this->shipmentDocumentFactory->create($this->magentoOrder);
        $shipment->register();

        $this->shipments[] = $shipment;
    }
}
