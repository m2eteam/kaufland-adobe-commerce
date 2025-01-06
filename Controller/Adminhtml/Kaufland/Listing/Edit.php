<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing;

class Edit extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractListing
{
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;
    private \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Model\Listing\Ui\RuntimeStorage $uiListingRuntimeStorage
    ) {
        parent::__construct();
        $this->listingRepository = $listingRepository;
        $this->uiListingRuntimeStorage = $uiListingRuntimeStorage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('M2E_Kaufland::listings_items');
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $listing = $this->listingRepository->get($id);
            $this->uiListingRuntimeStorage->setListing($listing);
        } catch (\M2E\Kaufland\Model\Exception\Logic $exception) {
            $this->getMessageManager()->addError($exception->getMessage());

            return $this->_redirect('*/kaufland_listing/index');
        }

        $this->addContent(
            $this->getLayout()->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\Edit::class,
                '',
                ['listing' => $listing],
            ),
        );
        $this->getResultPage()->getConfig()->getTitle()->prepend(
            __('Edit M2E Kaufland Listing "%listing_title" Settings', ['listing_title' => $listing->getTitle()]),
        );

        return $this->getResult();
    }
}
