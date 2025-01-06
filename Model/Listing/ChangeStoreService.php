<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing;

class ChangeStoreService
{
    private \M2E\Kaufland\Model\Product\Repository $productRepository;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;
    private \M2E\Kaufland\Model\InstructionService $instructionService;

    public function __construct(
        \M2E\Kaufland\Model\Product\Repository $productRepository,
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Model\InstructionService $instructionService
    ) {
        $this->productRepository = $productRepository;
        $this->listingRepository = $listingRepository;
        $this->instructionService = $instructionService;
    }

    public function change(\M2E\Kaufland\Model\Listing $listing, int $storeId): void
    {
        $this->updateStoreViewInListing($listing, $storeId);
        $this->addInstruction($listing->getId());
    }

    private function updateStoreViewInListing(\M2E\Kaufland\Model\Listing $listing, int $storeId): void
    {
        $listing->setStoreId($storeId);
        $this->listingRepository->save($listing);
    }

    private function addInstruction(int $listingId): void
    {
        $listingProductInstructionsData = [];

        foreach ($this->productRepository->findIdsByListingId($listingId) as $itemId) {
            $listingProductInstructionsData[] = [
                'listing_product_id' => $itemId,
                'type' => \M2E\Kaufland\Model\Listing::INSTRUCTION_TYPE_CHANGE_LISTING_STORE_VIEW,
                'initiator' => \M2E\Kaufland\Model\Listing::INSTRUCTION_INITIATOR_CHANGED_LISTING_STORE_VIEW,
                'priority' => 20,
            ];
        }

        $this->instructionService->createBatch($listingProductInstructionsData);
    }
}
