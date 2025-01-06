<?php

namespace M2E\Kaufland\Model\ResourceModel\Listing\Log;

use M2E\Kaufland\Model\ResourceModel\Listing\Log\Collection as ListingLogCollection;

class CollectionFactory
{
    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function create(): ListingLogCollection
    {
        return $this->objectManager->create(ListingLogCollection::class);
    }
}
