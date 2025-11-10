<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Listing\AutoAction\Mode;

abstract class AbstractCategory extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm
{
    public function _construct()
    {
        parent::_construct();
        $this->setId('listingAutoActionModeCategory');
    }

    //########################################

    protected function _prepareForm()
    {
        $this->prepareGroupsGrid();

        $form = $this->_formFactory->create();

        $containerHtml = $this->getChildHtml('group_grid');

        $form->addField(
            'custom_listing_auto_action_mode_category',
            \M2E\Kaufland\Block\Adminhtml\Magento\Form\Element\CustomContainer::class,
            [
                'text' => $containerHtml,
                'field_extra_attributes' => 'style="width: 100%"',
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    abstract protected function prepareGroupsGrid(): Category\Group\AbstractGrid;

    // ----------------------------------------

    protected function _afterToHtml($html)
    {
        $this->jsPhp->addConstants(
            \M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\Listing::class)
        );

        // ---------------------------------------
        $groupGrid = $this->getChildBlock('group_grid');
        // ---------------------------------------

        $skipConfirmation = \M2E\Core\Helper\Json::encode($groupGrid->getCollection()->getSize() == 0);
        $this->js->add(
            <<<JS
        var skipConfirmation = {$skipConfirmation};

        if (!skipConfirmation) {
            $('category_cancel_button').hide();
            $('category_close_button').hide();
            $('category_reset_button').show();
        }
JS
        );

        return parent::_afterToHtml($html);
    }

    protected function _toHtml()
    {
        $title = sprintf(
            '<div id="additional_autoaction_title_text" style="display: none">%s</div>',
            $this->getBlockTitle()
        );

        $content = sprintf(
            '<div id="block-content-wrapper"><div id="data_container">%s</div></div>',
            parent::_toHtml()
        );

        return $title . $content;
    }

    // ---------------------------------------

    protected function getBlockTitle(): string
    {
        return (string)__('Categories');
    }
}
