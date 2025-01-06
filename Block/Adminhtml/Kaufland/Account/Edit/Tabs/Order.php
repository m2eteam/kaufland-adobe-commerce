<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Account\Edit\Tabs;

use M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractForm;
use M2E\Kaufland\Model\Account;
use Magento\Framework\Message\MessageInterface;

class Order extends AbstractForm
{
    private \Magento\Sales\Model\Order\Config $orderConfig;
    private \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory;
    private \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxClassCollectionFactory;
    private \M2E\Core\Helper\Magento\Store\Website $storeWebsite;
    private \M2E\Core\Helper\Magento\Store $storeHelper;
    private ?Account $account;

    public function __construct(
        \Magento\Sales\Model\Order\Config $orderConfig,
        \M2E\Core\Helper\Magento\Store $storeHelper,
        \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxClassCollectionFactory,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory,
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \M2E\Core\Helper\Magento\Store\Website $storeWebsite,
        \M2E\Kaufland\Model\Account $account = null,
        array $data = []
    ) {
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
        $this->taxClassCollectionFactory = $taxClassCollectionFactory;
        $this->storeWebsite = $storeWebsite;
        $this->storeHelper = $storeHelper;
        $this->account = $account;
        $this->orderConfig = $orderConfig;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $orderSettings = new Account\Settings\Order();
        if ($this->account !== null) {
            $orderSettings = $this->account->getOrdersSettings();
        }

        $form = $this->_formFactory->create();

        $form->addField(
            'kaufland_accounts_orders',
            self::HELP_BLOCK,
            [
                'content' => __(
                    '<p>Specify how M2E Kaufland should manage the Orders imported from Kaufland.</p><br/>
<p>You are able to configure the different rules of <strong>Magento Order Creation</strong> considering whether the
Item was listed via M2E Kaufland or by some other software.</p><br/>
<p>Once Kaufland Order is imported, the <strong>Reserve Quantity</strong> feature will hold the Stock if Magento Order
could not be created immediately in accordance with provided settings.</p><br/>
<p>Besides, you can configure the <strong>Tax, Order Number</strong> and <strong>Order Status Mapping</strong> Settings
for your Magento Orders as well as specify the automatic creation of invoices and shipment notifications.</p>'
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'listed_by_m2e',
            [
                'legend' => __('Product Is Listed By M2E Kaufland'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_mode',
            'select',
            [
                'name' => 'magento_orders_settings[listing][mode]',
                'label' => __('Create Order in Magento'),
                'values' => [
                    1 => __('Yes'),
                    0 => __('No'),
                ],
                'value' => (int)$orderSettings->isListingEnabled(),
                'tooltip' => __(
                    'Choose whether a Magento Order should be created if an Kaufland Order is received for
                    an Kaufland Item Listed using M2E Kaufland.'
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_store_mode',
            'select',
            [
                'container_id' => 'magento_orders_listings_store_mode_container',
                'name' => 'magento_orders_settings[listing][store_mode]',
                'label' => __('Magento Store View Source'),
                'values' => [
                    \M2E\Kaufland\Model\Account\Settings\Order::LISTINGS_STORE_MODE_DEFAULT => __('Use Store View from Listing'),
                    \M2E\Kaufland\Model\Account\Settings\Order::LISTINGS_STORE_MODE_CUSTOM => __('Choose Store View Manually'),
                ],
                'value' => $orderSettings->getListingStoreMode(),
                'tooltip' => __(
                    'Choose to specify the Magento Store View here or to keep the Magento
                    Store View used in the M2E Kaufland Listing.'
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_store_id',
            self::STORE_SWITCHER,
            [
                'container_id' => 'magento_orders_listings_store_id_container',
                'name' => 'magento_orders_settings[listing][store_id]',
                'label' => __('Magento Store View'),
                'required' => true,
                'value' => $orderSettings->getListingStoreIdForCustomMode() ?: $this->storeHelper->getDefaultStoreId(),
                'has_empty_option' => true,
                'has_default_option' => false,
                'tooltip' => __('The Magento Store View that Orders will be placed in.'),
            ]
        );

        $fieldset = $form->addFieldset(
            'listed_by_other',
            [
                'legend' => __('Product Is Listed By Any Other Software'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_other_mode',
            'select',
            [
                'name' => 'magento_orders_settings[listing_other][mode]',
                'label' => __('Create Order in Magento'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => (int)$orderSettings->isUnmanagedListingEnabled(),
                'tooltip' => __(
                    'Choose whether a Magento Order should be created if an Kaufland Order is received
for an item that does <b>not</b> belong to the M2E Kaufland Listing.'
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_other_store_id',
            self::STORE_SWITCHER,
            [
                'container_id' => 'magento_orders_listings_other_store_id_container',
                'name' => 'magento_orders_settings[listing_other][store_id]',
                'label' => __('Magento Store View'),
                'required' => true,
                'value' => $orderSettings->getUnmanagedListingStoreId() ?: $this->storeHelper->getDefaultStoreId(),
                'has_empty_option' => true,
                'has_default_option' => false,
                'tooltip' => __('The Magento Store View that Orders will be placed in.'),
            ]
        );

        $tooltip = __(
            'Choose what should happen if an Order is received for a Product that
                         does not exist in your Magento Inventory.'
        );
        $fieldset->addField(
            'magento_orders_listings_other_product_mode',
            'select',
            [
                'container_id' => 'magento_orders_listings_other_product_mode_container',
                'name' => 'magento_orders_settings[listing_other][product_mode]',
                'label' => __('Product Not Found'),
                'values' => [
                    \M2E\Kaufland\Model\Account\Settings\Order::LISTINGS_OTHER_PRODUCT_MODE_IGNORE => __('Do Not Create Order'),
                    \M2E\Kaufland\Model\Account\Settings\Order::LISTINGS_OTHER_PRODUCT_MODE_IMPORT => __('Create Product and Order'),
                ],
                'value' => $orderSettings->getUnmanagedListingProductMode(),
                'tooltip' => $tooltip
                    . '<span id="magento_orders_listings_other_product_mode_note">'
                    . __(
                        '<br/><b>Note:</b> M2E Kaufland will create only Simple Magento Products.
                        For Channel variational items it will create a Simple Product for each variation.
                        Please note that the Create Product and Order option is not meant
                        for the creation of full-fledged products in your Magento catalog.'
                    )
                    . '</span>',
            ]
        );

        $fieldset->addField(
            'magento_orders_listings_other_product_mode_warning',
            self::MESSAGES,
            [
                'messages' => [
                    [
                        'type' => \Magento\Framework\Message\MessageInterface::TYPE_NOTICE,
                        'content' => __(
                            'Please note that a new Magento Product will be created
                            if the corresponding SKU is not found in your Catalog.'
                        ),
                    ],
                ],
                'style' => 'max-width:450px; margin-left:20%',
            ]
        );

        $productTaxClasses = $this
            ->taxClassCollectionFactory
            ->create()
            ->addFieldToFilter('class_type', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT)
            ->toOptionArray();

        $none = [
            'value' => \M2E\Kaufland\Model\Magento\Product::TAX_CLASS_ID_NONE,
            'label' => __('None'),
        ];

        array_unshift($productTaxClasses, $none);

        $values = [];
        foreach ($productTaxClasses as $taxClass) {
            $values[$taxClass['value']] = $taxClass['label'];
        }

        $fieldset->addField(
            'magento_orders_listings_other_product_tax_class_id',
            'select',
            [
                'container_id' => 'magento_orders_listings_other_product_tax_class_id_container',
                'name' => 'magento_orders_settings[listing_other][product_tax_class_id]',
                'label' => __('Product Tax Class'),
                'values' => $values,
                'value' => $orderSettings->getUnmanagedListingProductTaxClassId(),
                'tooltip' => __('Tax Class which will be used for Products created by M2E Kaufland.'),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_kaufland_accounts_magento_orders_number',
            [
                'legend' => __('Magento Order Number'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_number_source',
            'select',
            [
                'name' => 'magento_orders_settings[number][source]',
                'label' => __('Source'),
                'values' => [
                    \M2E\Kaufland\Model\Account\Settings\Order::NUMBER_SOURCE_MAGENTO => __('Magento'),
                    \M2E\Kaufland\Model\Account\Settings\Order::NUMBER_SOURCE_CHANNEL => __('Kaufland'),
                ],
                'value' => $orderSettings->getMagentoOrderNumberSource(),
                'tooltip' => __(
                    'If source is set to Magento, Magento Order numbers are created basing on your Magento Settings.
                    If source is set to Kaufland, Magento Order numbers are the same as Kaufland Order numbers.'
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_number_prefix_prefix',
            'text',
            [
                'container_id' => 'magento_orders_number_prefix_container',
                'name' => 'magento_orders_settings[number][prefix][prefix]',
                'label' => __('General Prefix'),
                'value' => $orderSettings->getMagentoOrdersNumberRegularPrefix(),
                'maxlength' => 5,
            ]
        );

        $fieldset->addField(
            'order_number_example',
            'label',
            [
                'label' => '',
                'note' => __('e.g.') . ' <span id="order_number_example_container"></span>',
            ]
        );

        $shipByDateFieldset = $form->addFieldset(
            'magento_block_kaufland_accounts_magento_orders_shipping_information',
            [
                'legend' => __('Shipping information'),
                'collapsable' => true,
            ]
        );

        $shipByDateFieldset->addField(
            'magento_orders_ship_by_date_settings',
            'select',
            [
                'name' => 'magento_orders_settings[shipping_information][ship_by_date]',
                'label' => __('Import Ship by date to Magento order'),
                'values' => [
                    1 => __('Yes'),
                    0 => __('No'),
                ],
                'value' => (int)$orderSettings->isImportShipByDate(),
            ]
        );

        $shipByDateFieldset->addField(
            'magento_orders_order_validation_shipping_address_region_override',
            'select',
            [
                'name' => 'magento_orders_settings[shipping_information][shipping_address_region_override]',
                'label' => __('Override invalid Region/State required value'),
                'values' => [
                    0 => __('No'),
                    1 => __('Yes'),
                ],
                'value' => (int)$orderSettings->isRegionOverrideRequired(),
                'tooltip' => __(
                    'When enabled, the invalid Region/State value will be replaced with an alternative one to create
                     an order in Magento.'
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_kaufland_accounts_magento_orders_customer',
            [
                'legend' => __('Customer Settings'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_customer_mode',
            'select',
            [
                'name' => 'magento_orders_settings[customer][mode]',
                'label' => __('Customer'),
                'values' => [
                    \M2E\Kaufland\Model\Account\Settings\Order::CUSTOMER_MODE_GUEST => __('Guest Account'),
                    \M2E\Kaufland\Model\Account\Settings\Order::CUSTOMER_MODE_PREDEFINED => __('Predefined Customer'),
                    \M2E\Kaufland\Model\Account\Settings\Order::CUSTOMER_MODE_NEW => __('Create New'),
                ],
                'value' => $orderSettings->getCustomerMode(),
                'tooltip' => __(
                    '<b>Guest Account:</b> Magento Guest Checkout Option must be enabled to use this Option.
                    Use the default Guest Account. Do not create a Customer Account.<br/><br/>
                    <b>Predefined Customer:</b> Use a specific Customer for all Orders.
                    You should specify the Magento Customer ID to use.<br/><br/>
                    <b>Create New:</b> Create a new Customer in Magento for the Order.
                    If an existing Magento Customer has the same email address as the email address used for the
                    Kaufland Order, the Order will be assigned to that Customer instead.'
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_customer_id',
            'text',
            [
                'container_id' => 'magento_orders_customer_id_container',
                'class' => 'validate-digits Kaufland-account-customer-id',
                'name' => 'magento_orders_settings[customer][id]',
                'label' => __('Customer ID'),
                'value' => $orderSettings->getCustomerPredefinedId(),
                'required' => true,
            ]
        );
        $values = [];
        foreach ($this->storeWebsite->getWebsites(true) as $website) {
            $values[$website['website_id']] = $website['name'];
        }

        $fieldset->addField(
            'magento_orders_customer_new_website_id',
            'select',
            [
                'container_id' => 'magento_orders_customer_new_website_id_container',
                'name' => 'magento_orders_settings[customer][website_id]',
                'label' => __('Associate to Website'),
                'values' => $values,
                'value' => $orderSettings->getCustomerNewWebsiteId(),
                'required' => true,
            ]
        );

        $temp = $this->customerGroupCollectionFactory->create()->toArray();

        $values = [];
        foreach ($temp['items'] as $group) {
            $values[$group['customer_group_id']] = $group['customer_group_code'];
        }

        $fieldset->addField(
            'magento_orders_customer_new_group_id',
            'select',
            [
                'container_id' => 'magento_orders_customer_new_group_id_container',
                'name' => 'magento_orders_settings[customer][group_id]',
                'label' => __('Customer Group'),
                'values' => $values,
                'value' => $orderSettings->getCustomerNewGroupId(),
                'required' => true,
            ]
        );

        $value = [];
        if ($orderSettings->isCustomerNewNotifyWhenOrderCreated()) {
            $value[] = 'order_created';
        }
        if ($orderSettings->isCustomerNewNotifyWhenInvoiceCreated()) {
            $value[] = 'invoice_created';
        }

        $fieldset->addField(
            'magento_orders_customer_new_notifications',
            'multiselect',
            [
                'container_id' => 'magento_orders_customer_new_notifications_container',
                'name' => 'magento_orders_settings[customer][notifications][]',
                'label' => __('Send Emails When The Following Is Created'),
                'values' => [
                    ['label' => __('Magento Order'), 'value' => 'order_created'],
                    ['label' => __('Invoice'), 'value' => 'invoice_created'],
                ],
                'value' => $value,
                'tooltip' => __(
                    '<p>Necessary emails will be sent according to Magento Settings in
                    Stores > Configuration > Sales > Sales Emails.</p>
                    <p>Hold Ctrl Button to choose more than one Option.</p>'
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_customer_billing_address_mode',
            'select',
            [
                'name' => 'magento_orders_settings[customer][billing_address_mode]',
                'label' => __('Billing Address Usage'),
                'values' => [
                    \M2E\Kaufland\Model\Account\Settings\Order::USE_SHIPPING_ADDRESS_AS_BILLING_ALWAYS => __(
                        'Always'
                    ),
                    \M2E\Kaufland\Model\Account\Settings\Order::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT => __(
                        'Buyer & Recipient have the same name'
                    ),
                ],
                'value' => $orderSettings->getCustomerBillingAddressMode(),
                'note' => __('When to use shipping address as billing.'),
                'tooltip' => __(
                    'Choose if you want to use your customer’s shipping address as the billing one regularly
                    or only if the buyer and recipient have the same names.'
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_kaufland_accounts_magento_orders_rules',
            [
                'legend' => __('Order Creation Rules'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_creation_mode_immediately_warning',
            self::MESSAGES,
            [
                'messages' => [
                    [
                        'type' => MessageInterface::TYPE_WARNING,
                        'content' => __(
                            'Please note that Immediate Magento order creation sets certain limits to the update of
                            the later order changes. If the shipping or tax details are modified after checkout is
                            completed, these changes will not be reflected in Magento order.'
                        ),
                    ],
                ],
                'style' => 'display: none',
            ]
        );

        $values = [];
        for ($day = 1; $day <= 14; $day++) {
            if ($day === 1) {
                $values[$day] = __('For %number day', ['number' => $day]);
            } else {
                $values[$day] = __('For %number days', ['number' => $day]);
            }
        }

        $fieldset->addField(
            'magento_orders_qty_reservation_days',
            'select',
            [
                'container_id' => 'magento_orders_qty_reservation_days_container',
                'name' => 'magento_orders_settings[qty_reservation][days]',
                'label' => __('Reserve Quantity'),
                'values' => $values,
                'value' => $orderSettings->getQtyReservationDays(),
                'tooltip' => __(
                    'Choose for how long M2E Kaufland should reserve Magento Product quantity per kaufland Order until
                    Magento Order is created.'
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_kaufland_accounts_magento_orders_cancellation',
            [
                'legend' => $this->__('Cancellation'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_cancel_mode',
            'select',
            [
                'container_id' => 'magento_orders_cancel_container',
                'name' => 'magento_orders_settings[order_cancel_on_channel][mode]',
                'label' => $this->__('Cancel Kaufland Orders'),
                'values' => [
                    \M2E\Kaufland\Model\Account\Settings\Order::CANCEL_ON_CHANNEL_NO => $this->__('No'),
                    \M2E\Kaufland\Model\Account\Settings\Order::CANCEL_ON_CHANNEL_YES => $this->__('Yes'),
                ],
                'value' => $orderSettings->getOrderCancelOrRefundOnChannelMode(),
                'tooltip' => $this->__(
                    'Enable to cancel Kaufland orders and automatically update their statuses on the Channel.',
                ),
            ]
        );

        $fieldset->addField(
            'magento_orders_cancel_reason',
            'select',
            [
                'container_id' => 'magento_orders_cancel_reason_container',
                'name' => 'magento_orders_settings[order_cancel_on_channel][cancel_reason]',
                'label' => $this->__('Default Cancellation Reason'),
                'values' => [
                    \M2E\Kaufland\Model\Account\Settings\Order::CANCEL_ON_CHANNEL_REASON_BUYER_CANCELLED => $this->__(
                        'Buyer cancelled order'
                    ),
                    \M2E\Kaufland\Model\Account\Settings\Order::CANCEL_ON_CHANNEL_REASON_SHIPPING_ADDRESS_UNDELIVERABLE => $this->__(
                        'Could not deliver to entered address'
                    ),
                    \M2E\Kaufland\Model\Account\Settings\Order::CANCEL_ON_CHANNEL_REASON_WRONG_CATALOG_DATA => $this->__(
                        'Item does not match seller\'s description'
                    ),
                    \M2E\Kaufland\Model\Account\Settings\Order::CANCEL_ON_CHANNEL_REASON_MERCHANDISE_NOT_RECEIVED => $this->__(
                        'Item did not arrive due to shipping issues'
                    ),
                    \M2E\Kaufland\Model\Account\Settings\Order::CANCEL_ON_CHANNEL_REASON_NO_INVENTORY => $this->__(
                        'Item out of stock'
                    ),
                    \M2E\Kaufland\Model\Account\Settings\Order::CANCEL_ON_CHANNEL_REASON_DELAYED_INVENTORY => $this->__(
                        'Item is out of stock and delayed by supplier'
                    ),
                    \M2E\Kaufland\Model\Account\Settings\Order::CANCEL_ON_CHANNEL_REASON_WRONG_PRICE => $this->__(
                        'Incorrect price listed on marketplace'
                    ),
                    \M2E\Kaufland\Model\Account\Settings\Order::CANCEL_ON_CHANNEL_REASON_UNDELIVERABLE_REGION => $this->__(
                        'No response from customer to complete order'
                    ),
                    \M2E\Kaufland\Model\Account\Settings\Order::CANCEL_ON_CHANNEL_REASON_OTHER => $this->__(
                        'Other reason'
                    ),
                ],
                'value' => $orderSettings->getOrderCancelOnChannelReason(),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_kaufland_accounts_magento_orders_tax',
            [
                'legend' => __('Order Tax Settings'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_tax_mode',
            'select',
            [
                'name' => 'magento_orders_settings[tax][mode]',
                'label' => __('Tax Source'),
                'values' => [
                    \M2E\Kaufland\Model\Account\Settings\Order::TAX_MODE_NONE => __('None'),
                    \M2E\Kaufland\Model\Account\Settings\Order::TAX_MODE_CHANNEL => __('Kaufland'),
                    \M2E\Kaufland\Model\Account\Settings\Order::TAX_MODE_MAGENTO => __('Magento'),
                    \M2E\Kaufland\Model\Account\Settings\Order::TAX_MODE_MIXED => __('Kaufland & Magento'),
                ],
                'value' => $orderSettings->getTaxMode(),
                'tooltip' => __(
                    'Choose where the tax settings for your Magento Order will be taken from.',
                ),
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_kaufland_accounts_magento_orders_status_mapping',
            [
                'legend' => __('Order Status Mapping'),
                'collapsable' => true,
            ]
        );

        $fieldset->addField(
            'magento_orders_status_mapping_mode',
            'select',
            [
                'name' => 'magento_orders_settings[order_status_mapping][mode]',
                'label' => __('Status Mapping'),
                'values' => [
                    \M2E\Kaufland\Model\Account\Settings\Order::ORDERS_STATUS_MAPPING_MODE_DEFAULT => __('Default Order Statuses'),
                    \M2E\Kaufland\Model\Account\Settings\Order::ORDERS_STATUS_MAPPING_MODE_CUSTOM => __('Custom Order Statuses'),
                ],
                'value' => $orderSettings->getStatusMappingMode(),
                'tooltip' => __(
                    'Configure the mapping between Kaufland and Magento order statuses.
                    Magento order statuses will automatically update according to these settings.'
                ),
            ]
        );

        $statusList = $this->orderConfig->getStatuses();

        $fieldset->addField(
            'magento_orders_status_mapping_processing',
            'select',
            [
                'container_id' => 'magento_orders_status_mapping_processing_container',
                'name' => 'magento_orders_settings[order_status_mapping][processing]',
                'label' => __('Order Status is Unshipped / Partially Shipped'),
                'values' => $statusList,
                'value' => $orderSettings->getStatusMappingForProcessing(),
                'disabled' => $orderSettings->isOrderStatusMappingModeDefault(),
            ]
        );

        $fieldset->addField(
            'magento_orders_status_mapping_shipped',
            'select',
            [
                'container_id' => 'magento_orders_status_mapping_shipped_container',
                'name' => 'magento_orders_settings[order_status_mapping][shipped]',
                'label' => __('Shipping Is Completed'),
                'values' => $statusList,
                'value' => $orderSettings->getStatusMappingForProcessingShipped(),
                'disabled' => $orderSettings->isOrderStatusMappingModeDefault(),
            ]
        );

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
