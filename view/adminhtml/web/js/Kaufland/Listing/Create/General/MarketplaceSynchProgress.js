define([
    'Kaufland/SynchProgress'
], function () {
    KauflandListingCreateGeneralMarketplaceSynchProgress = Class.create(SynchProgress, {

        // ---------------------------------------

        end: function ($super) {
            $super();

            var self = this;
            if (self.result == self.resultTypeError) {
                self.printFinalMessage();
                CommonObj.scrollPageToTop();
                return;
            }

            this.saveClick(Kaufland.url.get('kaufland_listing_create/index'), true)
        }

        // ---------------------------------------
    });
});
