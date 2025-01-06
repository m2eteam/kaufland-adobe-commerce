<?php

namespace M2E\Kaufland\Block\Adminhtml\Magento;

use Magento\Backend\Block\Widget;
use M2E\Kaufland\Block\Adminhtml\Traits;

abstract class AbstractBlock extends Widget
{
    use Traits\BlockTrait;
    use Traits\RendererTrait;

    /** @var \M2E\Kaufland\Model\Factory */
    protected $modelFactory;

    public function __construct(\M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context, array $data = [])
    {
        $this->modelFactory = $context->getModelFactory();

        $this->css = $context->getCss();
        $this->jsPhp = $context->getJsPhp();
        $this->js = $context->getJs();
        $this->jsTranslator = $context->getJsTranslator();
        $this->jsUrl = $context->getJsUrl();

        parent::__construct($context, $data);
    }
}
