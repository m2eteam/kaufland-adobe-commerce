<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Upgrade\v2_9_0;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\Kaufland\Setup\Update\y25_m11\AddSavedAdvancedFilter::class,
        ];
    }
}
