<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Grid\Column\Filter;

class ListingProductId extends \M2E\Kaufland\Block\Adminhtml\Magento\Grid\Column\Filter\AbstractFilter
{
    protected function _renderOption($option, $value)
    {
        $selected = (($option['value'] == $value && ($value !== null)) ? ' selected="selected"' : '');

        return '<option value="' . $this->escapeHtml($option['value']) . '"' . $selected . '>'
            . $this->escapeHtml($option['label'])
            . '</option>';
    }

    public function getHtml()
    {
        $value = $this->getValue('select');

        $optionsHtml = '';
        foreach ($this->_getOptions() as $option) {
            $optionsHtml .= $this->_renderOption($option, $value);
        }

        $html = <<<HTML
<div>
    <input type="text" name="{$this->_getHtmlName()}[input]" id="{$this->_getHtmlId()}_input"
           value="{$this->getEscapedValue('input')}" class="input-text admin__control-text no-changes"/>
</div>
<div style="margin-top: 5px; display: flex; align-items: center">
    <label style="vertical-align: text-bottom; white-space: nowrap;">{$this->__('Product Creator')}</label>
    <select class="admin__control-select"
            style="margin-left:6px; float:none;"
            name="{$this->_getHtmlName()}[select]" id="{$this->_getHtmlId()}_select">
        {$optionsHtml}
    </select>
</div>

HTML;

        return parent::getHtml() . $html;
    }

    protected function _getOptions()
    {
        return [
            [
                'label' => __('Any'),
                'value' => '',
            ],
            [
                'label' => __('Yes'),
                'value' => 1,
            ],
            [
                'label' => __('No'),
                'value' => 0,
            ],
        ];
    }
}
