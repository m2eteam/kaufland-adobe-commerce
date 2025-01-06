<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Grid\Column\Renderer;

use M2E\Kaufland\Model\ResourceModel\Product as ProductResource;

class KauflandProductId extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
{
    use \M2E\Kaufland\Block\Adminhtml\Traits\BlockTrait;

    public function render(\Magento\Framework\DataObject $row): string
    {
        return $this->renderGeneral($row, false);
    }

    public function renderExport(\Magento\Framework\DataObject $row): string
    {
        return $this->renderGeneral($row, true);
    }

    public function renderGeneral(\Magento\Framework\DataObject $row, bool $isExport): string
    {
        $kauflandProductId = $this->_getValue($row);
        $storefrontCode = $this->getColumn()->getRendererOptions()['storefront_code'];

        //if ($row->getData('status') == \M2E\Kaufland\Model\Product::STATUS_NOT_LISTED) {
        //    if ($isExport) {
        //        return '';
        //    }
        //
        //    return '<span style="color: gray;">' . __('Not Listed') . '</span>';
        //}

        if ($kauflandProductId === null || $kauflandProductId === '') {
            if ($isExport) {
                return '';
            }

            return __('N/A');
        }
        $creator = $row->getData(ProductResource::COLUMN_IS_KAUFLAND_PRODUCT_CREATOR) ?
            '<br><span style="font-size: 10px; color: grey;">' . __('Product Creator') . '</span>' : '';

        $url = 'https://www.kaufland.' . $storefrontCode . '/product/' . $kauflandProductId;

        return '<a href="' . $url . '" target="_blank">' . $kauflandProductId . '</a>' . $creator;
    }
}
