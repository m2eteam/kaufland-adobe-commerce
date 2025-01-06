<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ControlPanel\Inspection\Inspector;

use M2E\Kaufland\Model\ControlPanel\Inspection\InspectorInterface;

class MagentoSettings implements InspectorInterface
{
    private \M2E\Kaufland\Model\ControlPanel\Inspection\Issue\Factory $issueFactory;

    public function __construct(
        \M2E\Kaufland\Model\ControlPanel\Inspection\Issue\Factory $issueFactory
    ) {
        $this->issueFactory = $issueFactory;
    }

    public function process(): array
    {
        $issues = [];

        if (!extension_loaded('gd') || !function_exists('gd_info')) {
            $issues[] = $this->issueFactory->create(
                'GD library is not installed.'
            );
        }

        if (\M2E\Core\Helper\Date::getTimezone()->getDefaultTimezone() !== 'UTC') {
            $issues[] = $this->issueFactory->create(
                'Non-default Magento timezone set.',
                \M2E\Core\Helper\Date::getTimezone()->getDefaultTimezone()
            );
        }

        if (\M2E\Core\Helper\Client\Cache::isApcAvailable()) {
            $issues[] = $this->issueFactory->create(
                'APC Cache is enabled.'
            );
        }

        if (\M2E\Core\Helper\Client\Cache::isMemcachedAvailable()) {
            $issues[] = $this->issueFactory->create(
                'Memchached Cache is enabled.'
            );
        }

        if (\M2E\Core\Helper\Client\Cache::isRedisAvailable()) {
            $issues[] = $this->issueFactory->create(
                'Redis Cache is enabled.'
            );
        }

        return $issues;
    }
}
