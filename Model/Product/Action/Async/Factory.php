<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\Action\Async;

use M2E\Kaufland\Model\Product\Action\DefinitionsCollection;

class Factory
{
    private DefinitionsCollection $definitionsCollection;
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(
        DefinitionsCollection $definitionsCollection,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->definitionsCollection = $definitionsCollection;
        $this->objectManager = $objectManager;
    }

    public function createActionStart(string $nick): AbstractProcessStart
    {
        if (!$this->definitionsCollection->has($nick)) {
            throw new \M2E\Kaufland\Model\Exception\Logic("ProcessStart of action '$nick' is not found.");
        }

        $class = $this->definitionsCollection->getStart($nick);

        return $this->objectManager->create($class);
    }

    public function createActionEnd(string $nick): AbstractProcessEnd
    {
        if (!$this->definitionsCollection->has($nick)) {
            throw new \M2E\Kaufland\Model\Exception\Logic("ProcessEnd of action '$nick' is not found.");
        }

        $class = $this->definitionsCollection->getEnd($nick);

        return $this->objectManager->create($class);
    }
}
