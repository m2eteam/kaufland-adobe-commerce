<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Account;

class CreatePopup extends \M2E\Kaufland\Block\Adminhtml\Magento\AbstractBlock
{
    private \Magento\Framework\View\Page\Config $config;
    private \M2E\Kaufland\Block\Adminhtml\Kaufland\Account\CredentialsFormFactory $credentialsFormFactory;

    public function __construct(
        \Magento\Framework\View\Page\Config $config,
        \M2E\Kaufland\Block\Adminhtml\Kaufland\Account\CredentialsFormFactory $credentialsFormFactory,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        array $data = []
    ) {
        $this->config = $config;
        $this->credentialsFormFactory = $credentialsFormFactory;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->config->addPageAsset('M2E_Kaufland::css/account/credentials.css');
    }

    protected function _prepareLayout()
    {
        $this->addChild('form', \M2E\Kaufland\Block\Adminhtml\Kaufland\Account\Edit\Form::class);

        return parent::_prepareLayout();
    }

    protected function _toHtml(): string
    {
        return parent::_toHtml()
            . '<div class="custom-popup" style="display: none;">'
            . $this->credentialsFormFactory->create(
                true,
                true,
                'account_credentials'
            )->toHtml()
            . '</div>';
    }
}
