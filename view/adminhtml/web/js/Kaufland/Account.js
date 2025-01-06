define([
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'M2ECore/Plugin/Messages',
    'Kaufland/Common',
    'extjs/ext-tree-checkbox',
    'mage/adminhtml/form',
], function (modal, $t, MessageObj) {

    window.KauflandAccount = Class.create(Common, {

        // ---------------------------------------

        initialize: function () {

            jQuery.validator.addMethod('Kaufland-account-customer-id', function (value) {

                var checkResult = false;

                if ($('magento_orders_customer_id_container').getStyle('display') == 'none') {
                    return true;
                }

                new Ajax.Request(Kaufland.url.get('general/checkCustomerId'), {
                    method: 'post',
                    asynchronous: false,
                    parameters: {
                        customer_id: value,
                        id: Kaufland.formData.id
                    },
                    onSuccess: function (transport) {
                        checkResult = transport.responseText.evalJSON()['ok'];
                    }
                });

                return checkResult;
            }, $t('No Customer entry is found for specified ID.'));

            jQuery.validator.addMethod(
                    'Kaufland-require-select-attribute',
                    function (value, el) {
                        if ($('other_listings_mapping_mode').value == 0) {
                            return true;
                        }

                        var isAttributeSelected = false;

                        $$('.attribute-mode-select').each(function (obj) {
                            if (obj.value != 0) {
                                isAttributeSelected = true;
                            }
                        });

                        return isAttributeSelected;
                    },
                    $t('If Yes is chosen, you must select at least one Attribute for Product Linking.')
            );
        },

        initObservers: function () {
            if ($('kauflandAccountEditTabs_listingOther')) {

                $('other_listings_synchronization')
                        .observe('change', this.other_listings_synchronization_change)
                        .simulate('change');
                $('other_listings_mapping_mode')
                        .observe('change', this.other_listings_mapping_mode_change)
                        .simulate('change');
                $('mapping_sku_mode')
                        .observe('change', this.mapping_sku_mode_change)
                        .simulate('change');
                $('mapping_ean_mode')
                        .observe('change', this.mapping_ean_mode_change)
                        .simulate('change');
                $('mapping_item_id_mode')
                        .observe('change', this.mapping_item_id_mode_change)
                        .simulate('change');
            }

            if ($('kauflandAccountEditTabs_order')) {

                $('magento_orders_listings_mode')
                        .observe('change', this.magentoOrdersListingsModeChange)
                        .simulate('change');
                $('magento_orders_listings_store_mode')
                        .observe('change', this.magentoOrdersListingsStoreModeChange)
                        .simulate('change');

                $('magento_orders_listings_other_mode')
                        .observe('change', this.magentoOrdersListingsOtherModeChange)
                        .simulate('change');
                $('magento_orders_listings_other_product_mode')
                        .observe('change', this.magentoOrdersListingsOtherProductModeChange);

                $('magento_orders_number_source')
                        .observe('change', this.magentoOrdersNumberChange);
                $('magento_orders_number_prefix_prefix')
                        .observe('keyup', this.magentoOrdersNumberChange);

                KauflandAccountObj.renderOrderNumberExample();

                $('magento_orders_customer_mode')
                        .observe('change', this.magentoOrdersCustomerModeChange)
                        .simulate('change');

                $('magento_orders_status_mapping_mode').observe('change', KauflandAccountObj.magentoOrdersStatusMappingModeChange);

                $('order_number_example-note').previous().remove();
            }
        },

        // ---------------------------------------

        saveAndClose: function () {
            var self = this,
                    url = typeof Kaufland.url.urls.formSubmit == 'undefined' ?
                            Kaufland.url.formSubmit + 'back/' + Base64.encode('list') + '/' :
                            Kaufland.url.get('formSubmit', {'back': Base64.encode('list')});

            if (!this.isValidForm()) {
                return;
            }

            new Ajax.Request(url, {
                method: 'post',
                parameters: Form.serialize($('edit_form')),
                onSuccess: function (transport) {
                    transport = transport.responseText.evalJSON();

                    if (transport.success) {
                        window.close();
                    } else {
                        self.alert(transport.message);
                    }
                }
            });
        },

        // ---------------------------------------

        deleteClick: function (id) {
            this.confirm({
                content: Kaufland.translator.translate('confirmation_account_delete'),
                actions: {
                    confirm: function () {
                        if (id === undefined) {
                            setLocation(Kaufland.url.get('deleteAction'));
                        } else {
                            setLocation(Kaufland.url.get('*/kaufland_account/delete', {
                                id: id,
                            }));
                        }
                    },
                    cancel: function () {
                        return false;
                    }
                }
            });
        },

        openAccessDataPopup: function (postUrl) {
            var popup = jQuery('#account_credentials');

            modal({
                'type': 'popup',
                'modalClass': 'custom-popup',
                'responsive': true,
                'innerScroll': true,
                'buttons': []
            }, popup);

            popup.modal('openModal');

            jQuery('body').on('submit', '#account_credentials', function (e) {
                e.preventDefault();

                jQuery.ajax({
                    type: 'POST',
                    url: postUrl,
                    data: popup.serialize(),
                    showLoader: true,
                    dataType: 'json',
                    success: function (response) {
                        jQuery('#account_credentials').modal('closeModal');

                        if (response.redirectUrl) {
                            setLocation(response.redirectUrl);
                        }
                    },
                    error: function () {
                        jQuery('#account_credentials').modal('closeModal');
                    }
                });
            });
        },

        // ---------------------------------------

        magentoOrdersListingsModeChange: function () {
            var self = KauflandAccountObj;

            if ($('magento_orders_listings_mode').value == 1) {
                $('magento_orders_listings_store_mode_container').show();
            } else {
                $('magento_orders_listings_store_mode_container').hide();
                $('magento_orders_listings_store_mode').value = Kaufland.php.constant('Account\\Settings\\Order::LISTINGS_STORE_MODE_DEFAULT');
            }

            self.magentoOrdersListingsStoreModeChange();
            self.changeVisibilityForOrdersModesRelatedBlocks();
        },

        magentoOrdersListingsStoreModeChange: function () {
            if ($('magento_orders_listings_store_mode').value == Kaufland.php.constant('Account\\Settings\\Order::LISTINGS_STORE_MODE_CUSTOM')) {
                $('magento_orders_listings_store_id_container').show();
            } else {
                $('magento_orders_listings_store_id_container').hide();
                $('magento_orders_listings_store_id').value = '';
            }
        },

        magentoOrdersListingsOtherModeChange: function () {
            var self = KauflandAccountObj;

            $('magento_orders_listings_other_product_mode_container').hide(); //hide until ready get product info

            if ($('magento_orders_listings_other_mode').value == 1) {
                $('magento_orders_listings_other_store_id_container').show();
            } else {
                $('magento_orders_listings_other_store_id_container').hide();
                $('magento_orders_listings_other_product_mode').value = Kaufland.php.constant('Account\\Settings\\Order::LISTINGS_OTHER_PRODUCT_MODE_IGNORE');
                $('magento_orders_listings_other_store_id').value = '';
            }

            self.magentoOrdersListingsOtherProductModeChange();
            self.changeVisibilityForOrdersModesRelatedBlocks();
        },

        magentoOrdersStatusMappingModeChange: function() {
            // Reset dropdown selected values to default
            $('magento_orders_status_mapping_processing').value = Kaufland.php.constant('Account\\Settings\\Order::ORDERS_STATUS_MAPPING_PROCESSING');
            $('magento_orders_status_mapping_shipped').value = Kaufland.php.constant('Account\\Settings\\Order::ORDERS_STATUS_MAPPING_SHIPPED');

            var disabled = $('magento_orders_status_mapping_mode').value == Kaufland.php.constant('Account\\Settings\\Order::ORDERS_STATUS_MAPPING_MODE_DEFAULT');
            $('magento_orders_status_mapping_processing').disabled = disabled;
            $('magento_orders_status_mapping_shipped').disabled = disabled;
        },

        magentoOrdersListingsOtherProductModeChange: function () {
            if ($('magento_orders_listings_other_product_mode').value == Kaufland.php.constant('Account\\Settings\\Order::LISTINGS_OTHER_PRODUCT_MODE_IGNORE')) {
                $('magento_orders_listings_other_product_mode_note').hide();
                $('magento_orders_listings_other_product_tax_class_id_container').hide();
                $('magento_orders_listings_other_product_mode_warning').hide();
            } else {
                $('magento_orders_listings_other_product_mode_note').show();
                $('magento_orders_listings_other_product_tax_class_id_container').show();
                $('magento_orders_listings_other_product_mode_warning').show();
            }
        },

        magentoOrdersNumberChange: function () {
            var self = KauflandAccountObj;
            self.renderOrderNumberExample();
        },

        renderOrderNumberExample: function () {
            var orderNumber = '123456789';
            if ($('magento_orders_number_source').value == Kaufland.php.constant('Account\\Settings\\Order::NUMBER_SOURCE_CHANNEL')) {
                orderNumber = '123412341234123100';
            }

            orderNumber = $('magento_orders_number_prefix_prefix').value + orderNumber;

            $('order_number_example_container').update(orderNumber);
        },

        magentoOrdersCustomerModeChange: function () {
            var customerMode = $('magento_orders_customer_mode').value;

            if (customerMode == Kaufland.php.constant('Account\\Settings\\Order::CUSTOMER_MODE_PREDEFINED')) {
                $('magento_orders_customer_id_container').show();
                $('magento_orders_customer_id').addClassName('Kaufland-account-product-id');
            } else {  // Kaufland.php.constant('Account\Settings\Order::ORDERS_CUSTOMER_MODE_GUEST')
                // || Kaufland.php.constant('Account\Settings\Order::CUSTOMER_MODE_NEW')
                $('magento_orders_customer_id_container').hide();
                $('magento_orders_customer_id').value = '';
                $('magento_orders_customer_id').removeClassName('Kaufland-account-product-id');
            }

            var action = (customerMode == Kaufland.php.constant('Account\\Settings\\Order::CUSTOMER_MODE_NEW')) ? 'show' : 'hide';
            $('magento_orders_customer_new_website_id_container')[action]();
            $('magento_orders_customer_new_group_id_container')[action]();
            $('magento_orders_customer_new_notifications_container')[action]();

            if (action == 'hide') {
                $('magento_orders_customer_new_website_id').value = '';
                $('magento_orders_customer_new_group_id').value = '';
                $('magento_orders_customer_new_notifications').value = '';
            }
        },

        changeVisibilityForOrdersModesRelatedBlocks: function () {
            var self = KauflandAccountObj;

            if ($('magento_orders_listings_mode').value == 0 && $('magento_orders_listings_other_mode').value == 0) {

                $('magento_block_kaufland_accounts_magento_orders_number-wrapper').hide();
                $('magento_orders_number_source').value = Kaufland.php.constant('Account\\Settings\\Order::NUMBER_SOURCE_MAGENTO');

                $('magento_block_kaufland_accounts_magento_orders_customer-wrapper').hide();
                $('magento_orders_customer_mode').value = Kaufland.php.constant('Account\\Settings\\Order::CUSTOMER_MODE_GUEST');
                self.magentoOrdersCustomerModeChange();

                $('magento_block_kaufland_accounts_magento_orders_rules-wrapper').hide();
                $('magento_orders_qty_reservation_days').value = 1;

                $('magento_block_kaufland_accounts_magento_orders_tax-wrapper').hide();
                $('magento_orders_tax_mode').value = Kaufland.php.constant('Account\\Settings\\Order::TAX_MODE_MIXED');

                $('magento_orders_customer_billing_address_mode').value = Kaufland.php.constant('Account\\Settings\\Order::USE_SHIPPING_ADDRESS_AS_BILLING_IF_SAME_CUSTOMER_AND_RECIPIENT');
            } else {
                $('magento_block_kaufland_accounts_magento_orders_number-wrapper').show();
                $('magento_block_kaufland_accounts_magento_orders_customer-wrapper').show();
                $('magento_block_kaufland_accounts_magento_orders_rules-wrapper').show();
                $('magento_block_kaufland_accounts_magento_orders_tax-wrapper').show();
            }
        },

        // ---------------------------------------

        other_listings_synchronization_change: function () {
            var relatedStoreViews = $('magento_block_kaufland_accounts_other_listings_related_store_views-wrapper');

            if (this.value == 1) {
                $('other_listings_mapping_mode_tr').show();
                $('other_listings_mapping_mode').simulate('change');
                if (relatedStoreViews) {
                    relatedStoreViews.show();
                }
            } else {
                $('other_listings_mapping_mode').value = 0;
                $('other_listings_mapping_mode').simulate('change');
                $('other_listings_mapping_mode_tr').hide();
                if (relatedStoreViews) {
                    relatedStoreViews.hide();
                }
            }
        },

        other_listings_mapping_mode_change: function () {
            if (this.value == 1) {
                $('magento_block_kaufland_accounts_other_listings_product_mapping-wrapper').show();
            } else {
                $('magento_block_kaufland_accounts_other_listings_product_mapping-wrapper').hide();

                $('mapping_sku_mode').value = Kaufland.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_SKU_MODE_NONE');
                $('mapping_ean_mode').value = Kaufland.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_EAN_MODE_NONE');
            }

            $('mapping_sku_mode').simulate('change');
            $('mapping_ean_mode').simulate('change');
        },

        synchronization_mapped_change: function () {
            if (this.value == 0) {
                $('settings_button').hide();
            } else {
                $('settings_button').show();
            }
        },

        mapping_sku_mode_change: function () {
            var self = KauflandAccountObj,
                    attributeEl = $('mapping_sku_attribute');

            $('mapping_sku_priority').hide();
            if (this.value != Kaufland.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_SKU_MODE_NONE')) {
                $('mapping_sku_priority').show();
            }

            attributeEl.value = '';
            if (this.value == Kaufland.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_SKU_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, attributeEl);
            }
        },

        mapping_ean_mode_change: function () {
            var self = KauflandAccountObj,
                    attributeEl = $('mapping_ean_attribute');

            $('mapping_ean_priority').hide();
            if (this.value != Kaufland.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_EAN_MODE_NONE')) {
                $('mapping_ean_priority').show();
            }

            attributeEl.value = '';
            if (this.value == Kaufland.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_EAN_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, attributeEl);
            }
        },

        // todo need fix
        mapping_item_id_mode_change: function () {
            var self = KauflandAccountObj,
                    attributeEl = $('mapping_item_id_attribute');

            $('mapping_item_id_priority').hide();
            if (this.value != Kaufland.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_ITEM_ID_MODE_NONE')) {
                $('mapping_item_id_priority').show();
            }

            attributeEl.value = '';
            if (this.value == Kaufland.php.constant('Account\\Settings\\UnmanagedListings::MAPPING_ITEM_ID_MODE_CUSTOM_ATTRIBUTE')) {
                self.updateHiddenValue(this, attributeEl);
            }
        },

        // ---------------------------------------
    });

});
