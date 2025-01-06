define([
    'jquery',
    'mage/translate',
    'Kaufland/Common',
    'Magento_Ui/js/modal/confirm'
], function ($, $t) {

    window.KauflandTemplateShipping = Class.create(Common, {

        initialize: function () {
            $.validator.addMethod('Kaufland-validate-handling-time-mode', function (value, element) {
                const el = $(element);
                if ($('#handling_time').val() === '' && el.val() == Kaufland.php.constant('\\M2E\\Kaufland\\Model\\Template\\Shipping::HANDLING_TIME_MODE_VALUE')) {
                    return false;
                }

                return true;
            }, $t('This is a required field.'));
        },

        initObservers: function () {
            $('#handling_time_mode')
                    .on('change', this.handlingTimeChange.bind(this))
                    .trigger('change');

            $('#storefront_id')
                    .on('change', this.getShippingGroupsByStorefront.bind(this))
                    .trigger('change');
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
            let url = Kaufland.url.get('kaufland_template/refreshWarehouses');
            $.ajax({
                url: url,
                type: 'POST',
                data: { empty: true },
                dataType: 'json',
                success: function (data) {
                    let select = $('#warehouse_id');
                    let options = '';

                    $.each(data, function (index, item) {
                        options += `<option value="${item.warehouse_id}">${item.name}</option>`;
                    });

                    select.html(options);
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
            let url = Kaufland.url.get('kaufland_template/getShippingGroupsByStorefront');
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
    });
});
