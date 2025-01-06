<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ControlPanel\Inspection;

use Magento\Framework\ObjectManagerInterface;

class HandlerFactory
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param \M2E\Kaufland\Model\ControlPanel\Inspection\Definition $definition
     *
     * @return \M2E\Kaufland\Model\ControlPanel\Inspection\InspectorInterface
     */
    public function create(\M2E\Kaufland\Model\ControlPanel\Inspection\Definition $definition)
    {
        return $this->objectManager->create($definition->getHandler());
    }
}
