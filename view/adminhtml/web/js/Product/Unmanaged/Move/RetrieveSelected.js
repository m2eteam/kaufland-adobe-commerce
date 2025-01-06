define([
    'underscore',
    'jquery',
    'M2ECore/Plugin/Messages'
], (_, $, MessagesObj) => {
    'use strict';

    return {
        getSelectedProductIds: function (massActionData, urlGetSelectedProducts, callback) {
            const params = this.createFilterParams(massActionData);

            $.ajax(
                    {
                        url: urlGetSelectedProducts,
                        type: 'POST',
                        data: params,
                        dataType: 'json',
                        success: function(data) {
                            if (data.message) {
                                MessagesObj.addError(data.message);
                            } else {
                                callback(data.selected_products);
                            }
                        },
                    }
            );
        },

        createFilterParams: function (massActionData) {
            const itemsType = massActionData.excludeMode ? 'excluded' : 'selected';
            const selections = {};

            selections[itemsType] = massActionData[itemsType];

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, massActionData.params || {});

            return selections;
        }
    };
});
