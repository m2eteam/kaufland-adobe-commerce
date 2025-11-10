<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Listing\AutoAction\Mode\Category;

abstract class AbstractForm extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm
{
    public array $formData = [];

    private \M2E\Kaufland\Model\Listing $listing;
    private \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group $autoCategoryGroupResource;
    private \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $autoCategoryGroupRepository;
    private \M2E\Kaufland\Model\Listing\Auto\Category\Repository $autoCategoryRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing $listing,
        \M2E\Kaufland\Model\ResourceModel\Listing\Auto\Category\Group $autoCategoryGroupResource,
        \M2E\Kaufland\Model\Listing\Auto\Category\Group\Repository $autoCategoryGroupRepository,
        \M2E\Kaufland\Model\Listing\Auto\Category\Repository $autoCategoryRepository,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->listing = $listing;
        $this->autoCategoryGroupResource = $autoCategoryGroupResource;
        $this->autoCategoryGroupRepository = $autoCategoryGroupRepository;
        $this->autoCategoryRepository = $autoCategoryRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('listingAutoActionModeCategoryForm');
        $this->formData = $this->getFormData();
    }

    //########################################

    public function getFormData(): ?array
    {
        $groupId = $this->getRequest()->getParam('group_id');
        $default = $this->getDefault();
        if (empty($groupId)) {
            return $default;
        }

        $group = $this->autoCategoryGroupRepository->get((int)$groupId);

        return array_merge($default, $group->getData());
    }

    //########################################

    public function getDefault(): array
    {
        return [
            'id' => null,
            'title' => null,
            'category_id' => null,
            'adding_mode' => \M2E\Kaufland\Model\Listing::ADDING_MODE_NONE,
            'adding_add_not_visible' => \M2E\Kaufland\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'deleting_mode' => \M2E\Kaufland\Model\Listing::DELETING_MODE_NONE,
        ];
    }

    public function hasFormData(): bool
    {
        return $this->getListing()->getData('auto_mode') == \M2E\Kaufland\Model\Listing::AUTO_MODE_CATEGORY;
    }

    public function getListing(): \M2E\Kaufland\Model\Listing
    {
        return $this->listing;
    }

    //########################################

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
            ],
        ]);

        $form->addField(
            'group_id',
            'hidden',
            [
                'name' => 'id',
                'value' => $this->formData['id'],
            ]
        );

        $form->addField(
            'auto_mode',
            'hidden',
            [
                'name' => 'auto_mode',
                'value' => \M2E\Kaufland\Model\Listing::AUTO_MODE_CATEGORY,
            ]
        );

        $fieldSet = $form->addFieldset('category_form_container_field', []);

        $fieldSet->addField(
            'group_title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'class' => 'M2ePro-required-when-visible M2ePro-validate-category-group-title',
                'value' => $this->formData['title'],
                'required' => true,
            ]
        );

        $fieldSet->addField(
            'adding_mode',
            \M2E\Kaufland\Block\Adminhtml\Magento\Form\Element\Select::class,
            [
                'name' => 'adding_mode',
                'label' => __('Product Assigned to Categories'),
                'title' => __('Product Assigned to Categories'),
                'values' => [
                    [
                        'label' => __('No Action'),
                        'value' => \M2E\Kaufland\Model\Listing::ADDING_MODE_NONE],
                ],
                'value' => $this->formData['adding_mode'],
                'tooltip' => __('Action which will be applied automatically.'),
                'style' => 'width: 350px',
            ]
        );

        $fieldSet->addField(
            'adding_add_not_visible',
            \M2E\Kaufland\Block\Adminhtml\Magento\Form\Element\Select::class,
            [
                'name' => 'adding_add_not_visible',
                'label' => __('Add not Visible Individually Products'),
                'title' => __('Add not Visible Individually Products'),
                'values' => [
                    [
                        'label' => __('No'),
                        'value' => \M2E\Kaufland\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_NO,
                    ],
                    [
                        'label' => __('Yes'),
                        'value' => \M2E\Kaufland\Model\Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
                    ],
                ],
                'value' => $this->formData['adding_add_not_visible'],
                'field_extra_attributes' => 'id="adding_add_not_visible_field"',
                'tooltip' => __(
                    'Set to <strong>Yes</strong> if you want the Magento Products with
                    Visibility \'Not visible Individually\' to be added to the Listing
                    Automatically.<br/>
                    If set to <strong>No</strong>, only Variation (i.e.
                    Parent) Magento Products will be added to the Listing Automatically,
                    excluding Child Products.'
                ),
            ]
        );

        $fieldSet->addField(
            'deleting_mode',
            \M2E\Kaufland\Block\Adminhtml\Magento\Form\Element\Select::class,
            [
                'name' => 'deleting_mode',
                'label' => __('Product Deleted from Categories'),
                'title' => __('Product Deleted from Categories'),
                'values' => [
                    ['label' => __('No Action'), 'value' => \M2E\Kaufland\Model\Listing::DELETING_MODE_NONE],
                    [
                        'label' => __('Stop on Channel'),
                        'value' => \M2E\Kaufland\Model\Listing::DELETING_MODE_STOP,
                    ],
                    [
                        'label' => __('Stop on Channel and Delete from Listing'),
                        'value' => \M2E\Kaufland\Model\Listing::DELETING_MODE_STOP_REMOVE,
                    ],
                ],
                'value' => $this->formData['deleting_mode'],
                'style' => 'width: 350px',
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _afterToHtml($html)
    {
        $this->jsPhp->addConstants(\M2E\Kaufland\Helper\Data::getClassConstants(\M2E\Kaufland\Model\Listing::class));

        $magentoCategoryIdsFromOtherGroups = \M2E\Core\Helper\Json::encode(
            $this->getCategoriesFromOtherGroups()
        );
        $this->js->add(
            <<<JS
            ListingAutoActionObj.magentoCategoryIdsFromOtherGroups = {$magentoCategoryIdsFromOtherGroups};
JS
        );

        return parent::_afterToHtml($html);
    }

    //########################################

    public function getCategoriesFromOtherGroups(): array
    {
        $categories = $this->autoCategoryGroupResource
            ->getCategoriesFromOtherGroups(
                (int)$this->getRequest()->getParam('id'),
                $this->getRequest()->getParam('group_id')
            );

        foreach ($categories as &$group) {
            $group['title'] = $this->_escaper->escapeHtml($group['title']);
        }

        return $categories;
    }

    protected function _toHtml()
    {
        $selectedCategories = [];
        if ($this->getRequest()->getParam('group_id')) {
            $selectedCategories = $this->autoCategoryRepository
                ->getSelectedCategoriesIds((int)$this->getRequest()->getParam('group_id'));
        }

        /** @var \M2E\Kaufland\Block\Adminhtml\Listing\Category\Tree $block */
        $block = $this->getLayout()->createBlock(\M2E\Kaufland\Block\Adminhtml\Listing\Category\Tree::class);
        $block->setCallback('ListingAutoActionObj.magentoCategorySelectCallback');
        $block->setSelectedCategories($selectedCategories);

        $confirmMessage = <<<HTML
        <div id="dialog_confirm_content" style="display: none;">
            <div>
                {$this->__(
            'This Category is already used in the Rule %s.
                    If you press "Confirm" Button, Category will be removed from that Rule.'
        )}
            </div>
        </div>
HTML;

        $this->css->add(
            'label.mage-error[for="validate_category_selection"] { width: 230px !important; left: 13px !important; }'
        );

        return '<div id="category_child_data_container">
                    <div id="category_tree_container">' . $block->toHtml() . '</div>
                    <div id="category_form_container">' . parent::_toHtml() . '</div>
                </div><div style="clear: both;"></div>
                <div><form id="validate_category_selection_form"><input type="hidden"
                            name="validate_category_selection"
                            id="validate_category_selection"
                            style="width: 255px;"
                            class="M2ePro-validate-category-selection" /></form>
                </div>' . $confirmMessage;
    }

    //########################################
}
