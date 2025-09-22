<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Category\View;

class Edit extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractContainer
{
    private \M2E\Kaufland\Model\Category\Dictionary $dictionary;

    public function __construct(
        \M2E\Kaufland\Model\Category\Dictionary $dictionary,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        array $data = []
    ) {
        $this->dictionary = $dictionary;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();

        $this->removeButton('save');

        $this->setId('kauflandConfigurationCategoryViewTabsItemSpecificsEdit');
        $this->_controller = 'adminhtml_kaufland_template_category_view';

        $this->_headerText = '';

        $this->updateButton(
            'reset',
            'onclick',
            'KauflandTemplateCategorySpecificsObj.resetSpecifics()'
        );

        $editUrl = $this->_urlBuilder->getUrl(
            '*/kaufland_category/saveCategoryAttributes',
            ['back' => 'edit']
        );

        $closeUrl = $this->_urlBuilder->getUrl(
            '*/kaufland_category/SaveCategoryAttributes',
            ['back' => 'categories_grid']
        );

        $saveButtons = [
            'id' => 'save_and_continue',
            'label' => __('Save And Continue Edit'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => \M2E\Kaufland\Block\Adminhtml\Magento\Button\SplitButton::class,
            'onclick' => "KauflandTemplateCategorySpecificsObj.saveAndEditClick('$editUrl')",
            'options' => [
                'save' => [
                    'label' => __('Save And Back'),
                    'onclick' => "KauflandTemplateCategorySpecificsObj.saveAndCloseClick('$closeUrl')",
                ],
            ],
        ];

        $this->addButton('save_buttons', $saveButtons);

        if (!$this->dictionary->hasRecordsOfAttributes()) {
            $this->removeButton('reset');
            $this->removeButton('save_and_continue');
        }
    }

    public function getBackUrl()
    {
        return $this->getUrl('*/kaufland_template_category/index');
    }

    public function getDictionary(): \M2E\Kaufland\Model\Category\Dictionary
    {
        return $this->dictionary;
    }
}
