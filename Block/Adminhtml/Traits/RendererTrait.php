<?php

namespace M2E\Kaufland\Block\Adminhtml\Traits;

/**
 * Trait \M2E\Kaufland\Block\Adminhtml\Traits\RendererTrait
 */
trait RendererTrait
{
    /** @var \M2E\Kaufland\Block\Adminhtml\Magento\Renderer\JsPhpRenderer */
    public $jsPhp;

    /** @var \M2E\Kaufland\Block\Adminhtml\Magento\Renderer\JsTranslatorRenderer */
    public $jsTranslator;

    /** @var \M2E\Kaufland\Block\Adminhtml\Magento\Renderer\JsUrlRenderer */
    public $jsUrl;

    /** @var \M2E\Kaufland\Block\Adminhtml\Magento\Renderer\JsRenderer */
    public $js;

    /** @var \M2E\Kaufland\Block\Adminhtml\Magento\Renderer\CssRenderer */
    public $css;
}
