<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Template\Edit;

class Form extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm
{
    /** @var \M2E\Kaufland\Helper\Data\GlobalData */
    private $globalDataHelper;
    private \M2E\Kaufland\Model\Account\Repository $accountRepository;

    public function __construct(
        \M2E\Kaufland\Model\Account\Repository $accountRepository,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->accountRepository = $accountRepository;
        $this->globalDataHelper = $globalDataHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function _construct()
    {
        parent::_construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('KauflandTemplateEditForm');
        // ---------------------------------------

        $this->css->addFile('kaufland/template.css');
    }

    protected function _prepareForm()
    {
        $form = $this->_formFactory->create([
            'data' => [
                'id' => 'edit_form',
                'action' => 'javascript:void(0)',
                'method' => 'post',
                'enctype' => 'multipart/form-data',
            ],
        ]);

        $templateNick = $this->getTemplateNick();

        $fieldset = $form->addFieldset(
            'general_fieldset',
            ['legend' => __('General'), 'collapsable' => false]
        );

        $templateData = $this->getTemplateData();

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'value' => $templateData['title'],
                'class' => 'input-text validate-title-uniqueness',
                'required' => true,
            ]
        );

        if ($templateNick === \M2E\Kaufland\Model\Template\Manager::TEMPLATE_SHIPPING) {
            if ($this->getRequest()->getParam('account_id', false) !== false) {
                $fieldset->addField(
                    'account_id_hidden',
                    'hidden',
                    [
                        'name' => 'shipping[account_id]',
                        'value' => $templateData['account_id'],
                    ]
                );
            }

            $fieldset->addField(
                'account_id',
                'select',
                [
                    'name' => 'shipping[account_id]',
                    'label' => __('Account'),
                    'title' => __('Account'),
                    'values' => $this->getAccountOptions(),
                    'value' => $templateData['account_id'],
                    'required' => true,
                    'disabled' => !empty($templateData['account_id']),
                ]
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return $this;
    }

    public function getTemplateData()
    {
        $accountId = $this->getRequest()->getParam('account_id', false);

        $nick = $this->getTemplateNick();
        $templateData = $this->globalDataHelper->getValue("kaufland_template_{$nick}");

        return array_merge([
            'title' => '',
            'account_id' => ($accountId !== false) ? $accountId : ''
        ], $templateData->getData());
    }

    public function getTemplateNick()
    {
        return $this->getParentBlock()->getTemplateNick();
    }

    public function getTemplateId()
    {
        $template = $this->getParentBlock()->getTemplateObject();

        return $template ? $template->getId() : null;
    }

    private function getAccountOptions(): array
    {
        return $this->formatAccountOptions($this->accountRepository->getAll());
    }

    private function formatAccountOptions(array $accounts): array
    {
        $optionsResult = [];

        foreach ($accounts as $account) {
            $optionsResult[] = [
                'value' => $account->getId(),
                'label' => $account->getTitle(),
            ];
        }

        return $optionsResult;
    }

    protected function _toHtml()
    {
        $nick = $this->getTemplateNick();
        $this->jsUrl->addUrls([
            'kaufland_template/getTemplateHtml' => $this->getUrl(
                '*/kaufland_template/getTemplateHtml',
                [
                    'account_id' => null,
                    'id' => $this->getTemplateId(),
                    'nick' => $nick,
                    'mode' => \M2E\Kaufland\Model\Template\Manager::MODE_TEMPLATE,
                    'data_force' => true,
                    'storefront_id' => $this->getRequest()->getParam('storefront_id'),
                ]
            ),
            'kaufland_template/isTitleUnique' => $this->getUrl(
                '*/kaufland_template/isTitleUnique',
                [
                    'id' => $this->getTemplateId(),
                    'nick' => $nick,
                ]
            ),
            'deleteAction' => $this->getUrl(
                '*/Kaufland_template/delete',
                [
                    'id' => $this->getTemplateId(),
                    'nick' => $nick,
                ]
            ),
        ]);

        $this->jsTranslator->addTranslations([
            'Policy Title is not unique.' => __('Policy Title is not unique.'),
            'Do not show any more' => __('Do not show this message anymore'),
            'Save Policy' => __('Save Policy'),
        ]);

        $this->jsPhp->addConstants(
            [
                '\M2E\Kaufland\Model\Kaufland\Template\Manager::TEMPLATE_SYNCHRONIZATION' => \M2E\Kaufland\Model\Template\Manager::TEMPLATE_SYNCHRONIZATION,
            ]
        );

        $this->js->addRequireJs(
            [
                'form' => 'Kaufland/Kaufland/Template/Edit/Form',
                'jquery' => 'jquery',
            ],
            <<<JS

        window.KauflandTemplateEditObj = new KauflandTemplateEdit();
        KauflandTemplateEditObj.templateNick = '{$this->getTemplateNick()}';
        KauflandTemplateEditObj.initObservers();
JS
        );

        return parent::_toHtml();
    }
}
