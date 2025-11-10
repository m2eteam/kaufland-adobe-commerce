<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\AutoAction\Mode;

use M2E\Kaufland\Block\Adminhtml\Magento\Form\Element\Select;

class GlobalMode extends \M2E\Kaufland\Block\Adminhtml\Listing\AutoAction\Mode\AbstractGlobalMode
{
    public function _construct()
    {
        parent::_construct();
        $this->setId('kauflandListingAutoActionModeGlobal');
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create();

        $form->addField(
            'auto_mode',
            'hidden',
            [
                'name' => 'auto_mode',
                'value' => \M2E\Kaufland\Model\Listing::AUTO_MODE_GLOBAL,
            ]
        );

        $fieldSet = $form->addFieldset('auto_global_fieldset_container', []);

        $this->addAutoGlobalAddingModeField($fieldSet, $this->formData);
        $this->addAutoGlobalAddingAddNotVisibleField($fieldSet, $this->formData);
        $this->addAutoGlobalDeletingModeField($fieldSet, $this->formData);

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
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
        $helpBlockContent = __(
            '<p>These rules apply globally across your entire Magento Catalog.</p><br>' .
            '<p>If automatic adding is enabled, new Products added to the Magento Catalog will be automatically ' .
            'added to this Listing. Products already listed under the same Channel account and ' .
            'marketplace won’t be added again to avoid duplicates.</p><br>' .
            '<p>If a Product in this Listing is removed from the Website, it will also be removed from the ' .
            'Listing and its sale on the Channel will stop.</p><br>' .
            '<p>For more details, see the <a href="%url" target="_blank">documentation</a>.</p>',
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

    private function addAutoGlobalAddingModeField(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldSet,
        array $formData
    ) {
        $values = [
            [
                'value' => \M2E\Kaufland\Model\Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY,
                'label' => __('Add to the Listing and Assign %extension_title Category', [
                    'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle()
                ]),
            ],
        ];

        if ($this->formData['auto_global_adding_mode'] == \M2E\Kaufland\Model\Listing::ADDING_MODE_NONE) {
            $values[] = [
                'value' => \M2E\Kaufland\Model\Listing::ADDING_MODE_NONE,
                'label' => __('No Action'),
            ];
        }

        $fieldSet->addField(
            'auto_global_adding_mode',
            Select::class,
            [
                'name' => 'auto_global_adding_mode',
                'label' => __('New Product Added to Magento'),
                'title' => __('New Product Added to Magento'),
                'values' => $values,
                'value' => \M2E\Kaufland\Model\Listing::ADDING_MODE_NONE,
                'tooltip' => __('Action which will be applied automatically.'),
                'style' => 'width: 350px;',
            ]
        );
    }

    private function addAutoGlobalAddingAddNotVisibleField(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldSet,
        array $formData
    ) {
        $fieldSet->addField(
            'auto_global_adding_add_not_visible',
            Select::class,
            [
                'name' => 'auto_global_adding_add_not_visible',
                'label' => __('Add not Visible Individually Products'),
                'title' => __('Add not Visible Individually Products'),
                'values' => [
                    [
                        'value' => \M2E\Kaufland\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_NO,
                        'label' => __('No')],
                    [
                        'value' => \M2E\Kaufland\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
                        'label' => __('Yes'),
                    ],
                ],
                'value' => $this->formData['auto_global_adding_add_not_visible'],
                'field_extra_attributes' => 'id="auto_global_adding_add_not_visible_field"',
                'tooltip' => __(
                    'Set to <strong>Yes</strong> if you want the Magento Products with
                    Visibility \'Not visible Individually\' to be added to the Listing
                    Automatically.<br/>
                    If set to <strong>No</strong>, only Variation (i.e.
                    Parent) Magento Products will be added to the Listing Automatically,
                    excluding Child Products.'
                ),
                'style' => 'width: 350px;',
            ]
        );
    }

    private function addAutoGlobalDeletingModeField(
        \Magento\Framework\Data\Form\Element\Fieldset $fieldSet,
        array $formData
    ) {
        $fieldSet->addField(
            'auto_global_deleting_mode',
            Select::class,
            [
                'name' => 'auto_global_deleting_mode',
                'disabled' => true,
                'label' => __('Product Deleted from Magento'),
                'title' => __('Product Deleted from Magento'),
                'values' => [
                    [
                        'value' => \M2E\Kaufland\Model\Listing::DELETING_MODE_STOP_REMOVE,
                        'label' => __('Stop on Channel and Delete from Listing'),
                    ],
                ],
                'style' => 'width: 350px;',
            ]
        );
    }
}
