<?php

namespace M2E\Kaufland\Block\Adminhtml\Order\Edit;

use M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock;

class ShippingAddress extends AbstractBlock
{
    protected $_template = 'kaufland/order/shipping_address.phtml';

    protected ?array $shippingAddress = null;
    private \M2E\Kaufland\Model\Order $order;

    public function __construct(
        \M2E\Kaufland\Model\Order $order,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->order = $order;
    }

    public function getOrder(): \M2E\Kaufland\Model\Order
    {
        return $this->order;
    }

    public function getShippingAddress(): array
    {
        if ($this->shippingAddress === null) {
            $shippingAddress = $this->getOrder()->getShippingAddress();

            $this->shippingAddress = $shippingAddress->getData();
            $this->shippingAddress['country_name'] = $shippingAddress->getCountryName();
        }

        return $this->shippingAddress;
    }

    public function getBuyerName(): string
    {
        $shippingAddress = $this->getShippingAddress();

        return $shippingAddress['buyer_name'];
    }
}
