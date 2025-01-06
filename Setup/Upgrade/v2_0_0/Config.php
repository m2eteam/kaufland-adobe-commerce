<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Upgrade\v2_0_0;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\Kaufland\Setup\Update\y24_m10\AddSkuSettingsToListing::class,
            \M2E\Kaufland\Setup\Update\y24_m11\MigrateLicenseAndRegistrationUserToCore::class,
            \M2E\Kaufland\Setup\Update\y24_m11\MigrateConfigToCore::class,
            \M2E\Kaufland\Setup\Update\y24_m11\MigrateRegistryToCore::class,
            \M2E\Kaufland\Setup\Update\y24_m11\RemoveServerHost::class,
            \M2E\Kaufland\Setup\Update\y24_m12\RemoveUnusedConfigAndRegistryData::class,
            \M2E\Kaufland\Setup\Update\y24_m12\AddBillingDetailsToOrderTable::class
        ];
    }
}
