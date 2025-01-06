<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update\y24_m06;

use M2E\Kaufland\Helper\Module\Database\Tables;
use M2E\Kaufland\Model\ResourceModel\ScheduledAction as ScheduledActionResource;
use Magento\Framework\DB\Adapter\AdapterInterface;

class ModifyIndexScheduledActionColumn extends \M2E\Core\Model\Setup\Upgrade\Entity\AbstractFeature
{
    public function execute(): void
    {
        $modifier = $this->createTableModifier(Tables::TABLE_NAME_PRODUCT_SCHEDULED_ACTION);
        $modifier->dropIndex('listing_product_id');

        $this->getConnection()->addIndex(
            $this->getFullTableName(Tables::TABLE_NAME_PRODUCT_SCHEDULED_ACTION),
            'listing_product_id__action_type',
            [ScheduledActionResource::COLUMN_LISTING_PRODUCT_ID, ScheduledActionResource::COLUMN_ACTION_TYPE],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }
}
