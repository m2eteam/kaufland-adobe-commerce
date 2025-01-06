define([
    'jquery',
], ($) => {
    'use strict';

    return {
        prepareProducts: function(url, productsIds, callback) {
            $.ajax(
                    {
                        url: url,
                        type: 'POST',
                        data: {
                            other_product_ids: productsIds,
                        },
                        dataType: 'json',
                        success: function(data) {
                            callback(data.accountId, data.storefrontId);
                        },
                    }
            );
        },
    };
});
