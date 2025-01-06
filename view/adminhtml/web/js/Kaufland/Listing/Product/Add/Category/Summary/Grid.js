define([
    'Kaufland/Grid'
], function () {

    window.KauflandListingProductAddCategorySummaryGrid = Class.create(Grid, {

        // ---------------------------------------

        prepareActions: function () {
            this.actions = {
                removeAction: this.remove.bind(this)
            };
        },

        // ---------------------------------------

        remove: function () {
            var self = this;

            Grid.prototype.confirm({
                actions: {
                    confirm: function () {
                        var url = Kaufland.url.get('kaufland_listing_product_add/removeSessionProductsByCategory');
                        new Ajax.Request(url, {
                            parameters: {
                                ids: self.getSelectedProductsString()
                            },
                            onSuccess: self.unselectAllAndReload.bind(self)
                        });
                    },
                    cancel: function () {
                        return false;
                    }
                }
            });
        },

        // ---------------------------------------

        confirm: function (config) {
            if (config.actions && config.actions.confirm) {
                config.actions.confirm();
            }
        }

        // ---------------------------------------
    });
});
