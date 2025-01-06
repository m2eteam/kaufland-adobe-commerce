<?php

namespace M2E\Kaufland\Controller\Adminhtml\Product\Unmanaged\Moving;

class MoveToListingGrid extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        $block = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Listing\Moving\Grid::class,
            '',
            [
                'accountId' => (int)$this->getRequest()->getParam('account_id'),
                'storefrontId' => (int)$this->getRequest()->getParam('storefront_id'),
                'data' => [
                    'grid_url' => $this->getUrl(
                        '*/listing_other_moving/moveToListingGrid',
                        ['_current' => true]
                    ),
                ],
            ]
        );

        $this->setAjaxContent($block->toHtml());

        return $this->getResult();
    }
}
