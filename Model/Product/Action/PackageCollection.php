<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action;

class PackageCollection
{
    /** @var Package[] */
    private array $packages = [];

    public function isEmpty(): bool
    {
        return empty($this->packages);
    }

    public function add(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Product\Action\Configurator $configurator
    ): self {
        $this->packages[$product->getId()] = new Package($product, $configurator);

        return $this;
    }

    public function remove(int $productId): self
    {
        if (isset($this->packages[$productId])) {
            unset($this->packages[$productId]);
        }

        return $this;
    }

    /**
     * @return Package[]
     */
    public function getAll(): array
    {
        return array_values($this->packages);
    }
}
