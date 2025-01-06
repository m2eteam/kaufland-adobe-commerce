define([
    'Kaufland/Grid',
    'prototype'
], function () {

    window.KauflandListingGrid = Class.create(Grid, {

        // ---------------------------------------

        backParam: base64_encode('*/Kaufland_listing/index'),

        // ---------------------------------------

        prepareActions: function () {
            return false;
        },

        // ---------------------------------------

        addProductsSourceProductsAction: function (id) {
            setLocation(Kaufland.url.get('Kaufland_listing_product_add/index', {
                id: id,
                source: 'product',
                clear: true,
                back: this.backParam
            }));
        },

        // ---------------------------------------

        addProductsSourceCategoriesAction: function (id) {
            setLocation(Kaufland.url.get('Kaufland_listing_product_add/index', {
                id: id,
                source: 'category',
                clear: true,
                back: this.backParam
            }));
        }

        // ---------------------------------------
    });

});
