<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template\Category;

class Grid extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Template\AbstractCategory
{
    public function execute()
    {
        /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Grid $grid */
        $grid = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Grid::class
        );

        $this->setAjaxContent($grid->toHtml());

        return $this->getResult();
    }
}
