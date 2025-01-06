<?php

namespace M2E\Kaufland\Model\Kaufland\Listing\Product\Description;

class RendererFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\Kaufland\Model\Product $listingProduct): Renderer
    {
        return $this->objectManager->create(Renderer::class, [
            'listingProduct' => $listingProduct,
        ]);
    }
}
