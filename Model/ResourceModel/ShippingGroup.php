<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ResourceModel;

class ShippingGroup extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_SHIPPING_GROUP_ID = 'shipping_group_id';
    public const COLUMN_ACCOUNT_ID = 'account_id';
    public const COLUMN_CURRENCY = 'currency';
    public const COLUMN_STOREFRONT_ID = 'storefront_id';

    public const COLUMN_NAME = 'name';
    public const COLUMN_TYPE = 'type';
    public const COLUMN_IS_DEFAULT = 'is_default';
    public const COLUMN_REGIONS = 'regions';

    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    private ShippingGroup\CollectionFactory $shippingGroupCollectionFactory;

    public function __construct(
        \M2E\Kaufland\Model\ResourceModel\ShippingGroup\CollectionFactory $shippingGroupCollectionFactory,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct(
            $activeRecordFactory,
            $context,
            $connectionName
        );
        $this->shippingGroupCollectionFactory = $shippingGroupCollectionFactory;
    }

    public function _construct()
    {
        $this->_init(\M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_SHIPPING_GROUP, self::COLUMN_ID);
    }

    /**
     * @param \M2E\Kaufland\Model\ShippingGroup $object
     *
     * @return \M2E\Kaufland\Model\ResourceModel\ShippingGroup
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        $existedObject = $this->tryFindExistedObject($object);
        if ($existedObject !== null) {
            $object->setId($existedObject->getId());
        }

        return parent::save($object);
    }

    private function tryFindExistedObject(
        \M2E\Kaufland\Model\ShippingGroup $object
    ): ?\M2E\Kaufland\Model\ShippingGroup {
        $collection = $this->shippingGroupCollectionFactory->create();
        $collection->addFieldToFilter(self::COLUMN_ACCOUNT_ID, $object->getAccountId());
        $collection->addFieldToFilter(self::COLUMN_STOREFRONT_ID, $object->getStorefrontId());
        $collection->addFieldToFilter(self::COLUMN_SHIPPING_GROUP_ID, $object->getStorefrontId());

        /** @var \M2E\Kaufland\Model\ShippingGroup $existObject */
        $existObject = $collection->getFirstItem();

        if ($existObject->isObjectNew()) {
            return null;
        }

        return $existObject;
    }
}
