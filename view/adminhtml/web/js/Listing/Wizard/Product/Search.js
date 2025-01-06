define([
    'jquery',
    'mage/storage',
    'mage/translate',
    'Kaufland/Plugin/ProgressBar',
    'Magento_Ui/js/modal/modal'
], function($, storage, $t, ProgressBar, modal) {
    'use strict';

    return function (options) {

        const search = {
            urlForSearch: options.url_for_search,
            isNeedSearch: options.is_need_search,

            totalItems: 0,
            processedItems: 0,

            progressBar: null,
            isWaiterActive: false,

            startProcess: function (progressBar)  {
                if (!this.isNeedSearch) {
                    return;
                }

                this.ProgressBar = progressBar;

                this.ProgressBar.reset();
                this.ProgressBar.setTitle($t('Search Kaufland Products'));
                this.ProgressBar.setStatus($t('Search in process. Please wait...'));
                this.ProgressBar.show();

                this.updateProgressBar();
            },

            endProcess: function (withReload = true) {
                this.stopWaiter();

                this.ProgressBar.setPercents(100, 1);
                this.ProgressBar.setStatus('Search has been completed.');

                setTimeout(() => {
                    this.ProgressBar.hide();

                    if (withReload) {
                        location.reload(true);
                    }
                }, 500);
            },

            search: function () {
                if (!this.isNeedSearch) {
                    this.endProcess();

                    return;
                }

                this.startWaiter();

                storage.get(this.urlForSearch)
                        .done(this.processResponse.bind(this))
                        .fail(this.processError.bind(this));
            },

            processResponse: function (response) {
                this.isNeedSearch = !response.is_complete;
                this.totalItems = response.total_items;
                this.processedItems += response.processed_items;

                this.updateProgressBar();

                if (this.isNeedSearch) {
                    setTimeout(this.search.bind(this), 1000);

                    return;
                }

                this.endProcess();
            },

            processError: function (e) {
                console.log(e.responseText);
                this.endProcess(false);
            },

            // ----------------------------------------

            startWaiter: function () {
                if (this.isWaiterActive) {
                    return;
                }

                $("body").trigger('processStart');
                this.isWaiterActive = true;
            },

            stopWaiter: function () {
                if (!this.isWaiterActive) {
                    return;
                }

                $("body").trigger('processStop');
                this.isWaiterActive = false;
            },

            // ----------------------------------------

            updateProgressBar: function() {
                this.ProgressBar.setPercents(this.getProcessPercent(), 0);
            },

            getProcessPercent: function () {
                return (this.processedItems / this.totalItems) * 100;
            }
        };

        try {
            if (!search.isNeedSearch) {
                return;
            }

            search.startProcess(new window.ProgressBar(options.progress_bar_el_id));

            search.search();
        } catch (e) {
            console.log(e);
        }
    };
});
