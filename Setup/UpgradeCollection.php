<?php

declare(strict_types=1);

namespace M2E\Kaufland\Setup;

class UpgradeCollection extends \M2E\Core\Model\Setup\AbstractUpgradeCollection
{
    public function getMinAllowedVersion(): string
    {
        return '1.0.0';
    }

    protected function getSourceVersionUpgrades(): array
    {
        return [
            '1.0.0' => ['to' => '1.0.1', 'upgrade' => null],
            '1.0.1' => ['to' => '1.0.2', 'upgrade' => null],
            '1.0.2' => ['to' => '1.0.3', 'upgrade' => null],
            '1.0.3' => ['to' => '1.0.4', 'upgrade' => null],
            '1.0.4' => ['to' => '1.0.5', 'upgrade' => null],
            '1.0.5' => ['to' => '1.0.6', 'upgrade' => null],
            '1.0.6' => ['to' => '1.0.7', 'upgrade' => null],
            '1.0.7' => ['to' => '1.0.8', 'upgrade' => null],
            '1.0.8' => ['to' => '1.0.9', 'upgrade' => null],
            '1.0.9' => ['to' => '1.1.0', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v1_1_0\Config::class],
            '1.1.0' => ['to' => '1.1.1', 'upgrade' => null],
            '1.1.1' => ['to' => '1.2.0', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v1_2_0\Config::class],
            '1.2.0' => ['to' => '1.3.0', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v1_3_0\Config::class],
            '1.3.0' => ['to' => '1.4.0', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v1_4_0\Config::class],
            '1.4.0' => ['to' => '1.4.1', 'upgrade' => null],
            '1.4.1' => ['to' => '1.5.0', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v1_5_0\Config::class],
            '1.5.0' => ['to' => '1.5.1', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v1_5_1\Config::class],
            '1.5.1' => ['to' => '1.5.2', 'upgrade' => null],
            '1.5.2' => ['to' => '1.5.3', 'upgrade' => null],
            '1.5.3' => ['to' => '1.5.4', 'upgrade' => null],
            '1.5.4' => ['to' => '1.5.5', 'upgrade' => null],
            '1.5.5' => ['to' => '1.5.6', 'upgrade' => null],
            '1.5.6' => ['to' => '1.6.0', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v1_6_0\Config::class],
            '1.6.0' => ['to' => '1.7.0', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v1_7_0\Config::class],
            '1.7.0' => ['to' => '1.7.1', 'upgrade' => null],
            '1.7.1' => ['to' => '1.7.2', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v1_7_2\Config::class],
            '1.7.2' => ['to' => '1.7.3', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v1_7_3\Config::class],
            '1.7.3' => ['to' => '1.7.4', 'upgrade' => null],
            '1.7.4' => ['to' => '1.7.5', 'upgrade' => null],
            '1.7.5' => ['to' => '1.7.6', 'upgrade' => null],
            '1.7.6' => ['to' => '1.7.7', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v1_7_7\Config::class],
            '1.7.7' => ['to' => '2.0.0', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v2_0_0\Config::class],
            '2.0.0' => ['to' => '2.0.1', 'upgrade' => null],
            '2.0.1' => ['to' => '2.1.0', 'upgrade' => null],
            '2.1.0' => ['to' => '2.2.0', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v2_2_0\Config::class],
            '2.2.0' => ['to' => '2.2.1', 'upgrade' => null],
            '2.2.1' => ['to' => '2.2.2', 'upgrade' => null],
            '2.2.2' => ['to' => '2.2.3', 'upgrade' => null],
            '2.2.3' => ['to' => '2.2.4', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v2_2_4\Config::class],
            '2.2.4' => ['to' => '2.2.5', 'upgrade' => null],
            '2.2.5' => ['to' => '2.3.0', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v2_3_0\Config::class],
            '2.3.0' => ['to' => '2.4.0', 'upgrade' => null],
            '2.4.0' => ['to' => '2.4.1', 'upgrade' => null],
            '2.4.1' => ['to' => '2.5.0', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v2_5_0\Config::class],
            '2.5.0' => ['to' => '2.6.0', 'upgrade' => \M2E\Kaufland\Setup\Upgrade\v2_6_0\Config::class],
        ];
    }
}
