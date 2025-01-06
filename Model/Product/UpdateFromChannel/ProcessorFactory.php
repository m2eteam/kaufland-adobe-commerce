<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Product\UpdateFromChannel;

class ProcessorFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Kaufland\Model\Product $product,
        \M2E\Kaufland\Model\Listing\Other\KauflandProduct $channelProduct
    ): Processor {
        return $this->objectManager->create(
            Processor::class,
            [
                'product' => $product,
                'channelProduct' => $channelProduct,
            ],
        );
    }
}
