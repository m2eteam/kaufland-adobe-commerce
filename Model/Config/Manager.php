<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Config;

class Manager
{
    private \M2E\Core\Model\Config\Adapter $adapter;
    private \M2E\Core\Model\Config\AdapterFactory $configAdapterFactory;
    private \M2E\Kaufland\Helper\Data\Cache\Permanent $permanentCache;

    public function __construct(
        \M2E\Core\Model\Config\AdapterFactory $configAdapterFactory,
        \M2E\Kaufland\Helper\Data\Cache\Permanent $permanentCache
    ) {
        $this->configAdapterFactory = $configAdapterFactory;
        $this->permanentCache = $permanentCache;
    }

    public function getGroupValue(string $group, string $key)
    {
        return $this->getAdapter()->get($group, $key);
    }

    public function setGroupValue(string $group, string $key, $value): void
    {
        $this->getAdapter()->set($group, $key, $value);
    }

    public function getAdapter(): \M2E\Core\Model\Config\Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->adapter)) {
            $this->adapter = $this->configAdapterFactory->create(
                \M2E\Kaufland\Helper\Module::IDENTIFIER,
                $this->getCacheAdapter()
            );
        }

        return $this->adapter;
    }

    private function getCacheAdapter(): \M2E\Core\Model\Cache\Adapter
    {
        return $this->permanentCache->getAdapter();
    }
}
