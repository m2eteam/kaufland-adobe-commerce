<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Account;

class CredentialsFormFactory
{
    private \Magento\Framework\Data\FormFactory $formFactory;

    public function __construct(
        \Magento\Framework\Data\FormFactory $formFactory
    ) {
        $this->formFactory = $formFactory;
    }

    public function create(bool $withTitle, bool $withButton, string $id, string $submitUrl = '')
    {
        $form = $this->formFactory->create(
            [
                'data' => [
                    'id' => $id,
                    'action' => $submitUrl,
                    'method' => 'post',
                ],
            ]
        );

        $form->setUseContainer(true);

        $fieldset = $form->addFieldset(
            'general_credentials',
            [
                'legend' => __('Add API Keys'),
                'collapsable' => false,
                'class' => 'fieldset admin__fieldset admin__field-control'
            ],
        );

        if ($withTitle) {
            $fieldset->addField(
                'title',
                'text',
                [
                    'name' => 'title',
                    'class' => 'kaufland-account-title',
                    'label' => __('Title'),
                    'value' => '',
                    'required' => true,
                ],
            );
        }

        $fieldset->addField(
            'client_key',
            'text',
            [
                'name' => 'client_key',
                'class' => 'kaufland-account-title',
                'label' => __('Client Key'),
                'value' => '',
                'required' => true,
            ],
        );

        $fieldset->addField(
            'secret_key',
            'text',
            [
                'name' => 'secret_key',
                'class' => 'kaufland-account-title',
                'label' => __('Secret Key'),
                'value' => '',
                'required' => true,
            ],
        );

        if ($withButton) {
            $fieldset->addField(
                'submit_button',
                'submit',
                [
                    'value' => __('Save'),
                    'style' => '',
                    'class' => 'submit action-default Kaufland-fieldset field-submit_button',
                ]
            );
        }

        return $form;
    }
}
