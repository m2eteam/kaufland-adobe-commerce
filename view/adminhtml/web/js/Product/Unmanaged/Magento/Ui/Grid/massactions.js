define([
    'Magento_Ui/js/grid/massactions',
    'Kaufland/Product/Unmanaged/Move',
    'Kaufland/Common'
], function (Massactions, Move) {
    'use strict';

    return Massactions.extend({
        initialize: function () {
            this._super();
        },

        defaultCallback: function (action, massActionData) {
            if (action.type === 'move') {
                const urlPrepareMove = action.url_prepare_move;
                const urlGrid = action.url_grid;
                const urlGetSelectedProducts = action.url_get_selected_products;
                const urlListingCreate = action.url_listing_create;

                Move.startMoveForProducts(massActionData, urlPrepareMove, urlGrid, urlGetSelectedProducts, urlListingCreate);
                CommonObj.scrollPageToTop();
            } else {
                this._super();
            }
        },
    });
});
