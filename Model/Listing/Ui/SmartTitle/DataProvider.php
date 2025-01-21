<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Ui\SmartTitle;

class DataProvider implements \M2E\Core\Model\Ui\Widget\SmartTitle\DataProviderInterface
{
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository
    ) {
        $this->listingRepository = $listingRepository;
    }

    /**
     * @return \M2E\Core\Model\Ui\Widget\SmartTitle\Item[]
     */
    public function getItems(): array
    {
        $listings = $this->listingRepository->getAll();
        $result = [];

        foreach ($listings as $listing) {
            $result[] = new \M2E\Core\Model\Ui\Widget\SmartTitle\Item($listing->getId(), $listing->getTitle());
        }

        return $result;
    }
}
