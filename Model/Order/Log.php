<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order;

class Log extends \M2E\Kaufland\Model\Log\AbstractModel
{
    public function _construct()
    {
        parent::_construct();
        $this->_init(\M2E\Kaufland\Model\ResourceModel\Order\Log::class);
    }

    // ----------------------------------------

    public function setAccountId(int $accountId): void
    {
        $this->setData('account_id', $accountId);
    }

    public function setInitiator(int $initiator): void
    {
        $this->setData('initiator', $initiator);
    }

    public function setStorefrontId(int $storefrontId): void
    {
        $this->setData('storefront_id', $storefrontId);
    }

    public function setOrderId(int $orderId): void
    {
        $this->setData('order_id', $orderId);
    }

    public function setDescription(string $description)
    {
        $this->setData('description', $description);
    }

    public function setType(int $type)
    {
        $this->setData('type', $type);
    }

    public function setAdditionalData(string $additionalData)
    {
        $this->setData('additional_data', $additionalData);
    }

    public function setCreateDate(string $dateTime)
    {
        $this->setData('create_date', $dateTime);
    }
}
