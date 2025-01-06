define([
    'Kaufland/Common'
], function () {

    window.KauflandListingAllItemsAction = Class.create(Common, {

        // ---------------------------------------

        initialize: function ($super, actionProcessor) {
            this.actionProcessor = actionProcessor;
        },

        // ---------------------------------------

        listAction: function () {
            this.processActions(
                    Kaufland.translator.translate('listing_selected_items_message'),
                    Kaufland.url.get('runListProducts'),
            );
        },

        relistAction: function () {
            this.processActions(
                    Kaufland.translator.translate('relisting_selected_items_message'),
                    Kaufland.url.get('runRelistProducts'),
            );
        },

        reviseAction: function () {
            this.processActions(
                    Kaufland.translator.translate('revising_selected_items_message'),
                    Kaufland.url.get('runReviseProducts'),
            );
        },

        stopAction: function () {
            this.processActions(
                    Kaufland.translator.translate('stopping_selected_items_message'),
                    Kaufland.url.get('runStopProducts'),
            );
        },

        stopAndRemoveAction: function () {
            this.processActions(
                    Kaufland.translator.translate('stopping_and_removing_selected_items_message'),
                    Kaufland.url.get('runStopAndRemoveProducts'),
            );
        },

        // ---------------------------------------

        processActions: function (title, url) {

            var requestParams = {
                'is_realtime': (this.actionProcessor.gridHandler.getSelectedProductsArray().length <= 10)
            };
            this.actionProcessor.processActions(title, url, requestParams);
        },

        // ---------------------------------------
    });
});
