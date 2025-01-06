<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Chooser\Tabs;

class Recent extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('kauflandCategoryChooserCategoryRecent');
        $this->setTemplate('kaufland/template/category/chooser/tabs/recent.phtml');
    }
}
