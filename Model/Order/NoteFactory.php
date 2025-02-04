<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Order;

class NoteFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Note
    {
        return $this->objectManager->create(Note::class);
    }
}
