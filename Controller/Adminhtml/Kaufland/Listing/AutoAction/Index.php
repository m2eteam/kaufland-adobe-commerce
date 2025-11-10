<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction;

class Index extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing\AutoAction
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
        $listing = $this->listingRepository
            ->get((int)$this->getRequest()->getParam('id'));

        $autoMode = $this->getRequest()->getParam('auto_mode');
        if (empty($autoMode)) {
            $autoMode = $listing->getAutoMode();
        }

        switch ($autoMode) {
            case \M2E\Kaufland\Model\Listing::AUTO_MODE_GLOBAL:
                $blockName = \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\AutoAction\Mode\GlobalMode::class;
                break;
            case \M2E\Kaufland\Model\Listing::AUTO_MODE_WEBSITE:
                $blockName = \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\AutoAction\Mode\Website::class;
                break;
            case \M2E\Kaufland\Model\Listing::AUTO_MODE_CATEGORY:
                $blockName = \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\AutoAction\Mode\Category::class;
                break;
            default:
                $blockName = \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\AutoAction\Mode::class;
                break;
        }

        $this->setJsonContent([
            'mode' => $autoMode,
            'html' => $this
                ->getLayout()
                ->createBlock($blockName, '', ['listing' => $listing])->toHtml(),
        ]);

        return $this->getResult();
    }
}
