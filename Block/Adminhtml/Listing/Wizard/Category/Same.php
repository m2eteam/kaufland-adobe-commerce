<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Listing\Wizard\Category;

class Same extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractContainer
{
    use \M2E\Kaufland\Block\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Kaufland\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage;
    private \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\Kaufland\Model\Listing\Wizard\Ui\RuntimeStorage $uiWizardRuntimeStorage,
        array $data = []
    ) {
        $this->uiWizardRuntimeStorage = $uiWizardRuntimeStorage;
        $this->wizardManagerFactory = $wizardManagerFactory;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $wizardId = $this->getWizardIdFromRequest();
        $wizardManager = $this->wizardManagerFactory->createById($wizardId);

        $this->setId('listingCategoryChooser');

        $this->prepareButtons(
            [
                'label' => __('Continue'),
                'class' => 'action-primary forward',
                'onclick' => sprintf(
                    "KauflandListingCategoryObj.modeSameSubmitData('%s')",
                    $this->getUrl('*/listing_wizard_category/assignModeSame', ['id' => $this->getWizardIdFromRequest()]),
                ),
            ],
            $wizardManager
        );

        $this->_headerText = __('Categories');
    }

    protected function _beforeToHtml()
    {
        $this->js->add(
            <<<JS
 require([
    'Kaufland/Listing/Wizard/Category'
], function() {
    window.KauflandListingCategoryObj = new KauflandListingCategory(null);
});
JS,
        );

        return parent::_beforeToHtml();
    }

    protected function _toHtml()
    {
        /** @var \M2E\Kaufland\Block\Adminhtml\Category\CategoryChooser $chooserBlock */
        $chooserBlock = $this
            ->getLayout()
            ->createBlock(
                \M2E\Kaufland\Block\Adminhtml\Category\CategoryChooser::class,
                '',
                ['selectedCategory' => null],
            );

        return parent::_toHtml()
            . $chooserBlock->toHtml();
    }
}
