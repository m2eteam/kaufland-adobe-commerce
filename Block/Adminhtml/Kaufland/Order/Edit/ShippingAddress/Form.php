<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Order\Edit\ShippingAddress;

use M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm;

class Form extends AbstractForm
{
    private \M2E\Kaufland\Helper\Data $dataHelper;
    private \M2E\Core\Helper\Magento $magentoHelper;
    private \M2E\Kaufland\Model\Order $order;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Kaufland\Helper\Data $dataHelper,
        \M2E\Core\Helper\Magento $magentoHelper,
        \M2E\Kaufland\Model\Order $order,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->dataHelper = $dataHelper;
        $this->magentoHelper = $magentoHelper;
        $this->order = $order;
    }

    protected function _prepareForm()
    {
        $order = $this->order;

        $buyerEmail = $order->getBuyerEmail();
        if (stripos($buyerEmail, 'Invalid Request') !== false) {
            $buyerEmail = '';
        }

        $address = $order->getShippingAddress()->getData();

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                ],
            ]
        );

        $fieldset = $form->addFieldset(
            'order_address_info',
            [
                'legend' => __('Order Address Information'),
            ]
        );

        $fieldset->addField(
            'buyer_email',
            'text',
            [
                'name' => 'buyer_email',
                'label' => __('Buyer Email'),
                'value' => $buyerEmail,
                'required' => true,
            ]
        );

        $fieldset->addField(
            'recipient_name',
            'text',
            [
                'name' => 'recipient_name',
                'label' => __('Recipient Name'),
                'value' => isset($address['recipient_name'])
                    ? \M2E\Core\Helper\Data::escapeHtml($address['recipient_name']) : '',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'street_0',
            'text',
            [
                'name' => 'street',
                'label' => __('Street Address'),
                'value' => isset($address['street'])
                    ? \M2E\Core\Helper\Data::escapeHtml($address['street']) : '',
                'required' => true,
            ]
        );

        //$fieldset->addField(
        //    'street_1',
        //    'text',
        //    [
        //        'name' => 'street[1]',
        //        'label' => '',
        //        'value' => isset($address['street'][1])
        //            ? \M2E\Core\Helper\Data::escapeHtml($address['street'][1]) : '',
        //    ]
        //);

        $fieldset->addField(
            'city',
            'text',
            [
                'name' => 'city',
                'label' => __('City'),
                'value' => $address['city'],
                'required' => true,
            ]
        );

        $fieldset->addField(
            'country_code',
            'select',
            [
                'name' => 'country_code',
                'label' => __('Country'),
                'values' => $this->magentoHelper->getCountries(),
                'value' => $address['country_code'],
                'required' => true,
            ]
        );

        $fieldset->addField(
            'postal_code',
            'text',
            [
                'name' => 'postal_code',
                'label' => __('Zip/Postal Code'),
                'value' => $address['postal_code'],
            ]
        );

        $fieldset->addField(
            'phone',
            'text',
            [
                'name' => 'phone',
                'label' => __('Telephone'),
                'value' => $address['phone'],
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        $this->jsUrl->addUrls($this->dataHelper->getControllerActions('Order'));
        $this->jsUrl->add(
            $this->getUrl(
                '*/Kaufland_order_shippingAddress/save',
                ['order_id' => $this->getRequest()->getParam('id')]
            ),
            'formSubmit'
        );

        return parent::_prepareForm();
    }
}
