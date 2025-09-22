<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Update;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            'y24_m03' => [
                \M2E\Kaufland\Setup\Update\y24_m03\AddIdentifierCodeCustomAttribute::class,
                \M2E\Kaufland\Setup\Update\y24_m03\AddColumsToListing::class,
                \M2E\Kaufland\Setup\Update\y24_m03\AddShippingTemplateTable::class,
                \M2E\Kaufland\Setup\Update\y24_m03\AddWarehouseTable::class,
                \M2E\Kaufland\Setup\Update\y24_m03\AddShippingGroupTable::class,
            ],
            'y24_m04' => [
                \M2E\Kaufland\Setup\Update\y24_m04\RenameProductColumn::class,
                \M2E\Kaufland\Setup\Update\y24_m04\AddSearchStatusColumn::class,
                \M2E\Kaufland\Setup\Update\y24_m04\RemoveProductColumn::class,
                \M2E\Kaufland\Setup\Update\y24_m04\AddShippingDataColumn::class,
                \M2E\Kaufland\Setup\Update\y24_m04\AddColumnsToListingOther::class,
                \M2E\Kaufland\Setup\Update\y24_m04\AddOnlineConditionColumn::class,
                \M2E\Kaufland\Setup\Update\y24_m04\ModifyStopQueueTableColumn::class,
            ],
            'y24_m05' => [
                \M2E\Kaufland\Setup\Update\y24_m05\UpdateSynchronizationTemplates::class,
                \M2E\Kaufland\Setup\Update\y24_m05\AddExternalChangeTable::class,
                \M2E\Kaufland\Setup\Update\y24_m05\AddDescriptionTemplateTable::class,
                \M2E\Kaufland\Setup\Update\y24_m05\AddDescriptionTemplateIdToListing::class,
                \M2E\Kaufland\Setup\Update\y24_m05\AddCategoryTreeTable::class,
                \M2E\Kaufland\Setup\Update\y24_m05\AddCategoryDictionaryTable::class,
                \M2E\Kaufland\Setup\Update\y24_m05\AddCategoryAttributeTable::class,
                \M2E\Kaufland\Setup\Update\y24_m05\AddProductLockTable::class,
                \M2E\Kaufland\Setup\Update\y24_m05\AddTemplateCategoryIdColumn::class,
                \M2E\Kaufland\Setup\Update\y24_m05\AddColumnsToSynchronizationTemplate::class,
                \M2E\Kaufland\Setup\Update\y24_m05\AddColumnsToProductTable::class,
            ],
            'y24_m06' => [
                \M2E\Kaufland\Setup\Update\y24_m06\ModifyIndexScheduledActionColumn::class,
                \M2E\Kaufland\Setup\Update\y24_m06\ListingWizard::class,
                \M2E\Kaufland\Setup\Update\y24_m06\RemoveListingProductConfigurations::class,
                \M2E\Kaufland\Setup\Update\y24_m06\RemoveListingProductAddIds::class,
                \M2E\Kaufland\Setup\Update\y24_m06\RemoveSearchStatusColumn::class,
            ],
            'y24_m07' => [
                \M2E\Kaufland\Setup\Update\y24_m07\AddColumnsToProductTable::class,
                \M2E\Kaufland\Setup\Update\y24_m07\RemoveProductWithoutOfferId::class,
                \M2E\Kaufland\Setup\Update\y24_m07\MigrateStoppedProductStatus::class,
                \M2E\Kaufland\Setup\Update\y24_m07\MigrateStoppedStatusUnmanaged::class,
                \M2E\Kaufland\Setup\Update\y24_m07\AddStatusChangeDateColumnToProduct::class,
                \M2E\Kaufland\Setup\Update\y24_m07\AddUploadMagentoInvoice::class,
            ],
            'y24_m08' => [
                \M2E\Kaufland\Setup\Update\y24_m08\AddColumnsToShippingTemplateTable::class,
            ],
            'y24_m09' => [
                \M2E\Kaufland\Setup\Update\y24_m09\AddIdentifierToAccount::class,
                \M2E\Kaufland\Setup\Update\y24_m09\FixTablesStructure::class,
                \M2E\Kaufland\Setup\Update\y24_m09\AddColumnToProductTable::class,
            ],
            'y24_m10' => [
                \M2E\Kaufland\Setup\Update\y24_m10\FixCategoryTreeTableStructure::class,
                \M2E\Kaufland\Setup\Update\y24_m10\AddSkuSettingsToListing::class,
            ],
            'y24_m11' => [
                \M2E\Kaufland\Setup\Update\y24_m11\AddAttributeMapping::class,
                \M2E\Kaufland\Setup\Update\y24_m11\MigrateLicenseAndRegistrationUserToCore::class,
                \M2E\Kaufland\Setup\Update\y24_m11\MigrateConfigToCore::class,
                \M2E\Kaufland\Setup\Update\y24_m11\MigrateRegistryToCore::class,
                \M2E\Kaufland\Setup\Update\y24_m11\RemoveServerHost::class,
            ],
            'y24_m12' => [
                \M2E\Kaufland\Setup\Update\y24_m12\RemoveUnusedConfigAndRegistryData::class,
                \M2E\Kaufland\Setup\Update\y24_m12\AddBillingDetailsToOrderTable::class,
            ],
            'y25_m01' => [
                \M2E\Kaufland\Setup\Update\y25_m01\AddTrackDirectDatabaseChanges::class,
            ],
            'y25_m03' => [
                \M2E\Kaufland\Setup\Update\y25_m03\CheckConfigs::class,
                \M2E\Kaufland\Setup\Update\y25_m03\RemoveOldCronValues::class,
                \M2E\Kaufland\Setup\Update\y25_m03\AddAccountIdToShippingPolicy::class,
                \M2E\Kaufland\Setup\Update\y25_m03\AddStatusChangerColumnToScheduledAction::class,
            ],
            'y25_m04' => [
                \M2E\Kaufland\Setup\Update\y25_m04\MigrateAttributeMappingToCore::class,
            ],
            'y25_m06' => [
                \M2E\Kaufland\Setup\Update\y25_m06\RemoveReferencesOfPolicyFromProduct::class,
            ],
            'y25_m09' => [
                \M2E\Kaufland\Setup\Update\y25_m09\AddValidationAttributesColumns::class,
            ],
        ];
    }
}
