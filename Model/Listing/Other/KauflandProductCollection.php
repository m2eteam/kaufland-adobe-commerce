<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Other;

class KauflandProductCollection
{
    /** @var KauflandProduct[] */
    private array $products = [];

    public function empty(): bool
    {
        return empty($this->products);
    }

    public function has(string $offerId): bool
    {
        return isset($this->products[$offerId]);
    }

    public function add(KauflandProduct $product): void
    {
        $this->products[$product->getOfferId()] = $product;
    }

    public function get(string $offerId): KauflandProduct
    {
        return $this->products[$offerId];
    }

    public function remove(string $offerId): void
    {
        unset($this->products[$offerId]);
    }

    /**
     * @return \M2E\Kaufland\Model\Listing\Other\KauflandProduct[]
     */
    public function getAll(): array
    {
        return array_values($this->products);
    }

    public function getOfferIds(): array
    {
        return array_keys($this->products);
    }
}
