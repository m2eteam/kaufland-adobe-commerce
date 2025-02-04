<?php

declare(strict_types=1);

namespace M2E\Kaufland\Block\Adminhtml\System\Config\Sections;

class License extends \M2E\Kaufland\Block\Adminhtml\System\Config\Sections
{
    private \M2E\Core\Helper\Client $clientHelper;
    private \M2E\Core\Model\LicenseService $licenseService;

    public function __construct(
        \M2E\Core\Model\LicenseService $licenseService,
        \M2E\Core\Helper\Client $clientHelper,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);

        $this->clientHelper = $clientHelper;
        $this->licenseService = $licenseService;
    }

    protected function _prepareForm()
    {
        $license = $this->licenseService->get();

        $this->prepareButtons();

        $form = $this->_formFactory->create();

        $form->addField(
            'block_notice_configuration_license',
            self::HELP_BLOCK,
            [
                'no_collapse' => true,
                'no_hide' => true,
                'content' => __(
                    '<p>To use M2E Kaufland, you need to register on M2E Accounts and generate a
License Key.</p><br>

                    <p>Your email address used during the initial setup of M2E Kaufland automatically
                    registers you on
                    M2E Accounts. After logging in, you can manage your Subscription and Billing information.</p><br>

                    <p>License Key is a unique identifier of M2E Kaufland instance which is generated automatically
                    and strictly associated with the current IP and Domain of your Magento.</p><br>

                    <p>The same License Key cannot be used for different domains, sub-domains or IPs.
                    If your Magento Server changes its location, the new License Key must be obtained and provided
                    to M2E Kaufland License section. Click <strong>Save</strong> after the changes are made.</p><br>

                    <p><strong>Note:</strong> If you need some assistance to activate your M2E Kaufland instance,
                    please contact Support Team at <a href="mailto:support@m2epro.com">support@m2epro.com</a>.</p>'
                ),
            ]
        );

        $fieldSet = $form->addFieldset(
            'magento_block_configuration_license_data',
            [
                'legend' => __('General'),
                'collapsable' => false,
            ]
        );

        $fieldData = [
            'label' => __('License Key'),
            'text' => \M2E\Core\Helper\Data::escapeHtml($license->getKey()),
        ];

        $fieldSet->addField(
            'license_text_key_container',
            self::NOTE,
            $fieldData
        );

        if (!empty($license->getInfo()->getEmail())) {
            $fieldSet->addField(
                'associated_email',
                self::NOTE,
                [
                    'label' => __('Associated Email'),
                    'text' => $license->getInfo()->getEmail(),
                    'tooltip' => __(
                        'This email address is associated with your License.
                        You can also use it to access
                        <a href="%url" target="_blank" class="external-link">M2E Accounts</a>.',
                        ['url' => \M2E\Core\Helper\Module\Support::ACCOUNTS_URL],
                    ),
                ]
            );
        }

        if ($license->hasKey()) {
            $fieldSet->addField(
                'manage_license',
                self::LINK,
                [
                    'label' => '',
                    'value' => __('Manage License'),
                    'href' => \M2E\Core\Helper\Module\Support::ACCOUNTS_URL,
                    'class' => 'external-link',
                    'target' => '_blank',
                ]
            );
        }

        if (
            !empty($license->getInfo()->getDomainIdentifier()->getValidValue())
            || !empty($license->getInfo()->getIpIdentifier()->getValidValue())
        ) {
            $fieldSet = $form->addFieldset(
                'magento_block_configuration_license_valid',
                [
                    'legend' => __('Valid Location'),
                    'collapsable' => false,
                ]
            );

            if (!empty($license->getInfo()->getDomainIdentifier()->getValidValue())) {
                $text = '<span ' . ($license->getInfo()->getDomainIdentifier()->getValidValue() ? '' : 'style="color: red;"') . '>
                            ' . \M2E\Core\Helper\Data::escapeHtml($license->getInfo()->getDomainIdentifier()->getValidValue()) . '
                        </span>';
                if (
                    !$license->getInfo()->getDomainIdentifier()->isValid()
                    && !empty($license->getInfo()->getDomainIdentifier()->getRealValue())
                ) {
                    $text .= sprintf(
                        '<span>(%s: %s)</span>',
                        __('Your Domain'),
                        \M2E\Core\Helper\Data::escapeHtml($license->getInfo()->getDomainIdentifier()->getRealValue())
                    );
                }

                $fieldSet->addField(
                    'domain_field',
                    self::NOTE,
                    [
                        'label' => __('Domain'),
                        'text' => $text,
                    ]
                );
            }

            if (!empty($license->getInfo()->getIpIdentifier()->getValidValue())) {
                $text = '<span ' . ($license->getInfo()->getIpIdentifier()->getValidValue() ? '' : 'style="color: red;"') . '>
                            ' . \M2E\Core\Helper\Data::escapeHtml($license->getInfo()->getIpIdentifier()->getValidValue()) . '
                        </span>';
                if (
                    !$license->getInfo()->getIpIdentifier()->isValid()
                    && !empty($license->getInfo()->getIpIdentifier()->getRealValue())
                ) {
                    $text .= '<span> (' . __('Your IP') . ': '
                        . \M2E\Core\Helper\Data::escapeHtml($license->getInfo()->getDomainIdentifier()->getRealValue()) . ')</span>';
                }

                $fieldSet->addField(
                    'ip_field',
                    self::NOTE,
                    [
                        'label' => __('IP(s)'),
                        'text' => $text,
                        'after_element_html' => $this->getChildHtml('refresh_status'),
                    ]
                );
            }
        }

        $fieldSet = $form->addFieldset(
            'magento_block_configuration_license',
            [
                'legend' => !$license->hasKey()
                    ? (string)__('General')
                    : (string)__('Additional'),
                'collapsable' => false,
            ]
        );

        $fieldSet->addField(
            'license_buttons',
            'note',
            [
                'text' => '<span style="padding-right: 10px;">' . $this->getChildHtml('new_license') . '</span>'
                    . '<span>' . $this->getChildHtml('change_license') . '</span>',
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    private function prepareButtons(): void
    {
        $license = $this->licenseService->get();

        $data = [
            'label' => __('Refresh'),
            'onclick' => 'LicenseObj.refreshStatus();',
            'class' => 'refresh_status primary',
            'style' => 'margin-left: 2rem',
        ];
        $buttonBlock = $this->getLayout()
                            ->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Button::class)
                            ->setData($data);
        $this->setChild('refresh_status', $buttonBlock);
        // ---------------------------------------

        // ---------------------------------------
        $data = [
            'label' => !$license->hasKey()
                ? (string)__('Use Existing License')
                : (string)__('Change License'),
            'onclick' => 'LicenseObj.changeLicenseKeyPopup();',
            'class' => 'change_license primary',
        ];
        $buttonBlock = $this->getLayout()
                            ->createBlock(\M2E\Kaufland\Block\Adminhtml\Magento\Button::class)
                            ->setData($data);
        $this->setChild('change_license', $buttonBlock);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        try {
            $this->clientHelper->updateLocationData(true);
            // @codingStandardsIgnoreLine
        } catch (\Throwable $exception) {
        }

        $jsSettings = json_encode(
            [
                'change' => $this->getUrl('m2e_kaufland/settings_license/change'),
                'refresh' => $this->getUrl('m2e_kaufland/settings_license/refreshStatus'),
                'licenseSection' => $this->getUrl('m2e_kaufland/settings_license/section'),
            ]
        );

        $this->js->addRequireJs(
            [
                'l' => 'Kaufland/Settings/License',
            ],
            <<<JS
window.LicenseObj = new License($jsSettings);
JS
        );
    }
}
