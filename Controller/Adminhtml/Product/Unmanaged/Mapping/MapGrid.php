<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Product\Unmanaged\Mapping;

class MapGrid extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        $accountId = (int)$this->getRequest()->getParam('account_id');

        $block = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Listing\Mapping\Grid::class,
            '',
            [
                'data' => [
                    'other_product_id' => (int)$this->getRequest()->getParam('other_product_id'),
                    'grid_url' => '*/product_unmanaged_mapping/mapGrid',
                    'account_id' => $accountId
                ],
            ]
        );

        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
