<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction;

class GetAutoCategoryFormHtml extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction
{
    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Helper\Data\GlobalData $globalData,
        $context = null
    ) {
        parent::__construct($context);
        $this->listingRepository = $listingRepository;
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function execute()
    {
        $listing = $this->listingRepository
            ->get((int)$this->getRequest()->getParam('id'));

        $block = $this
            ->getLayout()
            ->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\AutoAction\Mode\Category\Form::class,
                '',
                [
                    'listing' => $listing
                ]
            );

        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
