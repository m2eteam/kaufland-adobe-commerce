<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Listing\AutoAction;

use M2E\Kaufland\Model\Template\Manager as TemplateManager;

class SetDescriptionPolicy extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm
{
    private \M2E\Kaufland\Model\Listing $listing;
    private \M2E\Kaufland\Model\Template\Description\Repository $descriptionTemplateRepository;

    private array $descriptionPolicies;

    public function __construct(
        \M2E\Kaufland\Model\Listing $listing,
        \M2E\Kaufland\Model\Template\Description\Repository $descriptionTemplateRepository,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->listing = $listing;
        $this->descriptionTemplateRepository = $descriptionTemplateRepository;
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'set_description_template_form',
            ],
        ]);

        $fieldset = $form->addFieldset(
            'main_fieldset',
            []
        );

        $fieldset->addField(
            'listing_id',
            'hidden',
            [
                'name' => 'listing_id',
                'value' => $this->listing->getId(),
            ]
        );

        $fieldset->addField(
            'template_description_container',
            self::CUSTOM_CONTAINER,
            [
                'label' => __('Description Policy'),
                'style' => 'line-height: 34px;display: initial;',
                'field_extra_attributes' => 'style="margin-bottom: 5px"',
                'required' => true,
                'text' => $this->getDropdownHtml($form),
                'after_element_html' => $this->getActionButtonsHtml(),
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _toHtml(): string
    {
        $helpBlockContent = (string)__(
            '<p>To set up Auto Add/Remove Rules for this listing, you first need to create ' .
            'and assign a Description Policy.<p><br><p>Once a policy is assigned, you will be able to proceed ' .
            'to configure rules.</p>',
        );

        $helpBlock = $this
            ->getLayout()
            ->createBlock(\M2E\Kaufland\Block\Adminhtml\HelpBlock::class)
            ->setData(['content' => $helpBlockContent]);

        return $helpBlock->toHtml() .
            parent::_toHtml();
    }

    private function getDropdownHtml(\Magento\Framework\Data\Form $form): string
    {
        $descriptionTemplates = $this->getDescriptionPolicies();

        $templateDescription = $this->elementFactory->create(
            'select',
            [
                'data' => [
                    'html_id' => 'template_description_id',
                    'name' => 'template_description_id',
                    'required' => true,
                    'class' => 'Kaufland-required-entry',
                    'style' => 'width: 50%;' . (count($descriptionTemplates) === 0 ? 'display: none' : ''),
                    'no_span' => true,
                    'values' => array_map(function ($descriptionPolicy) {
                        return [
                            'label' => $descriptionPolicy->getTitle(),
                            'value' => $descriptionPolicy->getId(),
                        ];
                    }, $descriptionTemplates),
                    'value' => $this->listing->getTemplateDescriptionId(),
                ],
            ]
        );
        $templateDescription->setForm($form);

        $style = count($descriptionTemplates) === 0 ? '' : 'display: none';
        $noPoliciesAvailableText = __('No Policies available.');

        return <<<HTML
    <span style="$style">
        $noPoliciesAvailableText
    </span>
    {$templateDescription->toHtml()}
HTML
            ;
    }

    private function getActionButtonsHtml(): string
    {
        $viewText = __('View');
        $editText = __('Edit');
        $orText = __('or');
        $addNewText = __('Add New');

        $editButtonStyle = 'color:#41362f;';
        if (count($this->getDescriptionPolicies()) === 0) {
            $editButtonStyle .= ' display:none;';
        }

        return <<<HTML
<span style="margin-left: 7px; line-height: 30px;">
    <span style="$editButtonStyle">
        <a class="edit-button" href="javascript: void(0);" data-url="{$this->getEditUrl()}">
            $viewText&nbsp;/&nbsp;$editText
        </a>
        <span>$orText</span>
    </span>
    <a class="add-button" href="javascript: void(0);" data-url="{$this->getAddNewUrl()}">
        $addNewText
    </a>
</span>
HTML
            ;
    }

    private function getAddNewUrl(): string
    {
        return $this->getUrl(
            '*/kaufland_template/newAction',
            [
                'storefront_id' => $this->listing->getStorefrontId(),
                'nick' => TemplateManager::TEMPLATE_DESCRIPTION,
                'account_id' => $this->listing->getAccountId(),
                'close_on_save' => 1,
            ]
        );
    }

    private function getEditUrl(): string
    {
        return $this->getUrl(
            '*/kaufland_template/edit',
            [
                'nick' => TemplateManager::TEMPLATE_DESCRIPTION,
                'close_on_save' => 1,
            ]
        );
    }

    /**
     * @return \M2E\Kaufland\Model\Template\Description[]
     */
    private function getDescriptionPolicies(): array
    {
        /** @psalm-suppress RedundantPropertyInitializationCheck */
        if (!isset($this->descriptionPolicies)) {
            $this->descriptionPolicies = $this->descriptionTemplateRepository->getAll();
        }

        return $this->descriptionPolicies;
    }
}
