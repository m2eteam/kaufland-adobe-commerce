<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Instruction\Handler;

class InputFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(
        \M2E\Kaufland\Model\Product $product,
        array $instructions
    ): Input {
        return $this->objectManager->create(
            Input::class,
            [
                'product' => $product,
                'instructions' => $instructions,
            ],
        );
    }
}
