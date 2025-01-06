<?php

declare(strict_types=1);

namespace M2E\Kaufland\Helper\Data\Cache;

use M2E\Core\Model\Cache\Adapter;

class Permanent implements \M2E\Kaufland\Helper\Data\Cache\BaseInterface
{
    private \M2E\Core\Model\Cache\AdapterFactory $cacheAdapterFactory;

    private \M2E\Core\Model\Cache\Adapter $cacheAdapter;

    public function __construct(
        \M2E\Core\Model\Cache\AdapterFactory $cacheAdapterFactory
    ) {
        $this->cacheAdapterFactory = $cacheAdapterFactory;
    }

    // ----------------------------------------

    /**
     * @inheritDoc
     */
    public function getValue($key)
    {
        return $this->getAdapter()->get($key);
    }

    /**
     * @inheritDoc
     */
    public function setValue($key, $value, array $tags = [], $lifetime = null): void
    {
        if ($lifetime === null || (int)$lifetime <= 0) {
            $lifetime = 60 * 60 * 24;
        }

        $this->cacheAdapter->set($key, $value, $lifetime, $tags);
    }

    // ----------------------------------------

    /**
     * @inheritDoc
     */
    public function removeValue($key): void
    {
        $this->cacheAdapter->remove($key);
    }

    /**
     * @inheritDoc
     */
    public function removeTagValues($tag): void
    {
        $this->cacheAdapter->removeByTag($tag);
    }

    /**
     * @inheritDoc
     */
    public function removeAllValues(): void
    {
        $this->cacheAdapter->removeAllValues();
    }

    public function getAdapter(): Adapter
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->cacheAdapter)) {
            $this->cacheAdapter = $this->cacheAdapterFactory->create(\M2E\Kaufland\Helper\Module::IDENTIFIER);
        }

        return $this->cacheAdapter;
    }
}
