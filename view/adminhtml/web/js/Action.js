define([
    'Kaufland/Common'
], function () {

    window.Action = Class.create(Common, {
        initialize: function (gridHandler) {
            this.gridHandler = gridHandler;
        }
    });
});
