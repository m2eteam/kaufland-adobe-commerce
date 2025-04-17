<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Wizard\InstallationKaufland\Installation\Account;

use M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm;

class Content extends AbstractForm
{
    private \M2E\Kaufland\Block\Adminhtml\Kaufland\Account\CredentialsFormFactory $credentialsFormFactory;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Kaufland\Block\Adminhtml\Kaufland\Account\CredentialsFormFactory $credentialsFormFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->credentialsFormFactory = $credentialsFormFactory;
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('wizardInstallationWizardTutorial');
    }

    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('wizard.help.block')->setContent(
            (string)__(
                'On this step, you should link your %channel_title Account with your %extension_title.<br/><br/>',
                [
                    'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                    'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
                ]
            )
        );

        parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $form = $this->credentialsFormFactory->create(true, false, 'edit_form');

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _beforeToHtml()
    {
        $this->jsTranslator->add(
            'An error during of account creation.',
            __(
                'The %channel_title token obtaining is currently unavailable. Please try again later.',
                [
                    'channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle(),
                ]
            )
        );

        return parent::_beforeToHtml();
    }
}
