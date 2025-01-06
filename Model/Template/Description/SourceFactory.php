<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template\Description;

class SourceFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Source
    {
        return $this->objectManager->create(Source::class);
    }
}
