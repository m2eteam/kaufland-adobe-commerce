<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing;

class Save extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractListing
{
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;
    private \M2E\Core\Helper\Url $urlHelper;
    private \M2E\Kaufland\Model\Listing\UpdateService $listingUpdateService;

    public function __construct(
        \M2E\Kaufland\Model\Listing\UpdateService $listingUpdateService,
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Core\Helper\Url $urlHelper
    ) {
        parent::__construct();

        $this->listingRepository = $listingRepository;
        $this->urlHelper = $urlHelper;
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
        try {
            $listing = $this->listingRepository->get($id);
        } catch (\M2E\Kaufland\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError(__($exception->getMessage()));

            return $this->_redirect('*/kaufland_listing/index');
        }

        try {
            $this->listingUpdateService->update($listing, $post);
        } catch (\M2E\Kaufland\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError(__($exception->getMessage()));

            return $this->_redirect('*/kaufland_listing/index');
        }

        $this->getMessageManager()->addSuccess(__('Listing Settings were saved.'));

        $redirectUrl = $this->urlHelper
            ->getBackUrl(
                'list',
                [],
                ['edit' => ['id' => $id]]
            );

        return $this->_redirect($redirectUrl);
    }
}
