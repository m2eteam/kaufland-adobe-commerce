<?php

namespace M2E\Kaufland\Model\Listing;

class Delete
{
    private Repository $listingRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository
    ) {
        $this->listingRepository = $listingRepository;
    }

    /**
     * @param int|string[] $listingIds
     *
     * @return array{deleted: int, locked: int}
     */
    public function process(array $listingIds): array
    {
        $result = [
            'deleted' => 0,
            'locked' => 0,
        ];

        foreach ($listingIds as $id) {
            $listing = $this->listingRepository->get($id);
            if ($listing->isLocked()) {
                $result['locked']++;
            } else {
                $listing->delete();
                $result['deleted']++;
            }
        }

        return $result;
    }
}
