<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup\Upgrade\v1_7_3;

class Config implements \M2E\Core\Model\Setup\Upgrade\Entity\ConfigInterface
{
    public function getFeaturesList(): array
    {
        return [
            \M2E\Kaufland\Setup\Update\y24_m10\FixCategoryTreeTableStructure::class
        ];
    }
}