<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Grid\Column\Filter;

use M2E\Kaufland\Block\Adminhtml\Traits;
use M2E\Kaufland\Block\Adminhtml\Magento\Renderer;

class CategoryMode extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    use Traits\BlockTrait;
    use Traits\RendererTrait;

    public const MODE_NOT_SELECTED = 0;
    public const MODE_SELECTED = 1;
    public const MODE_EBAY = 2;
    public const MODE_ATTRIBUTE = 3;
    public const MODE_TITLE = 10;

    public function __construct(
        Renderer\CssRenderer $css,
        Renderer\JsPhpRenderer $jsPhp,
        Renderer\JsRenderer $js,
        Renderer\JsTranslatorRenderer $jsTranslatorRenderer,
        Renderer\JsUrlRenderer $jsUrlRenderer,
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        array $data = []
    ) {
        parent::__construct($context, $resourceHelper, $data);

        $this->css = $css;
        $this->jsPhp = $jsPhp;
        $this->js = $js;
        $this->jsTranslator = $jsTranslatorRenderer;
        $this->jsUrl = $jsUrlRenderer;
    }

    //########################################

    public function getHtml()
    {
        $value = $this->getValue();

        $titleValue = !empty($value['title']) ? $value['title'] : '';
        $isAjax = \M2E\Core\Helper\Json::encode($this->getRequest()->isAjax());
        $modeTitle = self::MODE_TITLE;

        $this->js->add(
            <<<JS
    (function() {

        var initObservers = function () {

         $('{$this->_getHtmlId()}')
            .observe('change', function() {

                var div = $('{$this->_getHtmlId()}_title_container');
                div.hide();

                if (this.value == '{$modeTitle}') {
                    div.show();
                }
            })
            .simulate('change');
         };

         Event.observe(window, 'load', initObservers);
         if ({$isAjax}) {
             initObservers();
         }

    })();
JS
        );

        $categoryText = __('Category Path / Category ID');
        $html = <<<HTML
<div id="{$this->_getHtmlId()}_title_container" style="display: none;">
    <div style="width: auto; padding-top: 5px;">
        <span>$categoryText: </span><br>
        <input style="width: 300px;" type="text" value="{$titleValue}" name="{$this->getColumn()->getId()}[title]">
    </div>
</div>
HTML;

        return parent::getHtml() . $html;
    }

    //########################################

    public function getValue()
    {
        $value = $this->getData('value');

        if (
            is_array($value) &&
            (isset($value['mode']) && $value['mode'] !== null) ||
            (isset($value['title']) && !empty($value['mode']))
        ) {
            return $value;
        }

        return null;
    }

    //########################################

    protected function _renderOption($option, $value)
    {
        $value = isset($value['mode']) ? $value['mode'] : null;

        return parent::_renderOption($option, $value);
    }

    protected function _getHtmlName()
    {
        return "{$this->getColumn()->getId()}[mode]";
    }

    //########################################
}
