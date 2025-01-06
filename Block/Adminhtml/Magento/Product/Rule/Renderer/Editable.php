<?php

namespace M2E\Kaufland\Block\Adminhtml\Magento\Product\Rule\Renderer;

use M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class \M2E\Kaufland\Block\Adminhtml\Magento\Product\Rule\Renderer\Editable
 */
class Editable extends AbstractBlock implements \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    protected $translateInline;

    //########################################
    private \Magento\Framework\Escaper $escaper;

    public function __construct(
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Translate\Inline $translateInline,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->translateInline = $translateInline;
        parent::__construct($context, $data);
        $this->escaper = $escaper;
    }

    //########################################

    /**
     * Render element
     *
     * @param AbstractElement $element
     *
     * @return string
     * @see \Magento\Framework\Data\Form\Element\Renderer\RendererInterface::render()
     */
    public function render(AbstractElement $element)
    {
        $element->addClass('element-value-changer');
        $valueName = $element->getValueName();

        if ($element instanceof \Magento\Framework\Data\Form\Element\Select && $valueName == '...') {
            $optionValues = $element->getValues();

            foreach ($optionValues as $option) {
                if ($option['value'] === '') {
                    $valueName = $option['label'];
                }
            }
        }

        if (trim($valueName) === '') {
            $valueName = '...';
        }

        if ($element->getShowAsText()) {
            $html = ' <input type="hidden" class="hidden" id="' . $element->getHtmlId()
                . '" name="' . $element->getName() . '" value="' . $element->getValue() . '"/> '
                . $this->escaper->escapeHtml($valueName) . '&nbsp;';
        } else {
            $html = ' <span class="rule-param"'
                . ($element->getParamId() ? ' id="' . $element->getParamId() . '"' : '') . '>'
                . '<a href="javascript:void(0)" class="label">';

            $html .= $this->translateInline->isAllowed() ? $this->escapeHtml($valueName) :
                $this->escapeHtml($this->filterManager->truncate($valueName, ['length' => 33]));

            $html .= '</a><span class="element"> ' . $element->getElementHtml();

            if ($element->getExplicitApply()) {
                $html .= ' <a href="javascript:void(0)" class="rule-param-apply"><img src="'
                    . $this->_assetRepo->getUrl('M2E_Core::images/rule_component_apply.gif')
                    . '" class="v-middle" alt="'
                    . __('Apply') . '" title="' . __('Apply') . '" /></a> ';
            }

            $html .= '</span></span>&nbsp;';
        }

        return $html;
    }

    //########################################
}
