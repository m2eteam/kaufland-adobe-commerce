<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction;

class ValidateListingDescription extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction
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
        $listing = $this->listingRepository->get((int)$this->getRequest()->getParam('id'));

        $popupContent = null;
        if (!$listing->hasDescriptionPolicy()) {
            $popupContent = $this
                ->getLayout()
                ->createBlock(
                    \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\AutoAction\SetDescriptionPolicy::class,
                    '',
                    ['listing' => $listing]
                )->toHtml();
        }

        $this->getRawResult()->setHeader('Content-Type', 'application/json');
        $this->setJsonContent(['popup_content' => $popupContent]);

        return $this->getResult();
    }
}
