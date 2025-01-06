<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ControlPanel\Inspection\Issue;

use Magento\Framework\ObjectManagerInterface;

class Factory
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string|null $message
     * @param array|string|null $metadata
     *
     * @return \M2E\Kaufland\Model\ControlPanel\Inspection\Issue
     */
    public function create($message, $metadata = null)
    {
        return $this->objectManager->create(
            \M2E\Kaufland\Model\ControlPanel\Inspection\Issue::class,
            [
                'message' => $message,
                'metadata' => $metadata,
            ]
        );
    }
}
