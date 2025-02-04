<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\InstallHandler;

use M2E\Kaufland\Helper\Module\Configuration;
use M2E\Core\Model\Module\Adapter as ModuleAdapter;
use M2E\Core\Model\Module\Environment\Adapter as MOduleEnvAdapter;
use M2E\Kaufland\Helper\Module\Database\Tables as TablesHelper;
use M2E\Kaufland\Model\ResourceModel\Lock\Item as LockItemResource;
use M2E\Kaufland\Model\ResourceModel\Lock\Transactional as LockTransactionalResource;
use Magento\Framework\DB\Ddl\Table;

class CoreHandler implements \M2E\Core\Model\Setup\InstallHandlerInterface
{
    use \M2E\Kaufland\Setup\InstallHandlerTrait;

    public function installSchema(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $this->installWizardTable($setup);
        $this->installLockItemTableTable($setup);
        $this->installLockTransactionalTable($setup);
        $this->installOperationHistoryTable($setup);
    }

    private function installWizardTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_WIZARD);

        $wizardTable = $setup->getConnection()->newTable($tableName);
        $wizardTable
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                'nick',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'view',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'status',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'step',
                Table::TYPE_TEXT,
                255,
                ['default' => null]
            )
            ->addColumn(
                'type',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addColumn(
                'priority',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false]
            )
            ->addIndex('nick', 'nick')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($wizardTable);
    }

    private function installLockItemTableTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_LOCK_ITEM);

        $lockItemTable = $setup->getConnection()->newTable($tableName);

        $lockItemTable
            ->addColumn(
                LockItemResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'primary' => true, 'nullable' => false, 'auto_increment' => true]
            )
            ->addColumn(
                LockItemResource::COLUMN_NICK,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                LockItemResource::COLUMN_PARENT_ID,
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                LockItemResource::COLUMN_DATA,
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                LockItemResource::COLUMN_UPDATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                LockItemResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('nick', LockItemResource::COLUMN_NICK)
            ->addIndex('parent_id', LockItemResource::COLUMN_PARENT_ID)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($lockItemTable);
    }

    private function installLockTransactionalTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_LOCK_TRANSACTIONAL);

        $lockTransactional = $setup->getConnection()->newTable($tableName);
        $lockTransactional
            ->addColumn(
                LockTransactionalResource::COLUMN_ID,
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                LockTransactionalResource::COLUMN_NICK,
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                LockTransactionalResource::COLUMN_CREATE_DATE,
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('nick', LockTransactionalResource::COLUMN_NICK)
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($lockTransactional);
    }

    private function installOperationHistoryTable(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        $tableName = $this->getFullTableName(TablesHelper::TABLE_NAME_OPERATION_HISTORY);

        $operationHistoryTable = $setup->getConnection()->newTable($tableName);
        $operationHistoryTable
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'primary' => true,
                    'nullable' => false,
                    'auto_increment' => true,
                ]
            )
            ->addColumn(
                'nick',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false]
            )
            ->addColumn(
                'parent_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => null]
            )
            ->addColumn(
                'initiator',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => 0]
            )
            ->addColumn(
                'start_date',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false]
            )
            ->addColumn(
                'end_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'data',
                Table::TYPE_TEXT,
                null,
                ['default' => null]
            )
            ->addColumn(
                'update_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addColumn(
                'create_date',
                Table::TYPE_DATETIME,
                null,
                ['default' => null]
            )
            ->addIndex('nick', 'nick')
            ->addIndex('parent_id', 'parent_id')
            ->addIndex('initiator', 'initiator')
            ->addIndex('start_date', 'start_date')
            ->addIndex('end_date', 'end_date')
            ->setOption('type', 'INNODB')
            ->setOption('charset', 'utf8')
            ->setOption('collate', 'utf8_general_ci')
            ->setOption('row_format', 'dynamic');

        $setup->getConnection()->createTable($operationHistoryTable);
    }

    public function installData(\Magento\Framework\Setup\SetupInterface $setup): void
    {
        #region config
        $servicingInterval = random_int(43200, 86400);

        $config = $this->modifierConfigFactory->create(
            \M2E\Kaufland\Helper\Module::IDENTIFIER,
            $setup
        );

        $config->insert(ModuleAdapter::CONFIG_GROUP_ROOT, ModuleAdapter::CONFIG_KEY_DISABLED, '0');
        $config->insert(ModuleAdapter::CONFIG_GROUP_ROOT, MOduleEnvAdapter::CONFIG_KEY_ENVIRONMENT, 'production');
        $config->insert('/server/', 'application_key', '7248382d47edc4f925a076e419480d0540508ffe');
        $config->insert('/cron/', 'mode', '1');
        $config->insert('/cron/', 'runner', 'magento');
        $config->insert('/cron/magento/', 'disabled', '0');
        $config->insert('/cron/task/system/servicing/synchronize/', 'interval', $servicingInterval);
        $config->insert('/logs/clearing/listings/', 'mode', '1');
        $config->insert('/logs/clearing/listings/', 'days', '30');
        $config->insert('/logs/clearing/synchronizations/', 'mode', '1');
        $config->insert('/logs/clearing/synchronizations/', 'days', '30');
        $config->insert('/logs/clearing/orders/', 'mode', '1');
        $config->insert('/logs/clearing/orders/', 'days', '90');
        $config->insert('/logs/listings/', 'last_action_id', '0');
        $config->insert('/logs/grouped/', 'max_records_count', '100000');
        $config->insert('/support/', 'contact_email', 'support@m2epro.com');
        $config->insert(Configuration::CONFIG_GROUP, 'listing_product_inspector_mode', '0');
        $config->insert(Configuration::CONFIG_GROUP, 'view_show_block_notices_mode', '1');
        $config->insert(Configuration::CONFIG_GROUP, 'view_show_products_thumbnails_mode', '1');
        $config->insert(Configuration::CONFIG_GROUP, 'view_products_grid_use_alternative_mysql_select_mode', '0');
        $config->insert(Configuration::CONFIG_GROUP, 'other_pay_pal_url', 'paypal.com/cgi-bin/webscr/');
        $config->insert(Configuration::CONFIG_GROUP, 'product_index_mode', '1');
        $config->insert(Configuration::CONFIG_GROUP, 'product_force_qty_mode', '0');
        $config->insert(Configuration::CONFIG_GROUP, 'product_force_qty_value', '10');
        $config->insert(Configuration::CONFIG_GROUP, 'qty_percentage_rounding_greater', '0');
        $config->insert(Configuration::CONFIG_GROUP, 'magento_attribute_price_type_converting_mode', '0');
        $config->insert(
            Configuration::CONFIG_GROUP,
            'create_with_first_product_options_when_variation_unavailable',
            '1'
        );
        $config->insert(Configuration::CONFIG_GROUP, 'secure_image_url_in_item_description_mode', '0');
        $config->insert('/magento/product/simple_type/', 'custom_types', '');
        $config->insert('/magento/product/downloadable_type/', 'custom_types', '');
        $config->insert('/magento/product/configurable_type/', 'custom_types', '');
        $config->insert('/magento/product/bundle_type/', 'custom_types', '');
        $config->insert('/magento/product/grouped_type/', 'custom_types', '');
        $config->insert('/health_status/notification/', 'mode', 1);
        $config->insert('/health_status/notification/', 'email', '');
        $config->insert('/health_status/notification/', 'level', 40);
        $config->insert(
            \M2E\Kaufland\Model\Product\InspectDirectChanges\Config::GROUP,
            \M2E\Kaufland\Model\Product\InspectDirectChanges\Config::KEY_MAX_ALLOWED_PRODUCT_COUNT,
            '2000'
        );
        $config->insert('/listing/product/instructions/cron/', 'listings_products_per_one_time', '1000');
        $config->insert('/listing/product/scheduled_actions/', 'max_prepared_actions_count', '3000');
        $config->insert('/kaufland/configuration/', 'identifier_code_mode', '1');
        $config->insert('/kaufland/configuration/', 'identifier_code_custom_attribute', 'ean');
        #endregion

        #region wizard
        $setup->getConnection()->insertMultiple(
            $this->getFullTableName(TablesHelper::TABLE_NAME_WIZARD),
            [
                [
                    'nick' => 'installationKaufland',
                    'view' => 'kaufland',
                    'status' => 0,
                    'step' => null,
                    'type' => 1,
                    'priority' => 2,
                ],
            ],
        );
        #endregion
    }
}
