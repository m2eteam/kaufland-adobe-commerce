<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m07;

use M2E\Kaufland\Helper\Module\Database\Tables;

class RemoveProductWithoutOfferId extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $this->getConnection()->delete(
            $this->getFullTableName(Tables::TABLE_NAME_LISTING_OTHER),
            "offer_id = ''"
        );
    }
}
