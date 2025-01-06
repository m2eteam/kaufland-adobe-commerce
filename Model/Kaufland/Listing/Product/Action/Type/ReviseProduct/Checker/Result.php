<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Type\ReviseProduct\Checker;

class Result
{
    /** @var \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Configurator */
    private $configurator;
    /** @var array */
    private $tags;

    public function __construct(
        \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Configurator $configurator,
        array $tags
    ) {
        $this->configurator = $configurator;
        $this->tags = $tags;
    }

    public function getConfigurator(): \M2E\Kaufland\Model\Kaufland\Listing\Product\Action\Configurator
    {
        return $this->configurator;
    }

    public function getTags(): array
    {
        return $this->tags;
    }
}
