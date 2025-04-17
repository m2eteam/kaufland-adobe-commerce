<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Log\Listing\Product;

class Index extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Log\AbstractListing
{
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository
    ) {
        parent::__construct();

        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $listingId = $this->getRequest()->getParam(
            \M2E\Kaufland\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD,
            false
        );

        if ($listingId) {
            $listing = $this->listingRepository->find($listingId);

            if ($listing === null) {
                $this->getMessageManager()->addErrorMessage(__('Listing does not exist.'));

                return $this->_redirect('*/*/index');
            }

            $this->getResult()->getConfig()->getTitle()->prepend(
                __(
                    '%extension_title Listing "%s" Log',
                    [
                        's' => $listing->getTitle(),
                        'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
                    ]
                ),
            );
        } else {
            $this->getResult()->getConfig()->getTitle()->prepend(__('Listings Logs & Events'));
        }

        $this->addContent(
            $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Log\Listing\Product\View::class)
        );

        return $this->getResult();
    }
}
