define([
    'Kaufland/Product/Unmanaged/Move/RetrieveSelected',
    'Kaufland/Product/Unmanaged/Move/PrepareProducts',
    'Kaufland/Product/Unmanaged/Move/Processor',
], (RetrieveSelected, PrepareProducts, MoveProcess) => {
    'use strict';

    return {
        startMoveForProduct: (id, urlPrepareMove, urlGrid, urlListingCreate) => {
            PrepareProducts.prepareProducts(
                    urlPrepareMove,
                    [id],
                    function (accountId, storefrontId) {
                        MoveProcess.openMoveToListingGrid(
                                urlGrid,
                                urlListingCreate,
                                accountId,
                                storefrontId
                        );
                    }
            );
        },

        startMoveForProducts: (massActionData, urlPrepareMove, urlGrid, urlGetSelectedProducts, urlListingCreate) => {
            RetrieveSelected.getSelectedProductIds(
                    massActionData,
                    urlGetSelectedProducts,
                    function (selectedProductIds) {
                        PrepareProducts.prepareProducts(
                                urlPrepareMove,
                                selectedProductIds,
                                function (accountId, storefrontId) {
                                    MoveProcess.openMoveToListingGrid(
                                            urlGrid,
                                            urlListingCreate,
                                            accountId,
                                            storefrontId
                                    );
                                }
                        );
                    }
            );
        }
    };
});
