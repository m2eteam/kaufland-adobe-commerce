<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Grid\Column\Renderer;

class ItemId extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Text
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
        $itemId = $this->_getValue($row);

        if ($row->getData('status') == \M2E\Kaufland\Model\Product::STATUS_NOT_LISTED) {
            if ($isExport) {
                return '';
            }

            return '<span style="color: gray;">' . __('Not Listed') . '</span>';
        }

        if ($itemId === null || $itemId === '') {
            if ($isExport) {
                return '';
            }

            return __('N/A');
        }

        return $itemId;
    }
}
