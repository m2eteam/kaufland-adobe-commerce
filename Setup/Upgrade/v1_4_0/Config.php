<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Upgrade\v1_4_0;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\Kaufland\Setup\Update\y24_m06\RemoveListingProductConfigurations::class,
            \M2E\Kaufland\Setup\Update\y24_m07\AddColumnsToProductTable::class,
            \M2E\Kaufland\Setup\Update\y24_m07\RemoveProductWithoutOfferId::class,
        ];
    }
}
