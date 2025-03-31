define([
    'jquery',
    'mage/translate',
    'Kaufland/Common',
    'Magento_Ui/js/modal/confirm'
], function ($, $t) {

    window.KauflandTemplateShipping = Class.create(Common, {

        accountId: null,
        storefrontId: null,
        warehouseId: null,
        shippingGroupId: null,

        initialize: function (config) {
            this.setAccountId($('#account_id').val());
            this.setStorefrontId(config.storefrontId);
            this.setWarehouseId(config.warehouseId);
            this.setShippingGroupId(config.shippingGroupId);

            $.validator.addMethod('Kaufland-validate-handling-time-mode', function (value, element) {
                const el = $(element);
                if ($('#handling_time').val() === '' && el.val() == Kaufland.php.constant('\\M2E\\Kaufland\\Model\\Template\\Shipping::HANDLING_TIME_MODE_VALUE')) {
                    return false;
                }

                return true;
            }, $t('This is a required field.'));
        },

        initObservers: function () {
            const self = this;

            $('#account_id').on('change', function() {
                self.setAccountId($('#account_id').val());
            });

            $('#handling_time_mode')
                    .on('change', this.handlingTimeChange.bind(this))
                    .trigger('change');

            $('#storefront_id').on('change', function() {
                self.setStorefrontId($('#storefront_id').val());
            });
        },

        setAccountId: function(id) {
            this.accountId = parseInt(id) || null;

            if (this.hasAccountId()) {
                $('#refresh_warehouse').show();
                this.loadAccountData();
            }
        },

        getAccountId: function() {
            return this.accountId;
        },

        hasAccountId: function() {
            return this.accountId !== null;
        },

        loadAccountData: function() {
            this.updateStorefronts();
            this.refreshWarehouses();
        },

        hasStorefrontId: function() {
            return this.storefrontId !== null;
        },

        getStorefrontId: function() {
            return this.storefrontId;
        },

        setStorefrontId: function(id) {
            this.storefrontId = id || null;

            if (this.hasStorefrontId()) {
                this.getShippingGroupsByStorefront();
            }
        },

        setWarehouseId: function(id) {
            this.warehouseId = id || null;
        },

        hasWarehouseId: function() {
            return this.warehouseId !== null;
        },

        getWarehouseId: function() {
            return this.warehouseId;
        },

        setShippingGroupId: function(id) {
            this.shippingGroupId = id || null;
        },

        hasShippingGroupId: function(id) {
            return this.shippingGroupId !== null;
        },

        getShippingGroupId: function(id) {
            return this.shippingGroupId;
        },

        updateStorefronts: function() {
            const self = this;

            new Ajax.Request(Kaufland.url.get('kaufland_account/getStorefrontsForAccount'), {
                method: 'get',
                parameters: { account_id: self.getAccountId() },
                onSuccess: function(transport) {
                    const response = JSON.parse(transport.responseText);
                    if (response.result) {
                        self.renderStorefronts(response.storefronts);
                        return;
                    }

                    console.error(response.message);
                },
            });
        },

        renderStorefronts: function(storefronts) {
            const select = jQuery('#storefront_id');
            select.find('option').remove();

            select.append(new Option('', ''));
            storefronts.forEach(function(storefront) {
                select.append(new Option(storefront.storefront_name, storefront.id));
            });
            if (this.hasStorefrontId()) {
                select.val(this.getStorefrontId());
            }
        },

        handlingTimeChange: function (event) {
            const el = event.target;

            if (el.value == Kaufland.php.constant('\\M2E\\Kaufland\\Model\\Template\\Shipping::HANDLING_TIME_MODE_VALUE')) {
                this.updateHiddenValue(el, $('#handling_time')[0]);
            }

            if (el.value == Kaufland.php.constant('\\M2E\\Kaufland\\Model\\Template\\Shipping::HANDLING_TIME_MODE_ATTRIBUTE')) {
                this.updateHiddenValue(el, $('#handling_time_attribute')[0]);
            }
        },

        refreshWarehouses: function () {
            const self = this;

            let url = Kaufland.url.get('kaufland_template/refreshWarehouses');
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    account_id: self.getAccountId()
                },
                dataType: 'json',
                success: function (data) {
                    let select = $('#warehouse_id');
                    let options = '';

                    $.each(data, function (index, item) {
                        options += `<option value="${item.warehouse_id}">${item.name}</option>`;
                    });

                    select.html(options);
                    if (self.hasWarehouseId()) {
                        select.val(self.getWarehouseId());
                    }
                },
            });
        },

        refreshShippingGroups: function () {
            let url = Kaufland.url.get('kaufland_template/refreshShippingGroups');
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    storefront_id: $('#storefront_id').val()
                },
                dataType: 'json',
                success: function (data) {
                    let select = $('#shipping_group_id');
                    let options = '';

                    $.each(data, function (index, item) {
                        options += `<option value="${item.shipping_group_id}">${item.name}</option>`;
                    });

                    select.html(options);
                },
            });
        },

        getShippingGroupsByStorefront: function () {
            const self = this;

            let url = Kaufland.url.get('kaufland_template/getShippingGroupsByStorefront');
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    storefront_id: self.getStorefrontId()
                },
                dataType: 'json',
                success: function (data) {
                    let select = $('#shipping_group_id');
                    let options = '';

                    $.each(data, function (index, item) {
                        options += `<option value="${item.shipping_group_id}">${item.name}</option>`;
                    });

                    select.html(options);
                    if (self.hasShippingGroupId()) {
                        select.val(self.getShippingGroupId());
                    }
                    $('#refresh_shipping_group').show();
                },
            });
        },
    });
});
