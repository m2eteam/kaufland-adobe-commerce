<?php

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\Description;

class Save extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractListing
{
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;
    private \M2E\Kaufland\Model\Listing\UpdateService $listingUpdateService;

    public function __construct(
        \M2E\Kaufland\Model\Listing\UpdateService $listingUpdateService,
        \M2E\Kaufland\Model\Listing\Repository $listingRepository
    ) {
        parent::__construct();

        $this->listingRepository = $listingRepository;
        $this->listingUpdateService = $listingUpdateService;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::listings_items');
    }

    public function execute()
    {
        if (!$post = $this->getRequest()->getParams()) {
            $this->_redirect('*/kaufland_listing/index');
        }

        $id = $this->getRequest()->getParam('id');
        $wizardId = $this->getRequest()->getParam('wizard_id');

        if (!$this->getRequest()->getParam('template_description_id')) {
            $this->getMessageManager()->addError(
                __('Description Policy is required. Please add a valid Description Policy to proceed.')
            );

            $url = $this->getUrl(
                '*/listing_wizard_description/view',
                ['id' => $wizardId]
            );

            return $this->_redirect($url);
        }

        try {
            $listing = $this->listingRepository->get($id);
        } catch (\M2E\Kaufland\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError(__($exception->getMessage()));
        }

        try {
            $this->listingUpdateService->update($listing, $post);
        } catch (\M2E\Kaufland\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError(__($exception->getMessage()));
        }

        $urlCompleteStep = $this->getUrl(
            '*/listing_wizard_description/completeStep',
            ['id' => $wizardId]
        );

        return $this->_redirect($urlCompleteStep);
    }
}
