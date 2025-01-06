<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template\Category;

class Edit extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\Template\AbstractCategory
{
    public function execute()
    {
        $selectedValue = $this->getRequest()->getParam('selected_value');
        $selectedPath = $this->getRequest()->getParam('selected_path');

        /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Chooser\Edit $editBlock */
        $editBlock = $this->getLayout()->createBlock(
            \M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Chooser\Edit::class,
        );

        if (!empty($selectedValue)) {
            $editBlock->setSelectedCategory($selectedValue, $selectedPath);
        }

        $html = $editBlock->toHtml();
        $this->setAjaxContent($html);

        return $this->getResult();
    }
}
