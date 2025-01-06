<?php

namespace M2E\Kaufland\Block\Adminhtml\Log\Listing\Product;

abstract class AbstractView extends \M2E\Kaufland\Block\Adminhtml\Log\Listing\AbstractView
{
    protected function getFiltersHtml()
    {
        $html = $this->accountSwitcherBlock->toHtml();

        return $this->getSwitcherHtml($html);
    }

    private function getSwitcherHtml(string $html): string
    {
        return
            '<div class="switcher-separator"></div>'
            . $html;
    }

    public function getListingId()
    {
        return $this->getRequest()->getParam(
            \M2E\Kaufland\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_ID_FIELD,
            false
        );
    }

    public function getListingProductId()
    {
        return $this->getRequest()->getParam(
            \M2E\Kaufland\Block\Adminhtml\Log\Listing\Product\AbstractGrid::LISTING_PRODUCT_ID_FIELD,
            false
        );
    }
}
