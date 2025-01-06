<?php

namespace M2E\Kaufland\Block\Adminhtml\Magento\Button;

/**
 * Class \M2E\Kaufland\Block\Adminhtml\Magento\Button\SplitButton
 */
class SplitButton extends \Magento\Backend\Block\Widget\Button\SplitButton
{
    //########################################

    /**
     * Retrieve button attributes html
     * @return string
     */
    public function getButtonAttributesHtml()
    {
        $disabled = $this->getDisabled() ? 'disabled' : '';
        $title = $this->getTitle();
        if (!$title) {
            $title = $this->getLabel();
        }
        $classes = [];
        $classes[] = 'action-default';
        $classes[] = 'primary';

        if ($this->getClass()) {
            $classes[] = $this->getClass();
        }
        if ($disabled) {
            $classes[] = $disabled;
        }

        $onclick = $this->getOnclick();
        $attributes = [
            'id' => $this->getId() . '-button',
            'title' => $title,
            'class' => join(' ', $classes),
            'disabled' => $disabled,
            'style' => $this->getStyle(),
            'onclick' => !empty($onclick) ? $onclick : '',
        ];

        if ($this->getDataAttribute()) {
            $this->_getDataAttributes($this->getDataAttribute(), $attributes);
        }

        $html = $this->_getAttributesString($attributes);
        $html .= $this->getUiId();

        return $html;
    }

    //########################################
}
