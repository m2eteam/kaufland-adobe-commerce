<?php

namespace M2E\Kaufland\Model\Kaufland\Order\Item;

class Builder extends \Magento\Framework\DataObject
{
    private \M2E\Kaufland\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory;
    private \M2E\Kaufland\Model\Kaufland\Order\StatusResolver $orderStatusResolver;
    private \M2E\Kaufland\Model\Kaufland\Order\TaxResolver $taxResolver;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \M2E\Kaufland\Model\Kaufland\Order\StatusResolver $orderStatusResolver,
        \M2E\Kaufland\Model\Kaufland\Order\TaxResolver $taxResolver,
        array $data = []
    ) {
        parent::__construct($data);
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->orderStatusResolver = $orderStatusResolver;
        $this->taxResolver = $taxResolver;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function initialize(array $data): void
    {
        // Base
        $this->setData('order_id', $data['order_id']);
        $this->setData('kaufland_order_item_id', $data['order_unit_id']);
        $this->setData('kaufland_product_id', $data['product']['product_id']);
        $this->setData('kaufland_offer_id', $data['offer_id']);
        $this->setData('status', $this->orderStatusResolver->convertKauflandOrderStatus($data['status']));
        $this->setData('title', $data['product']['title']);
        $this->setData('eans', json_encode($data['product']['eans'], JSON_THROW_ON_ERROR));

        // Price
        $this->setData('sale_price', (float)$data['price']);
        $this->setData('revenue_gross', (float)$data['revenue_gross']);
        $this->setData('revenue_net', (float)$data['revenue_net']);

        // Taxes
        $taxRate = $this->taxResolver->getVatbyStorefrontCode($data['storefront_code']);
        $taxAmount = $this->taxResolver->getTaxAmount((float)$data['price'], (float)$taxRate);
        $taxDetails = [
            'percent' => $taxRate,
            'amount' => $taxAmount,
        ];

        $this->setData('tax_details', \M2E\Core\Helper\Json::encode($taxDetails));

        // QTY always one
        $this->setData('qty_purchased', 1);
    }

    public function process(): \M2E\Kaufland\Model\Order\Item
    {
        $collection = $this->orderItemCollectionFactory->create();
        $collection->addFieldToFilter('order_id', $this->getData('order_id'));
        $collection->addFieldToFilter('kaufland_order_item_id', $this->getData('kaufland_order_item_id'));

        /** @var \M2E\Kaufland\Model\Order\Item $item */
        $item = $collection->getFirstItem();

        foreach ($this->getData() as $key => $value) {
            if (
                $item->isObjectNew()
                || ($item->hasData($key) && $item->getData($key) != $value)
            ) {
                $item->addData($this->getData());
                $item->save();
                break;
            }
        }

        return $item;
    }
}
