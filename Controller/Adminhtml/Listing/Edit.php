<?php

namespace M2E\Kaufland\Controller\Adminhtml\Listing;

class Edit extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    /** @var \M2E\Kaufland\Helper\Data\GlobalData */
    private $globalData;
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Helper\Data\GlobalData $globalData,
        \M2E\Kaufland\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);

        $this->globalData = $globalData;
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (empty($params['id'])) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listing = $this->listingRepository->get($params['id']);

        if ($this->getRequest()->isPost()) {
            $listing->addData($params)->save();

            return $this->getResult();
        }

        $this->globalData->setValue('edit_listing', $listing);

        $this->setAjaxContent(
            $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Listing\Edit::class)
        );

        return $this->getResult();
    }
}
