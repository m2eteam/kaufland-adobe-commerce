<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template;

use M2E\Kaufland\Model\ResourceModel\Template\Shipping as ShippingResource;

class Shipping extends \M2E\Kaufland\Model\ActiveRecord\AbstractModel implements PolicyInterface
{
    public const HANDLING_TIME_MODE_VALUE = 1;
    public const HANDLING_TIME_MODE_ATTRIBUTE = 2;

    private \M2E\Kaufland\Model\ShippingGroup\Repository $shippingGroupRepository;
    private \M2E\Kaufland\Model\Warehouse\Repository $warehouseGroupRepository;

    public function __construct(
        \M2E\Kaufland\Model\ShippingGroup\Repository $shippingGroupRepository,
        \M2E\Kaufland\Model\Warehouse\Repository $warehouseGroupRepository,
        \M2E\Kaufland\Model\Factory $modelFactory = null,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory = null,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $modelFactory,
            $activeRecordFactory,
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->shippingGroupRepository = $shippingGroupRepository;
        $this->warehouseGroupRepository = $warehouseGroupRepository;
    }

    public function _construct(): void
    {
        parent::_construct();
        $this->_init(ShippingResource::class);
    }

    public function getNick(): string
    {
        return \M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SHIPPING;
    }

    public function getTitle(): string
    {
        return (string)$this->getData(ShippingResource::COLUMN_TITLE);
    }

    public function getWarehouseId(): int
    {
        return (int)$this->getData(ShippingResource::COLUMN_WAREHOUSE_ID);
    }

    public function getShippingGroupId(): int
    {
        return (int)$this->getData(ShippingResource::COLUMN_SHIPPING_GROUP_ID);
    }

    public function getHandlingTimeValue(): int
    {
        return (int)$this->getData(ShippingResource::COLUMN_HANDLING_TIME);
    }

    public function getHandlingTimeMode(): int
    {
        return (int)$this->getData(ShippingResource::COLUMN_HANDLING_TIME_MODE);
    }

    public function isHandlingTimeModeAttribute(): bool
    {
        return $this->getHandlingTimeMode() == self::HANDLING_TIME_MODE_ATTRIBUTE;
    }

    public function getHandlingTimeAttribute(): string
    {
        return $this->getData(ShippingResource::COLUMN_HANDLING_TIME_ATTRIBUTE);
    }

    public function getCreateDate()
    {
        return $this->getData(ShippingResource::COLUMN_CREATE_DATE);
    }

    public function getUpdateDate()
    {
        return $this->getData(ShippingResource::COLUMN_UPDATE_DATE);
    }

    public function getKauflandShippingGroupId(): int
    {
        $shippingGroupId = $this->getShippingGroupId();

        return $this->shippingGroupRepository->get($shippingGroupId)->getShippingGroupId();
    }

    public function getKauflandSWarehouseId(): int
    {
        $warehouseId = $this->getWarehouseId();

        return $this->warehouseGroupRepository->get($warehouseId)->getWarehouseId();
    }
}
