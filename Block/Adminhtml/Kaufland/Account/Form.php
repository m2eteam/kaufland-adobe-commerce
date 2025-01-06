<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Account;

class Form extends \M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm
{
    /** @var \M2E\Kaufland\Block\Adminhtml\Kaufland\Account\CredentialsFormFactory */
    private CredentialsFormFactory $credentialsFormFactory;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Kaufland\Block\Adminhtml\Kaufland\Account\CredentialsFormFactory $credentialsFormFactory,
        array $data = []
    ) {
        $this->credentialsFormFactory = $credentialsFormFactory;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _toHtml(): string
    {
        return parent::_toHtml()
            . '<div class="custom-popup" style="">'
            . $this->credentialsFormFactory->create(
                true,
                true,
                'account_credentials',
                $this->getUrl('*/kaufland_account/create'),
            )->toHtml()
            . '</div>';
    }
}
