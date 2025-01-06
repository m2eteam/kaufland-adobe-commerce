<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product;

use M2E\Kaufland\Model\ResourceModel\Product\Lock as LockResource;

class Lock extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel
{
    public const TYPE_PRODUCT = 'product';
    public const TYPE_UNIT = 'unit';

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(LockResource::class);
    }

    public function init(int $productId, string $type, string $initiator, \DateTime $createDate): self
    {
        $this->setData(LockResource::COLUMN_PRODUCT_ID, $productId);
        $this->setData(LockResource::COLUMN_TYPE, $type);
        $this->setData(LockResource::COLUMN_INITIATOR, $initiator);
        $this->setData(LockResource::COLUMN_CREATE_DATE, $createDate);

        return $this;
    }

    public function getInitiator(): string
    {
        return $this->getData('initiator');
    }

    public function isLockAsProduct(): bool
    {
        return $this->getType() === self::TYPE_PRODUCT;
    }

    public function isLockAsUnit(): bool
    {
        return $this->getType() === self::TYPE_UNIT;
    }

    public function getType(): string
    {
        return $this->getData(LockResource::COLUMN_TYPE);
    }

    public function getProductId(): int
    {
        return (int)$this->getData(LockResource::COLUMN_PRODUCT_ID);
    }

    public function getCreateDate(): \DateTime
    {
        return \M2E\Core\Helper\Date::createDateGmt(
            $this->getData(LockResource::COLUMN_CREATE_DATE)
        );
    }
}
