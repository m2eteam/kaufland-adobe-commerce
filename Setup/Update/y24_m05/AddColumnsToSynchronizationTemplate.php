<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m05;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Template\Synchronization as SynchronizationResource;
use Magento\Framework\DB\Ddl\Table;

class AddColumnsToSynchronizationTemplate extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_TEMPLATE_SYNCHRONIZATION);

        $modifier->addColumn(
            SynchronizationResource::COLUMN_REVISE_UPDATE_TITLE,
            Table::TYPE_SMALLINT,
            0,
            SynchronizationResource::COLUMN_REVISE_UPDATE_PRICE,
        );

        $modifier->addColumn(
            SynchronizationResource::COLUMN_REVISE_UPDATE_CATEGORIES,
            Table::TYPE_SMALLINT,
            0,
            SynchronizationResource::COLUMN_REVISE_UPDATE_TITLE
        );

        $modifier->addColumn(
            SynchronizationResource::COLUMN_REVISE_UPDATE_IMAGES,
            Table::TYPE_SMALLINT,
            0,
            SynchronizationResource::COLUMN_REVISE_UPDATE_CATEGORIES
        );

        $modifier->addColumn(
            SynchronizationResource::COLUMN_REVISE_UPDATE_DESCRIPTION,
            Table::TYPE_SMALLINT,
            0,
            SynchronizationResource::COLUMN_REVISE_UPDATE_IMAGES,
        );
    }
}
