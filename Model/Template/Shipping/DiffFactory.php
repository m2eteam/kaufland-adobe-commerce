<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\Shipping;

class DiffFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Diff
    {
        return $this->objectManager->create(Diff::class);
    }
}
