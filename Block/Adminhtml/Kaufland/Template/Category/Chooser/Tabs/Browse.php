<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\Chooser\Tabs;

class Browse extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock
{
    /** @var \M2E\Kaufland\Helper\View\Kaufland */
    public $viewHelper;
    /** @var \M2E\Kaufland\Helper\Module\Wizard */
    private $wizardHelper;

    public function __construct(
        \M2E\Kaufland\Helper\View\Kaufland $viewHelper,
        \M2E\Kaufland\Helper\Module\Wizard $wizardHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->viewHelper = $viewHelper;
        $this->wizardHelper = $wizardHelper;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('kauflandCategoryChooserCategoryBrowse');
        $this->setTemplate('kaufland/template/category/chooser/tabs/browse.phtml');
    }

    public function isWizardActive()
    {
        return $this->wizardHelper->isActive(\M2E\Kaufland\Helper\View\Kaufland::WIZARD_INSTALLATION_NICK);
    }
}
