define([
    'Magento_Ui/js/modal/modal',
    'M2ECore/Plugin/Messages',
    'Kaufland/Action'
], function (modal, MessageObj) {

    window.KauflandListingViewKauflandBids = Class.create(Action, {

        // ---------------------------------------

        initialize: function ($super, gridHandler) {
            var self = this;

            $super(gridHandler);

        },

        // ---------------------------------------

        parseResponse: function (response) {
            if (!response.responseText.isJSON()) {
                return;
            }

            return response.responseText.evalJSON();
        },

        // ---------------------------------------

        openPopUp: function (productId, title) {
            var self = this;

            MessageObj.clear();

            new Ajax.Request(Kaufland.url.get('kaufland_listing/getListingProductBids'), {
                method: 'post',
                parameters: {
                    product_id: productId
                },
                onSuccess: function (transport) {

                    var containerEl = $('kaufland_listing_product_bids');

                    if (containerEl) {
                        containerEl.remove();
                    }

                    $('html-body').insert({bottom: '<div id="kaufland_listing_product_bids"></div>'});
                    $('kaufland_listing_product_bids').update(transport.responseText);

                    self.listingProductBidsPopup = jQuery('#kaufland_listing_product_bids');

                    modal({
                        title: title,
                        type: 'popup',
                        buttons: [{
                            text: Kaufland.translator.translate('Close'),
                            class: 'action-secondary',
                            click: function () {
                                self.listingProductBidsPopup.modal('closeModal')
                            }
                        }]
                    }, self.listingProductBidsPopup);

                    self.listingProductBidsPopup.modal('openModal');
                }
            });
        }

        // ---------------------------------------
    });
});
