<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing;

class DeleteService
{
    private \M2E\Kaufland\Model\Processing\DeleteService $processingDeleteService;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;
    private \M2E\Kaufland\Model\Product\DeleteService $productDeleteService;
    private \M2E\Kaufland\Model\Listing\LogService $listingLogService;
    private \M2E\Kaufland\Model\Product\Repository $productRepository;
    private \M2E\Kaufland\Model\Listing\Wizard\DeleteService $wizardDeleteService;
    private \M2E\Kaufland\Model\Listing\Auto\Category\Group\DeleteService $autoCategoryGroupDeleteService;
    private \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $autoCategoryGroupRepository;

    public function __construct(
        \M2E\Kaufland\Model\Processing\DeleteService $processingDeleteService,
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Model\Product\DeleteService $productDeleteService,
        \M2E\Kaufland\Model\Listing\LogService $listingLogService,
        \M2E\Kaufland\Model\Product\Repository $productRepository,
        \M2E\Kaufland\Model\Listing\Wizard\DeleteService $wizardDeleteService,
        \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $autoCategoryGroupRepository,
        \M2E\Kaufland\Model\Listing\Auto\Category\Group\DeleteService $autoCategoryGroupDeleteService
    ) {
        $this->processingDeleteService = $processingDeleteService;
        $this->listingRepository = $listingRepository;
        $this->productDeleteService = $productDeleteService;
        $this->listingLogService = $listingLogService;
        $this->productRepository = $productRepository;
        $this->wizardDeleteService = $wizardDeleteService;
        $this->autoCategoryGroupRepository = $autoCategoryGroupRepository;
        $this->autoCategoryGroupDeleteService = $autoCategoryGroupDeleteService;
    }

    public function isAllowed(\M2E\Kaufland\Model\Listing $listing): bool
    {
        return $this->productRepository->getCountListedProductsForListing($listing) === 0
            && !$this->listingRepository->hasProductsInSomeAction($listing);
    }

    public function process(\M2E\Kaufland\Model\Listing $listing, bool $isForce = false): void
    {
        if (!$isForce && !$this->isAllowed($listing)) {
            return;
        }

        $this->processingDeleteService->deleteByObjAndObjId(
            \M2E\Kaufland\Model\Listing::LOCK_NICK,
            $listing->getId(),
        );

        $this->deleteProducts($listing);
        $this->deleteAutoCategoryGroups($listing);
        $this->wizardDeleteService->removeByListing($listing);

        $this->listingLogService->addListing(
            $listing,
            \M2E\Core\Helper\Data::INITIATOR_UNKNOWN,
            \M2E\Kaufland\Model\Listing\Log::ACTION_DELETE_LISTING,
            null,
            (string)__('Listing was deleted'),
            \M2E\Kaufland\Model\Log\AbstractModel::TYPE_INFO
        );

        $this->listingRepository->remove($listing);
    }

    private function deleteProducts(\M2E\Kaufland\Model\Listing $listing): void
    {
        foreach ($listing->getProducts() as $listingProduct) {
            $this->productDeleteService->process($listingProduct);
        }
    }

    private function deleteAutoCategoryGroups(\M2E\Kaufland\Model\Listing $listing)
    {
        $autoCategoryGroups = $this->autoCategoryGroupRepository->getByListingId($listing->getId());
        foreach ($autoCategoryGroups as $autoCategoryGroup) {
            $this->autoCategoryGroupDeleteService->execute($autoCategoryGroup);
        }
    }
}
