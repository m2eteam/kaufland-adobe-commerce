<?php

namespace M2E\Kaufland\Model\Template\SellingFormat;

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
