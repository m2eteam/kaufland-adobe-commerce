<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Upgrade\v1_3_0;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\Kaufland\Setup\Update\y24_m05\AddDescriptionTemplateTable::class,
            \M2E\Kaufland\Setup\Update\y24_m05\AddDescriptionTemplateIdToListing::class,
            \M2E\Kaufland\Setup\Update\y24_m05\AddCategoryTreeTable::class,
            \M2E\Kaufland\Setup\Update\y24_m05\AddCategoryDictionaryTable::class,
            \M2E\Kaufland\Setup\Update\y24_m05\AddCategoryAttributeTable::class,
            \M2E\Kaufland\Setup\Update\y24_m05\AddProductLockTable::class,
            \M2E\Kaufland\Setup\Update\y24_m05\AddTemplateCategoryIdColumn::class,
            \M2E\Kaufland\Setup\Update\y24_m05\AddColumnsToSynchronizationTemplate::class,
            \M2E\Kaufland\Setup\Update\y24_m05\AddColumnsToProductTable::class,
            \M2E\Kaufland\Setup\Update\y24_m06\ModifyIndexScheduledActionColumn::class,
            \M2E\Kaufland\Setup\Update\y24_m06\ListingWizard::class,
            \M2E\Kaufland\Setup\Update\y24_m06\RemoveListingProductAddIds::class,
            \M2E\Kaufland\Setup\Update\y24_m06\RemoveSearchStatusColumn::class,
        ];
    }
}
