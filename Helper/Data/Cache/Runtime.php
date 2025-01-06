<?php

declare(strict_types=1);

namespace M2E\Kaufland\Helper\Data\Cache;

class Runtime implements \M2E\Kaufland\Helper\Data\Cache\BaseInterface
{
    private array $cacheStorage = [];

    /**
     * @inheritDoc
     */
    public function getValue($key)
    {
        return $this->cacheStorage[$key]['data'] ?? null;
    }

    /**
     * @inheritDoc
     */
    public function setValue($key, $value, array $tags = [], $lifetime = null): void
    {
        $this->cacheStorage[$key] = [
            'data' => $value,
            'tags' => $tags,
        ];
    }

    // ----------------------------------------

    /**
     * @inheritDoc
     */
    public function removeValue($key): void
    {
        unset($this->cacheStorage[$key]);
    }

    /**
     * @inheritDoc
     */
    public function removeTagValues($tag): void
    {
        foreach ($this->cacheStorage as $key => $data) {
            if (!in_array($tag, $data['tags'])) {
                continue;
            }

            unset($this->cacheStorage[$key]);
        }
    }

    /**
     * @inheritDoc
     */
    public function removeAllValues(): void
    {
        $this->cacheStorage = [];
    }
}
