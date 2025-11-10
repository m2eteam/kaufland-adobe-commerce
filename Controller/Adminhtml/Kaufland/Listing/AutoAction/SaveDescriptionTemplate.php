<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction;

class SaveDescriptionTemplate extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction
{
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        $context = null
    ) {
        parent::__construct($context);
        $this->listingRepository = $listingRepository;
    }

    public function execute()
    {
        $listing = $this->listingRepository->get((int)$this->getRequest()->getParam('listing_id'));

        $templateDescriptionId = $this->getRequest()->getParam('template_description_id');
        if (!empty($templateDescriptionId)) {
            $listing->setTemplateDescriptionId((int)$templateDescriptionId);
            $this->listingRepository->save($listing);
        }

        return $this->getResult();
    }
}
