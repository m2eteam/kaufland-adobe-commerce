<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category;

use M2E\Kaufland\Helper\Module;

class View extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractContainer
{
    private \M2E\Kaufland\Model\Category\Dictionary $dictionary;

    public function __construct(
        \M2E\Kaufland\Model\Category\Dictionary $dictionary,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->dictionary = $dictionary;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->setId('kauflandCategoryView');
        $this->_template = Module::IDENTIFIER . '::kaufland/category/view.phtml';

        $this->removeButton('back');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
    }

    protected function _prepareLayout()
    {
        /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\View\Info $infoBlock */
        $infoBlock = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\View\Info::class,
            '',
            ['dictionary' => $this->dictionary]
        );

        /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\View\Edit $editBlock */
        $editBlock = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\View\Edit::class,
            '',
            ['dictionary' => $this->dictionary]
        );

        $this->setChild('info', $infoBlock);
        $this->setChild('edit', $editBlock);

        return parent::_prepareLayout();
    }

    public function getInfoHtml()
    {
        return $this->getChildHtml('info');
    }

    public function getEditHtml()
    {
        return $this->getChildHtml('edit');
    }
}
