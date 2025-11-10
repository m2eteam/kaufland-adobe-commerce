<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction;

class Reset extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction
{
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;
    private \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $autoCategoryGroupRepository;
    private \M2E\Kaufland\Model\Listing\Auto\Category\Group\DeleteService $autoCategoryGroupDeleteService;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $autoCategoryGroupRepository,
        \M2E\Kaufland\Model\Listing\Auto\Category\Group\DeleteService $autoCategoryGroupDeleteService,
        $context = null
    ) {
        parent::__construct($context);
        $this->listingRepository = $listingRepository;
        $this->autoCategoryGroupRepository = $autoCategoryGroupRepository;
        $this->autoCategoryGroupDeleteService = $autoCategoryGroupDeleteService;
    }

    public function execute()
    {
        $listing = $this->listingRepository
            ->get((int)$this->getRequest()->getParam('id'));

        $listing->resetAutoAction();
        $this->listingRepository->save($listing);

        $autoCategoryGroups = $this->autoCategoryGroupRepository->getByListingId($listing->getId());
        foreach ($autoCategoryGroups as $autoCategoryGroup) {
            $this->autoCategoryGroupDeleteService->execute($autoCategoryGroup);
        }
    }
}
