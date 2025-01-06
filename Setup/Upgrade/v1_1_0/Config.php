<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Upgrade\v1_1_0;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\Kaufland\Setup\Update\y24_m03\AddIdentifierCodeCustomAttribute::class,
            \M2E\Kaufland\Setup\Update\y24_m03\AddColumsToListing::class,
            \M2E\Kaufland\Setup\Update\y24_m03\AddShippingTemplateTable::class,
            \M2E\Kaufland\Setup\Update\y24_m03\AddWarehouseTable::class,
            \M2E\Kaufland\Setup\Update\y24_m03\AddShippingGroupTable::class,
            \M2E\Kaufland\Setup\Update\y24_m04\RenameProductColumn::class,
            \M2E\Kaufland\Setup\Update\y24_m04\AddSearchStatusColumn::class,
            \M2E\Kaufland\Setup\Update\y24_m04\RemoveProductColumn::class,
            \M2E\Kaufland\Setup\Update\y24_m04\AddShippingDataColumn::class,
            \M2E\Kaufland\Setup\Update\y24_m04\AddColumnsToListingOther::class,
            \M2E\Kaufland\Setup\Update\y24_m04\AddOnlineConditionColumn::class,
            \M2E\Kaufland\Setup\Update\y24_m04\ModifyStopQueueTableColumn::class,
            \M2E\Kaufland\Setup\Update\y24_m05\UpdateSynchronizationTemplates::class,
        ];
    }
}
