<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Grid\Column\Filter;

class OrderId extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Text
{
    public function getValue($index = null)
    {
        if ($index === null) {
            $value = $this->getData('value');

            return is_array($value) ? $value : ['value' => $value];
        }

        return $this->getData('value', $index);
    }

    public function getEscapedValue($index = null)
    {
        $value = $this->getValue($index);
        if ($index === null) {
            $value = $value['value'];
        }

        return $this->escapeHtml($value);
    }
}
