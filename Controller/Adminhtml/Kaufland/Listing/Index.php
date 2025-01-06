<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Listing;

class Index extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractListing
{
    public function execute()
    {
        if ($this->isAjax()) {
            /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\ItemsByListing\Grid $grid */
            $grid = $this->getLayout()->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\ItemsByListing\Grid::class
            );
            $this->setAjaxContent($grid);

            return $this->getResult();
        }

        /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\ItemsByListing $block */
        $block = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\ItemsByListing::class
        );
        $this->addContent($block);

        $this->getResultPage()->getConfig()->getTitle()->prepend(__('Items By Listing'));
        $this->setPageHelpLink('https://docs-m2.m2epro.com/m2e-kaufland-listings');

        return $this->getResult();
    }
}
