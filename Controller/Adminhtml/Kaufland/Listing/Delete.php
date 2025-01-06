<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractListing;

class Delete extends AbstractListing
{
    private \M2E\Kaufland\Model\Listing\DeleteService $deleteService;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Model\Listing\DeleteService $deleteService
    ) {
        parent::__construct();

        $this->deleteService = $deleteService;
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $ids = $this->getRequestIds();
        $backUrl = '*/kaufland_listing/index';

        if (count($ids) == 0) {
            $this->getMessageManager()->addError(__('Please select Item(s) to remove.'));
            $this->_redirect($backUrl);

            return;
        }

        $result = [
            'deleted' => 0,
            'locked' => 0,
        ];
        foreach ($ids as $id) {
            $listing = $this->listingRepository->get((int)$id);
            if ($this->deleteService->isExistListedProducts($listing)) {
                $result['locked']++;
            } else {
                $this->deleteService->process($listing);
                $result['deleted']++;
            }
        }

        if ($result['deleted']) {
            $this->getMessageManager()->addSuccess(
                sprintf('%d Listing(s) were deleted', $result['deleted'])
            );
        }

        if ($result['locked']) {
            $this->getMessageManager()->addError(
                sprintf(
                    '%d Listing(s) cannot be deleted because they have Items with Status "In Progress" or "Listed".',
                    $result['locked']
                )
            );
        }

        $this->_redirect($backUrl);
    }
}
