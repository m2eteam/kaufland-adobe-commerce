<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action;

class Package
{
    private \M2E\Kaufland\Model\Product $product;
    private Configurator $actionConfigurator;

    public function __construct(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Configurator $actionConfigurator
    ) {
        $this->product = $product;
        $this->actionConfigurator = $actionConfigurator;
    }

    public function getProduct(): \M2E\Kaufland\Model\Product
    {
        return $this->product;
    }

    public function getActionConfigurator(): Configurator
    {
        return $this->actionConfigurator;
    }
}
