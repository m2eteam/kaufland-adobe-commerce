<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Auto\Actions;

use Magento\Framework\ObjectManagerInterface;

class ListingFactory
{
    private ObjectManagerInterface $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param \M2E\Kaufland\Model\Listing $listing
     *
     * @return \M2E\Kaufland\Model\Listing\Auto\Actions\Listing
     */
    public function create(\M2E\Kaufland\Model\Listing $listing): \M2E\Kaufland\Model\Listing\Auto\Actions\Listing
    {
        return $this->objectManager->create(
            \M2E\Kaufland\Model\Listing\Auto\Actions\KauflandListing::class,
            ['listing' => $listing]
        );
    }
}
