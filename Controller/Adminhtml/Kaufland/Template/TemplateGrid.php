<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractTemplate;

class TemplateGrid extends AbstractTemplate
{
    public function execute()
    {
        /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Grid $switcherBlock */
        $grid = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Grid::class);

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
