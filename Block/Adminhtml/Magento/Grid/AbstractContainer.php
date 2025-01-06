<?php

namespace M2E\Kaufland\Block\Adminhtml\Magento\Grid;

use Magento\Backend\Block\Widget\Grid\Container;
use M2E\Kaufland\Block\Adminhtml\Traits;

abstract class AbstractContainer extends Container
{
    use Traits\BlockTrait;
    use Traits\RendererTrait;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->css = $context->getCss();
        $this->jsPhp = $context->getJsPhp();
        $this->js = $context->getJs();
        $this->jsTranslator = $context->getJsTranslator();
        $this->jsUrl = $context->getJsUrl();

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_blockGroup = 'M2E_Kaufland';
    }

    protected function addGridBlock(string $gridClassName): self
    {
        if (!is_a($gridClassName, \M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractGrid::class, true)) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                sprintf(
                    'Grid %s must implement %s',
                    $gridClassName,
                    \M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractGrid::class,
                ),
            );
        }

        $this->addChild('grid', $gridClassName);

        /** @var \M2E\Kaufland\Block\Adminhtml\Magento\Grid\AbstractGrid $grid */
        $grid = $this->getChildBlock('grid');
        $grid->setSaveParametersInSession(true);

        return $this;
    }

    protected function addBlock(string $gridClassName): self
    {

        $this->addChild('form', $gridClassName);
        $grid = $this->getChildBlock('form');
        $grid->setSaveParametersInSession(true);

        return $this;
    }
}
