<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Kaufland\Template\Category;

class ChangeProcessorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): ChangeProcessor
    {
        return $this->objectManager->create(ChangeProcessor::class);
    }
}
