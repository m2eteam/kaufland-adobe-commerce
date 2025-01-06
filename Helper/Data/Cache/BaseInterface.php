<?php

declare(strict_types=1);

namespace M2E\Kaufland\Helper\Data\Cache;

interface BaseInterface
{
    /**
     * @param string $key
     *
     * @return mixed
     */
    public function getValue($key);

    /**
     * @param string $key
     * @param mixed $value
     * @param array $tags
     * @param int|null $lifetime
     *
     * @return void
     */
    public function setValue($key, $value, array $tags = [], $lifetime = null): void;

    /**
     * @param string $key
     *
     * @return void
     */
    public function removeValue($key): void;

    /**
     * @param string $tag
     *
     * @return void
     */
    public function removeTagValues($tag): void;

    /**
     * @return void
     */
    public function removeAllValues(): void;
}
