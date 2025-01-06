<?php

namespace M2E\Kaufland\Model\Kaufland\Order\Item;

class ImporterFactory
{
    private \Magento\Framework\ObjectManagerInterface $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(\M2E\Kaufland\Model\Order\Item $orderItem, array $data = []): Importer
    {
        $data['orderItem'] = $orderItem;

        return $this->objectManager->create(Importer::class, $data);
    }
}
