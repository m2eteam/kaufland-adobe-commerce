define([
    'jquery',
    'Kaufland/Kaufland/Listing/View/Grid',
    'Kaufland/Listing/MovingFromListing',
    'Magento_Ui/js/modal/modal'
], function (jQuery) {

    window.KauflandListingViewSettingsGrid = Class.create(KauflandListingViewGrid, {

        // ---------------------------------------

        storefrontId: null,
        accountId: null,

        // ---------------------------------------

        initialize: function ($super, gridId, listingId, storefrontId, accountId) {
            this.storefrontId = storefrontId;
            this.accountId = accountId;

            $super(gridId, listingId);
        },

        // ---------------------------------------

        prepareActions: function ($super) {
            $super();

            this.movingHandler = new MovingFromListing(this);

            this.actions = Object.extend(this.actions, {
                editCategorySettingsAction: function (id) {
                    KauflandListingCategoryObj.editCategorySettings(id, 'both');
                }.bind(this),

                movingAction: this.movingHandler.run.bind(this.movingHandler),
            });
        },

        // ---------------------------------------

        tryToMove: function (listingId) {
            this.movingHandler.submit(listingId, this.onSuccess)
        },

        onSuccess: function () {
            this.unselectAllAndReload();
        },

        // ---------------------------------------

        confirm: function (config) {
            if (config.actions && config.actions.confirm) {
                config.actions.confirm();
            }
        },
    });
});
