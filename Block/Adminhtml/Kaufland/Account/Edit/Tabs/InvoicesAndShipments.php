<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Account\Edit\Tabs;

use M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm;

class InvoicesAndShipments extends AbstractForm
{
    private \M2E\Kaufland\Model\Account $account;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Kaufland\Model\Account $account,
        array $data = []
    ) {
        $this->account = $account;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $invoicesAndShipmentSettings = $this->account->getInvoiceAndShipmentSettings();

        $form = $this->_formFactory->create();

        $form->addField(
            'invoices_and_shipments',
            self::HELP_BLOCK,
            [
                'content' => __(
                    '<p>Under this tab, you can set %extension_title to automatically create ' .
                    'invoices and shipments in your Magento. To do that, keep Magento ' .
                    '<i>Invoice/Shipment Creation</i> options enabled.</p>',
                    [
                        'extension_title' => \M2E\Kaufland\Helper\Module::getExtensionTitle(),
                    ]
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'invoices',
            [
                'legend' => __('Invoices'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'create_magento_invoice',
            'select',
            [
                'label' => __('Magento Invoice Creation'),
                'title' => __('Magento Invoice Creation'),
                'name' => 'create_magento_invoice',
                'values' => [
                    0 => __('Disabled'),
                    1 => __('Enabled'),
                ],
                'value' => (int)$invoicesAndShipmentSettings->isCreateMagentoInvoice(),
                'tooltip' => __(
                    'Enable to automatically create Magento Invoices when payment is completed.'
                ),
            ]
        );

        $fieldset->addField(
            'upload_magento_invoice',
            'select',
            [
                'label' => __('Upload Invoice to %channel_title', ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]),
                'title' => __('Upload Invoice to %channel_title', ['channel_title' => \M2E\Kaufland\Helper\Module::getChannelTitle()]),
                'name' => 'upload_magento_invoice',
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => (int)$invoicesAndShipmentSettings->isUploadMagentoInvoice(),
            ]
        );

        $fieldset = $form->addFieldset(
            'shipments',
            [
                'legend' => __('Shipments'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'create_magento_shipment',
            \Magento\Framework\Data\Form\Element\Select::class,
            [
                'label' => __('Magento Shipment Creation'),
                'title' => __('Magento Shipment Creation'),
                'name' => 'create_magento_shipment',
                'values' => [
                    0 => __('Disabled'),
                    1 => __('Enabled'),
                ],
                'value' => (int)$invoicesAndShipmentSettings->isCreateMagentoShipment(),
                'tooltip' => __(
                    'Enable to automatically create shipment for the Magento order when the associated order
                    on Channel is shipped.'
                ),
            ]
        );

        $form->setUseContainer(false);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
