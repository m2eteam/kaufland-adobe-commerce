<?php

declare(strict_types=1);

namespace M2E\Kaufland\Helper\Module\Database;

class Tables
{
    public const PREFIX = 'm2e_kaufland_';

    public const TABLE_NAME_WIZARD = self::PREFIX . 'wizard';

    public const TABLE_NAME_ACCOUNT = self::PREFIX . 'account';
    public const TABLE_NAME_STOREFRONT = self::PREFIX . 'storefront';
    public const TABLE_NAME_WAREHOUSE = self::PREFIX . 'warehouse';
    public const TABLE_NAME_SHIPPING_GROUP = self::PREFIX . 'shipping_group';

    public const TABLE_NAME_LISTING = self::PREFIX . 'listing';
    public const TABLE_NAME_LISTING_LOG = self::PREFIX . 'listing_log';
    public const TABLE_NAME_LISTING_WIZARD = self::PREFIX . 'listing_wizard';
    public const TABLE_NAME_LISTING_WIZARD_STEP = self::PREFIX . 'listing_wizard_step';
    public const TABLE_NAME_LISTING_WIZARD_PRODUCT = self::PREFIX . 'listing_wizard_product';
    public const TABLE_NAME_PRODUCT = self::PREFIX . 'product';
    public const TABLE_NAME_PRODUCT_INSTRUCTION = self::PREFIX . 'product_instruction';
    public const TABLE_NAME_PRODUCT_SCHEDULED_ACTION = self::PREFIX . 'product_scheduled_action';
    public const TABLE_NAME_PRODUCT_LOCK = self::PREFIX . 'product_lock';

    public const TABLE_NAME_LOCK_ITEM = self::PREFIX . 'lock_item';
    public const TABLE_NAME_LOCK_TRANSACTIONAL = self::PREFIX . 'lock_transactional';

    public const TABLE_NAME_PROCESSING = self::PREFIX . 'processing';
    public const TABLE_NAME_PROCESSING_PARTIAL_DATA = self::PREFIX . 'processing_partial_data';
    public const TABLE_NAME_PROCESSING_LOCK = self::PREFIX . 'processing_lock';
    public const TABLE_NAME_STOP_QUEUE = self::PREFIX . 'stop_queue';

    public const TABLE_NAME_SYNCHRONIZATION_LOG = self::PREFIX . 'synchronization_log';
    public const TABLE_NAME_SYSTEM_LOG = self::PREFIX . 'system_log';
    public const TABLE_NAME_OPERATION_HISTORY = self::PREFIX . 'operation_history';

    public const TABLE_NAME_TEMPLATE_SELLING_FORMAT = self::PREFIX . 'template_selling_format';
    public const TABLE_NAME_TEMPLATE_SYNCHRONIZATION = self::PREFIX . 'template_synchronization';
    public const TABLE_NAME_TEMPLATE_SHIPPING = self::PREFIX . 'template_shipping';
    public const TABLE_NAME_TEMPLATE_DESCRIPTION = self::PREFIX . 'template_description';
    public const TABLE_NAME_TAG = self::PREFIX . 'tag';
    public const TABLE_NAME_PRODUCT_TAG_RELATION = self::PREFIX . 'listing_product_tag_relation';
    public const TABLE_NAME_ORDER = self::PREFIX . 'order';
    public const TABLE_NAME_ORDER_ITEM = self::PREFIX . 'order_item';
    public const TABLE_NAME_ORDER_LOG = self::PREFIX . 'order_log';
    public const TABLE_NAME_ORDER_NOTE = self::PREFIX . 'order_note';
    public const TABLE_NAME_ORDER_CHANGE = self::PREFIX . 'order_change';

    public const TABLE_NAME_LISTING_OTHER = self::PREFIX . 'listing_other';

    public const TABLE_NAME_CATEGORY_TREE = self::PREFIX . 'category_tree';
    public const TABLE_NAME_CATEGORY_DICTIONARY = self::PREFIX . 'category_dictionary';
    public const TABLE_NAME_CATEGORY_ATTRIBUTES = self::PREFIX . 'category_attributes';
    public const TABLE_NAME_ATTRIBUTE_MAPPING = self::PREFIX . 'attribute_mapping';
    public const TABLE_NAME_EXTERNAL_CHANGE = self::PREFIX . 'external_change';

    private \Magento\Framework\App\ResourceConnection $resourceConnection;
    private Structure $databaseHelper;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \M2E\Kaufland\Helper\Module\Database\Structure $databaseHelper
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->databaseHelper = $databaseHelper;
    }

    /**
     * @param string $tableName
     *
     * @return bool
     */
    public function isExists(string $tableName): bool
    {
        return $this->resourceConnection
            ->getConnection()
            ->isTableExists($this->getFullName($tableName));
    }

    /**
     * @param string $tableName
     *
     * @return string
     */
    public function getFullName(string $tableName): string
    {
        if (strpos($tableName, self::PREFIX) === false) {
            $tableName = self::PREFIX . $tableName;
        }

        return $this->databaseHelper->getTableNameWithPrefix($tableName);
    }

    /**
     * @param string $oldTable
     * @param string $newTable
     *
     * @return bool
     */
    public function renameTable(string $oldTable, string $newTable): bool
    {
        $oldTable = $this->getFullName($oldTable);
        $newTable = $this->getFullName($newTable);

        if (
            $this->resourceConnection->getConnection()->isTableExists($oldTable) &&
            !$this->resourceConnection->getConnection()->isTableExists($newTable)
        ) {
            $this->resourceConnection->getConnection()->renameTable(
                $oldTable,
                $newTable,
            );

            return true;
        }

        return false;
    }
}
