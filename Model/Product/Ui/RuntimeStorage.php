<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Ui;

use M2E\Kaufland\Model\Product\Repository;
use M2E\Kaufland\Model\Product;

class RuntimeStorage
{
    /** @var \M2E\Kaufland\Model\Product[] */
    private array $products;
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param int[] $ids
     *
     * @return void
     */
    public function loadByIds(array $ids): void
    {
        $products = [];
        foreach ($this->repository->findByIds($ids) as $product) {
            $products[$product->getId()] = $product;
        }

        $this->products = $products;
    }

    public function findProduct(int $id): ?\M2E\Kaufland\Model\Product
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->products)) {
            return null;
        }

        /** @psalm-suppress RedundantCondition */
        return $this->products[$id] ?? null;
    }

    public function getAll(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->products)) {
            throw new \LogicException('Products was not initialized.');
        }

        return $this->products;
    }
}
