define([
    'Magento_Ui/js/modal/modal',
    'jquery',
    'M2ECore/Plugin/Messages',
], function (modal, $, MessageObj) {
    'use strict';

    return function (options) {
        const addAccountPopup = $('.custom-popup');

        $('#add_account').on('click', function () {
            modal({
                type: 'popup',
                buttons: []
            }, addAccountPopup);

            addAccountPopup.modal('openModal');
        });

        $('#account_credentials').on('submit', function (e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $.ajax({
                url: options.urlCreate,
                type: 'POST',
                data: formData,
                showLoader: true,
                dataType: 'json',
                success: function (response) {
                    addAccountPopup.modal('closeModal');

                    if (response.redirectUrl) {
                        setLocation(response.redirectUrl);
                    }
                }
            });
        });
    }
});
