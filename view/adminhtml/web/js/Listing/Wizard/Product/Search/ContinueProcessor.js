define([
    'jquery',
    'mage/storage',
    'mage/translate',
    'Magento_Ui/js/modal/modal'
], function ($, storage, $t, modal) {
    'use strict';

    return function (options, continueButton) {
        const processor = {
            urlCheck: options.urlCheck,
            urlContinue: options.urlContinue,
            urlEnableProductNewMode: options.urlEnableProductNewMode,

            process: function () {
                storage.get(this.urlCheck)
                        .done(this.processCheckResult.bind(this));
            },

            processCheckResult: function (response) {
                const isSearchCompleted = response.is_search_completed;
                if (!isSearchCompleted) {
                    alert($t('Please configure the EAN setting to complete the Product search on Kaufland.'));

                    return;
                }

                const totalProductForCreate = response.count_products_for_create;
                if (totalProductForCreate === 0) {
                    this.goFrom();

                    return;
                }

                this.openPopup(response.popupHtml);
            },

            openPopup: function (content) {
                $('body').append(content);

                this.newProductPopup = $('#search_ean_new_product_popup');

                const self = this;

                modal({
                    title: $t('New Kaufland Product Creation'),
                    type: 'popup',
                    modalClass: 'search_ean_new_product_popup_class',
                    buttons: [{
                        class: 'action-secondary action-dismiss add_products_search_ean_new_product_popup_no',
                        text: $t('No'),
                        click: function () {
                            self.goFrom();
                        }
                    }, {
                        class: 'action-primary action-accept add_products_search_ean_new_product_popup_yes',
                        text: $t('Yes'),
                        click: function () {
                            self.enableCreateNewProduct();
                            self.goFrom();
                        }
                    }]
                }, this.newProductPopup);

                this.newProductPopup.modal('openModal');
            },

            enableCreateNewProduct: function () {
                storage.post(
                        this.urlEnableProductNewMode,
                        {form_key: FORM_KEY},
                        undefined,
                        'application/x-www-form-urlencoded'
                );
            },

            goFrom: function () {
                window.location.href = this.urlContinue;
            }
        };

        $(continueButton).on('click', function (e) {
            e.preventDefault();
            processor.process();
        });
    };
});
