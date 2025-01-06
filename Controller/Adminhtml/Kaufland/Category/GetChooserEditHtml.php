<?php

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Category;

use M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Chooser\Edit;

class GetChooserEditHtml extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractCategory
{
    public function execute()
    {
        $selectedValue = $this->getRequest()->getParam('selected_value');
        $selectedPath = $this->getRequest()->getParam('selected_path');
        $viewMode = $this->getRequest()->getParam('view_mode', Edit::WITHOUT_TABS_VIEW_MODE);

        /** @var Edit $editBlock */
        $editBlock = $this->getLayout()->createBlock(Edit::class);
        $editBlock->setData(Edit::VIEW_MODE_KEY, $viewMode);

        if (
            !empty($selectedPath)
            && !empty($selectedValue)
        ) {
            $editBlock->setSelectedCategory($selectedValue, $selectedPath);
        }

        $this->setAjaxContent($editBlock->toHtml());

        return $this->getResult();
    }
}
