<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m07;

use M2E\Kaufland\Model\Product;
use M2E\Kaufland\Helper\Module\Database\Tables;

class MigrateStoppedStatusUnmanaged extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    private const PRODUCT_STATUS_STOPPED = 3;

    public function execute(): void
    {
        $this->getConnection()
             ->update(
                 $this->getFullTableName(Tables::TABLE_NAME_LISTING_OTHER),
                 ['status' => Product::STATUS_INACTIVE],
                 ['status = ?' => self::PRODUCT_STATUS_STOPPED],
             );
    }
}
