<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Category;

class DictionaryFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): Dictionary
    {
        return $this->objectManager->create(Dictionary::class);
    }
}
