<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m07;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\Account as AccountResource;

class AddUploadMagentoInvoice extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_ACCOUNT);

        $modifier->addColumn(
            AccountResource::COLUMN_UPLOAD_MAGENTO_INVOICE,
            'SMALLINT UNSIGNED NOT NULL',
            0,
            AccountResource::COLUMN_CREATE_MAGENTO_INVOICE
        );
    }
}
