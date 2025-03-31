<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y25_m03;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Template\Shipping as ShippingResource;
use Magento\Framework\DB\Ddl\Table;

class AddAccountIdToShippingPolicy extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_TEMPLATE_SHIPPING);

        $modifier->addColumn(
            ShippingResource::COLUMN_ACCOUNT_ID,
            Table::TYPE_INTEGER,
            null,
            ShippingResource::COLUMN_ID,
            false,
            false
        );

        $modifier->commit();

        $this->fillAccountId();
    }

    private function fillAccountId(): void
    {
        $connection = $this->getConnection();
        $shippingTable = $this->getFullTableName(Tables::TABLE_NAME_TEMPLATE_SHIPPING);
        $storefrontTable = $this->getFullTableName(Tables::TABLE_NAME_STOREFRONT);

        $connection->query("
            UPDATE {$shippingTable} ts
            INNER JOIN {$storefrontTable} s ON ts.storefront_id = s.id
            SET ts.account_id = s.account_id
        ");
    }
}
