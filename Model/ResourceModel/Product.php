<?php

namespace M2E\Kaufland\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;

class Product extends \M2E\Kaufland\Model\ResourceModel\ActiveRecord\AbstractModel
{
    public const COLUMN_ID = 'id';
    public const COLUMN_LISTING_ID = 'listing_id';
    public const COLUMN_MAGENTO_PRODUCT_ID = 'magento_product_id';
    public const COLUMN_KAUFLAND_PRODUCT_ID = 'kaufland_product_id';
    public const COLUMN_IS_KAUFLAND_PRODUCT_CREATOR = 'is_kaufland_product_creator';
    public const COLUMN_OFFER_ID = 'offer_id';
    public const COLUMN_UNIT_ID = 'unit_id';
    public const COLUMN_STOREFRONT_ID = 'storefront_id';
    public const COLUMN_STATUS = 'status';
    public const COLUMN_IS_INCOMPLETE = 'is_incomplete';
    public const COLUMN_STATUS_CHANGE_DATE = 'status_change_date';
    public const COLUMN_STATUS_CHANGER = 'status_changer';
    public const COLUMN_ONLINE_PRICE = 'online_price';
    public const COLUMN_ONLINE_QTY = 'online_qty';

    public const COLUMN_ONLINE_HANDLING_TIME = 'online_handling_time';
    public const COLUMN_ONLINE_WAREHOUSE_ID = 'online_warehouse_id';
    public const COLUMN_ONLINE_SHIPPING_GROUP_ID = 'online_shipping_group_id';
    public const COLUMN_ONLINE_CONDITION = 'online_condition';
    public const COLUMN_ONLINE_CATEGORY_ID = 'online_category_id';
    public const COLUMN_ONLINE_CATEGORIES_DATA = 'online_categories_data';
    public const COLUMN_ONLINE_CATEGORIES_ATTRIBUTES_DATA = 'online_categories_attributes_data';
    public const COLUMN_ONLINE_TITLE = 'online_title';
    public const COLUMN_ONLINE_DESCRIPTION = 'online_description';
    public const COLUMN_ONLINE_IMAGE = 'online_image';

    public const COLUMN_TEMPLATE_CATEGORY_ID = 'template_category_id';
    public const COLUMN_TEMPLATE_SELLING_FORMAT_MODE = 'template_selling_format_mode';
    public const COLUMN_TEMPLATE_SELLING_FORMAT_ID = 'template_selling_format_id';
    public const COLUMN_TEMPLATE_SYNCHRONIZATION_MODE = 'template_synchronization_mode';
    public const COLUMN_TEMPLATE_SYNCHRONIZATION_ID = 'template_synchronization_id';
    public const COLUMN_LAST_BLOCKING_ERROR_DATE = 'last_blocking_error_date';
    public const COLUMN_ADDITIONAL_DATA = 'additional_data';
    public const COLUMN_CHANNEL_PRODUCT_EMPTY_ATTRIBUTES = 'channel_product_empty_attributes';
    public const COLUMN_UPDATE_DATE = 'update_date';
    public const COLUMN_CREATE_DATE = 'create_date';

    private \Magento\Framework\EntityManager\MetadataPool $metadataPool;
    private \M2E\Kaufland\Helper\Module\Database\Structure $dbStructureHelper;

    public function __construct(
        \M2E\Kaufland\Helper\Module\Database\Structure $dbStructureHelper,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\EntityManager\MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct(
            $activeRecordFactory,
            $context,
            $connectionName
        );
        $this->metadataPool = $metadataPool;
        $this->dbStructureHelper = $dbStructureHelper;
    }

    public function _construct(): void
    {
        $this->_init(
            \M2E\Kaufland\Helper\Module\Database\Tables::TABLE_NAME_PRODUCT,
            self::COLUMN_ID
        );
    }

    public function getProductIds(array $listingProductIds): array
    {
        $select = $this->getConnection()
                       ->select()
                       ->from(['lp' => $this->getMainTable()])
                       ->reset(\Magento\Framework\DB\Select::COLUMNS)
                       ->columns(['product_id'])
                       ->where('id IN (?)', $listingProductIds);

        return $select->query()->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getParentEntityIdsByChild($childId)
    {
        $select = $this->getConnection()
                       ->select()
                       ->from([
                           'l' => $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_link'),
                       ], [])
                       ->join(
                           [
                               'e' => $this->dbStructureHelper->getTableNameWithPrefix('catalog_product_entity'),
                           ],
                           'e.' .
                           $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField(
                           ) . ' = l.product_id',
                           ['e.entity_id']
                       )
                       ->where('l.linked_product_id = ?', $childId)
                       ->where(
                           'link_type_id = ?',
                           Link::LINK_TYPE_GROUPED
                       );

        return $this->getConnection()->fetchCol($select);
    }

    public function getTemplateCategoryIds(array $listingProductIds, $columnName, $returnNull = false)
    {
        $select = $this->getConnection()
                       ->select()
                       ->from(['elp' => $this->getMainTable()])
                       ->reset(\Magento\Framework\DB\Select::COLUMNS)
                       ->columns([$columnName])
            ->where('id IN (?)', $listingProductIds);

        !$returnNull && $select->where("{$columnName} IS NOT NULL");

        foreach ($select->query()->fetchAll() as $row) {
            $id = $row[$columnName] !== null ? (int)$row[$columnName] : null;
            if (!$returnNull) {
                continue;
            }

            $ids[$id] = $id;
        }

        return array_values($ids);
    }
}
