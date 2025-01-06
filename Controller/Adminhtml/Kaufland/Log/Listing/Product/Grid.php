<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Log\Listing\Product;

use M2E\Kaufland\Block\Adminhtml\Log\Listing\View;

class Grid extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Log\AbstractListing
{
    private \M2E\Kaufland\Helper\Data\Session $sessionHelper;

    public function __construct(
        \M2E\Kaufland\Helper\Data\Session $sessionHelper
    ) {
        parent::__construct();

        $this->sessionHelper = $sessionHelper;
    }

    public function execute()
    {
        $sessionViewMode = $this->sessionHelper->getValue(
            \M2E\Kaufland\Helper\View\Kaufland::NICK . '_log_listing_view_mode',
        );

        if ($sessionViewMode === null) {
            $sessionViewMode = View\Switcher::VIEW_MODE_SEPARATED;
        }

        $viewMode = $this->getRequest()->getParam(
            'view_mode',
            $sessionViewMode,
        );

        if ($viewMode === View\Switcher::VIEW_MODE_GROUPED) {
            $gridClass = \M2E\Kaufland\Block\Adminhtml\Kaufland\Log\Listing\Product\View\Grouped\Grid::class;
        } else {
            $gridClass = \M2E\Kaufland\Block\Adminhtml\Kaufland\Log\Listing\Product\View\Separated\Grid::class;
        }

        $block = $this->getLayout()->createBlock($gridClass);
        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
