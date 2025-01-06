<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Product\Unmanaged\Mapping;

class MapProductPopupHtml extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    public function execute()
    {
        $productOtherId = $this->getRequest()->getParam('other_product_id');
        $block = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Listing\Mapping\View::class,
            '',
            [
                'data' => [
                    'other_product_id' => $productOtherId,
                    'grid_url' => '*/product_unmanaged_mapping/mapGrid',
                ],
            ]
        );

        $this->setAjaxContent($block);

        return $this->getResult();
    }
}
