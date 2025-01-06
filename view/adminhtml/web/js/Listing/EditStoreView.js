define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/modal'
], function (jQuery, confirm, modal) {

    window.ListingEditListingStoreView = Class.create({

        // ---------------------------------------

        initialize: function (listingId) {
            this.listingId = listingId;
        },

        openPopup: function (id = null) {
            const listingId = id === null ? this.listingId : id;

            new Ajax.Request(Kaufland.url.get('listing/selectStoreView'), {
                method: 'GET',
                parameters: {
                    id: listingId,
                },
                onSuccess: (function (transport) {
                    if ($('edit_store_view_form')) {
                        $('edit_store_view_form').remove();
                    }

                    $('html-body').insert({bottom: transport.responseText});
                    const form = jQuery('#edit_store_view_form');
                    const initialStoreValue = form.find('#store_id').val();

                    modal({
                        title: Kaufland.translator.translate('Edit Listing Store View'),
                        type: 'popup',
                        modalClass: 'width-50',
                        buttons: [{
                            text: Kaufland.translator.translate('Cancel'),
                            class: 'action-secondary action-dismiss',
                            click: function () {
                                form.modal('closeModal');
                            }
                        }, {
                            text: Kaufland.translator.translate('Save'),
                            class: 'action-primary action-accept',
                            click: function () {
                                const currentStoreValue = form.find('#store_id').val();

                                if (currentStoreValue === initialStoreValue) {
                                    form.modal('closeModal');
                                    return false;
                                }

                                EditListingStoreViewObj.saveListingStoreView();
                            }
                        }]
                    }, form);

                    jQuery('#edit_store_view_form').modal('openModal');
                }).bind(this)
            });
        },

        saveListingStoreView: function () {
            if (!jQuery('#edit_store_view_form').valid()) {
                return false;
            }

            confirm({
                content: Kaufland.translator.translate('Are you sure?'),
                actions: {
                    confirm: function () {
                        new Ajax.Request(Kaufland.url.get('listing/saveStoreView'), {
                            method: 'post',
                            parameters: $('edit_store_view_form').serialize(),
                            onSuccess: (function (transport) {
                                jQuery('#edit_store_view_form').modal('closeModal');
                                location.reload();
                            })
                        });
                    },
                    cancel: function () {
                        jQuery('#edit_store_view_form').modal('closeModal');
                        return false;
                    }
                }
            });
        }

        // ---------------------------------------
    });
});
