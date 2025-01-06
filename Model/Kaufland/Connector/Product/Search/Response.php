<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Connector\Product\Search;

class Response
{
    /** @var \M2E\Kaufland\Model\Kaufland\Connector\Product\Search\Product[] */
    private array $product;

    public function __construct(array $product)
    {
        $this->product = $product;
    }

    /**
     * @return \M2E\Kaufland\Model\Kaufland\Connector\Product\Search\Product[]
     */
    public function getProducts(): array
    {
        return $this->product;
    }
}
