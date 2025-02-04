define([
    'Kaufland/Product/Unmanaged/Move/RetrieveSelected',
    'Kaufland/Product/Unmanaged/Move/PrepareProducts',
    'Kaufland/Product/Unmanaged/Move/Processor',
], (RetrieveSelected, PrepareProducts, MoveProcess) => {
    'use strict';

    return {
        startMoveForProduct: (id, urlPrepareMove, urlGrid, urlListingCreate, accountId) => {
            PrepareProducts.prepareProducts(
                    urlPrepareMove,
                    [id],
                    accountId,
                    function (storefrontId) {
                        MoveProcess.openMoveToListingGrid(
                                urlGrid,
                                urlListingCreate,
                                accountId,
                                storefrontId
                        );
                    }
            );
        },

        startMoveForProducts: (massActionData, urlPrepareMove, urlGrid, urlGetSelectedProducts, urlListingCreate, accountId) => {
            RetrieveSelected.getSelectedProductIds(
                    massActionData,
                    urlGetSelectedProducts,
                    accountId,
                    function (selectedProductIds) {
                        PrepareProducts.prepareProducts(
                                urlPrepareMove,
                                selectedProductIds,
                                accountId,
                                function (storefrontId) {
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
