<?php

namespace M2E\Kaufland\Model\Template\SellingFormat;

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
