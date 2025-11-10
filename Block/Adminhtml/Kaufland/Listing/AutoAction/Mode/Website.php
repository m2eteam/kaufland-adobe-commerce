<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\AutoAction\Mode;

class Website extends \M2E\Kaufland\Block\Adminhtml\Listing\AutoAction\Mode\AbstractWebsite
{
    public function _construct()
    {
        parent::_construct();

        $this->setId('kauflandListingAutoActionModeWebsite');
    }

    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();

        $autoGlobalAddingMode = $form->getElement('auto_website_adding_mode');
        $autoGlobalAddingMode->addElementValues([
            \M2E\Kaufland\Model\Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY => __(
                'Add to the Listing and Assign %extension_title Category',
                [
                    'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
                ]
            ),
        ]);

        return $this;
    }

    protected function _afterToHtml($html)
    {
        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\Listing::class)
        );

        return parent::_afterToHtml($html);
    }

    protected function _toHtml()
    {
        $helpBlockContent = (string)__(
            '<p>These rules apply when Products are added to or removed from a Magento Website ' .
            'associated with the Store View selected for this Listing.</p><br>' .
            '<p>If automatic adding is enabled, new Products added to the Website will be ' .
            'automatically added to this Listing.</p><br>' .
            '<p>Products already listed under the same Channel ' .
            'account and storefront won’t be added again to avoid duplicates.</p><br>' .
            '<p>If a Product in this Listing is removed from the Website, it will also be removed ' .
            'from the Listing and its sale on the Channel will stop.</p><br>' .
            '<p>For more details, see the ' .
            '<a href="%url" target="_blank">documentation</a>.</p>',
            ['url' => 'https://docs-m2.m2epro.com/docs/kaufland-auto-add-remove-rules/']
        );

        $helpBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Kaufland\Block\Adminhtml\HelpBlock::class)
            ->setData(['content' => $helpBlockContent]);

        return $helpBlock->toHtml() .
            parent::_toHtml() .
            '<div id="kaufland_category_chooser"></div>';
    }
}
