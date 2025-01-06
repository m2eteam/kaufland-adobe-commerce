<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m06;

class RemoveListingProductConfigurations extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $connection = $this->getConnection();
        $configTable = $this->getFullTableName('m2e_kaufland_config');

        if (!$connection->isTableExists($configTable)) {
            return;
        }

        $whereConditions = [
            sprintf('`%s` = ?', \M2E\Core\Model\ResourceModel\Config::COLUMN_GROUP) => '/cron/task/listing/product/process_instructions/',
            sprintf('`%s` = ?', \M2E\Core\Model\ResourceModel\Config::COLUMN_KEY) => 'mode',
        ];

        $connection->delete($configTable, $whereConditions);
    }
}
