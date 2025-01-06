define([
    'jquery',
    'Kaufland/Common',
    'Kaufland/General/PhpFunctions'
], function (jQuery) {

    window.MagentoProductGrid = Class.create(Common, {

        // ---------------------------------------

        initialize: function (AddListingHandlerObj) {
            this.addListingHandlerObj = AddListingHandlerObj || null;
        },

        // ---------------------------------------

        saveClick: function (back) {
            var selected = this.getSelectedProducts();
            if (selected) {
                this.addListingHandlerObj.add(selected, false, back, '');
            }
        },

        // ---------------------------------------

        save_and_list_click: function (back) {
            if (this.getSelectedProducts()) {
                this.addListingHandlerObj.add(this.getSelectedProducts(), back, 'yes');
            }
        },

        // ---------------------------------------

        setFilter: function (event) {
            if (event != undefined) {
                Event.stop(event);
            }

            var filters = $$('#' + this.containerId + ' .data-grid-filters input',
                    '#' + this.containerId + ' .data-grid-filters select');
            var elements = [];
            filters.forEach(function (el) {
                if (el.value && el.value.length)
                    elements.push(el);
            });
            if (!this.doFilterCallback || (this.doFilterCallback && this.doFilterCallback())) {
                var ruleForm = $('rule_form'),
                        ruleParams = {};

                if (ruleForm) {
                    ruleParams = ruleForm.serialize(true);
                }

                var numParams = 0;
                for (var param in ruleParams) {
                    numParams++;
                }

                this.reloadParams = this.reloadParams || {};

                for (var reloadParam in this.reloadParams) {
                    reloadParam.match('^rule|^hide') && delete this.reloadParams[reloadParam];
                }

                if (numParams > 5) {
                    this.reloadParams = Object.extend(this.reloadParams, ruleParams);
                } else {

                    if (ruleParams['hide_products_others_listings'] == 0) {
                        this.reloadParams.hide_products_others_listings = 0;
                    }

                    this.reloadParams.rule = "";
                }

                ProductGridObj.clearUrlFromFilter();

                this.reload(this.addVarToUrl(this.filterVar, base64_encode(Form.serializeElements(elements))));
            }
        },

        resetFilter: function () {
            if (!this.reloadParams) {
                this.reloadParams = Object.extend({});
            }

            for (var reloadParam in this.reloadParams) {
                reloadParam.match('^rule|^hide') && delete this.reloadParams[reloadParam];
            }
            this.reloadParams.rule = "";

            ProductGridObj.clearUrlFromFilter();

            this.reload(this.addVarToUrl(this.filterVar, ''));
        },

        advancedFilterToggle: function () {
            var $gridObj = jQuery('#' + ProductGridObj.getGridId().replace(/JsObject$/, '')),
                    $massactionEl = $gridObj.find('.admin__data-grid-header-row:last-child'),
                    $massSelectWrap = $massactionEl.find('.mass-select-wrap');

            if (jQuery('#listing_product_rules:visible').length) {

                jQuery('#listing_product_rules').hide();
                $('advanced_filter_button').removeClassName('advanced-filter-button-active');

                if (!ProductGridObj.isMassActionExists) {
                    $massactionEl.css({'width': ''});
                    $massSelectWrap.css({'margin-left': '-63.6%'});
                }

                if ($$('#advanced_filter_button span span span').length > 0) {
                    $$('#advanced_filter_button span span span')[0].innerHTML = Kaufland.translator.translate('Show Advanced Filter');
                } else {
                    $$('#advanced_filter_button span')[0].innerHTML = Kaufland.translator.translate('Show Advanced Filter');
                }
            } else {

                jQuery('#listing_product_rules').show();
                $('advanced_filter_button').addClassName('advanced-filter-button-active');

                if (!ProductGridObj.isMassActionExists) {
                    $massactionEl.css({'width': '100%'});
                    $massSelectWrap.css({'margin-left': '-1.3em'});
                }

                if ($$('#advanced_filter_button span span span').length > 0) {
                    $$('#advanced_filter_button span span span')[0].innerHTML = Kaufland.translator.translate('Hide Advanced Filter');
                } else {
                    $$('#advanced_filter_button span')[0].innerHTML = Kaufland.translator.translate('Hide Advanced Filter');
                }
            }
        },

        massactionMassSelectStyleFix: function () {
            var $gridObj = jQuery('#' + ProductGridObj.getGridId().replace(/JsObject$/, '')),
                    $massactionEl = $gridObj.find('.admin__data-grid-header-row:last-child'),
                    $massSelectWrap = $massactionEl.find('.mass-select-wrap');

            if (jQuery('#listing_product_rules:visible').length) {
                $massSelectWrap.css({'margin-left': '-1.3em'});
            } else {
                $massSelectWrap.css({'margin-left': '-63.6%'});
            }
        },

        // ---------------------------------------

        setGridId: function (id) {
            this.gridId = id;
        },

        getGridId: function () {
            return this.gridId;
        },

        // ---------------------------------------

        getSelectedProducts: function () {
            var selectedProducts = window[this.getGridId() + '_massactionJsObject'].checkedString;

            if (!selectedProducts) {
                this.alert(Kaufland.translator.translate('Please select the Products you want to perform the Action on.'));
                return false;
            }
            return selectedProducts;
        },

        clearUrlFromFilter: function () {
            var url = window.location.href;
            url = this.replaceFilter(url);

            if (url) {
                window.history.pushState("", "", url);

                for (var child of $$('.store-switcher-all')) {
                    url = child.firstElementChild.getAttribute('href');
                    url = this.replaceFilter(url);
                    if (url) {
                        child.firstElementChild.setAttribute("href", url);
                    }
                }
            }
        },

        replaceFilter: function (url) {
            var urlParts = url.split('/');
            var index = urlParts.indexOf('filter');

            if (index !== -1) {
                urlParts.splice(index, 2);
                return urlParts.join('/');
            }

            return '';
        }
    });
});
