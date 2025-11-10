<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\AutoAction\Mode\Category;

class Form extends \M2E\Kaufland\Block\Adminhtml\Listing\AutoAction\Mode\Category\AbstractForm
{
    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('listingAutoActionModeCategoryForm');
        // ---------------------------------------
    }

    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();

        $addingMode = $form->getElement('adding_mode');
        $addingMode->addElementValues([
            \M2E\Kaufland\Model\Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY => __(
                'Add to the Listing and Assign %extension_title Category',
                [
                    'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
                ]
            ),
        ]);

        return $this;
    }

    //########################################

    protected function _afterToHtml($html)
    {
        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\Listing::class)
        );

        $this->js->add(
            <<<JS
            $('adding_mode')
                .observe('change', ListingAutoActionObj.categoryAddingMode)
                .simulate('change');
JS
        );

        return parent::_afterToHtml($html);
    }

    //########################################

    protected function _toHtml()
    {
        return parent::_toHtml() .
            '<div id="kaufland_category_chooser"></div>';
    }

    //########################################
}
