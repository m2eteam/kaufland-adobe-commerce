<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Magento\Product;

class ImageFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Image
    {
        return $this->objectManager->create(Image::class);
    }
}
