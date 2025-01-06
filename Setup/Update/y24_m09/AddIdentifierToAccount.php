<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m09;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Account as AccountResource;

class AddIdentifierToAccount extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ACCOUNT);

        $modifier->addColumn(
            AccountResource::COLUMN_IDENTIFIER,
            'VARCHAR(100)',
            null,
            AccountResource::COLUMN_SERVER_HASH,
            false,
            false
        );

        $modifier->commit();
    }
}
