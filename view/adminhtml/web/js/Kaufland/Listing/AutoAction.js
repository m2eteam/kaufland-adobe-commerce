define([
    'jquery',
    'Kaufland/Kaufland/Listing/AutoAction/DescriptionPolicyValidator',
    'Kaufland/Listing/AutoAction',
    'prototype'
], function (jQuery, DescriptionPolicyValidator) {
    window.KauflandListingAutoAction = Class.create(ListingAutoAction, {

        // ---------------------------------------

        getController: function () {
            return 'kaufland_listing_autoAction';
        },

        // ---------------------------------------

        loadAutoActionHtml: function ($super, mode) {
            if (mode) {
                return $super(mode)
            }

            const config = {
                validateUrl: Kaufland.url.get(this.getController() + '/validateListingDescription'),
                saveDescriptionTemplateUrl: Kaufland.url.get(this.getController() + '/saveDescriptionTemplate'),
                successCallback: () => $super(mode),
            };

            new DescriptionPolicyValidator(config).validate();
        },

        addingModeChange: function () {
            var mode = ListingAutoActionObj.getPopupMode();
            if (this.value == Kaufland.php.constant('M2E_Kaufland_Model_Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY')) {
                $(mode + 'confirm_button').hide();
                $(mode + 'continue_button').show();
            } else {
                $(mode + 'continue_button').hide();
                $(mode + 'confirm_button').show();
            }

            if (this.value != Kaufland.php.constant('M2E_Kaufland_Model_Listing::ADDING_MODE_NONE')) {
                $$('[id$="adding_add_not_visible_field"]')[0].show();
            } else {
                $$('[id$="adding_add_not_visible"]')[0].value = Kaufland.php.constant('M2E_Kaufland_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES');
                $$('[id$="adding_add_not_visible_field"]')[0].hide();
            }
        },

        categoryAddingMode: function () {
            var popupMode = ListingAutoActionObj.getPopupMode();
            if (this.value == Kaufland.php.constant('M2E_Kaufland_Model_Listing::ADDING_MODE_ADD_AND_ASSIGN_CATEGORY')) {
                $(popupMode + 'confirm_button').hide();
                $(popupMode + 'continue_button').show();
            } else {
                $(popupMode + 'continue_button').hide();
                $(popupMode + 'confirm_button').show();
            }

            if (this.value != Kaufland.php.constant('M2E_Kaufland_Model_Listing::ADDING_MODE_NONE')) {
                $$('[id$="adding_add_not_visible_field"]')[0].show();
            } else {
                $$('[id$="adding_add_not_visible"]')[0].value = Kaufland.php.constant('M2E_Kaufland_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES');
                $$('[id$="adding_add_not_visible_field"]')[0].hide();
            }
        },

        // ---------------------------------------

        loadCategoryChooser: function (callback) {
            const mode = $('auto_mode').value;
            new Ajax.Request(Kaufland.url.get(ListingAutoActionObj.getController() + '/getCategoryChooserHtml'), {
                method: 'get',
                asynchronous: true,
                parameters: {
                    auto_mode: mode,
                    group_id: this.internalData.id,
                    magento_category_id: typeof categories_selected_items != 'undefined' ? categories_selected_items[0] : null
                },
                onSuccess: function (transport) {
                    var dataContainer;
                    if (mode == Kaufland.php.constant('M2E_Kaufland_Model_Listing::AUTO_MODE_CATEGORY')) {
                        dataContainer = $('category_child_data_container');
                    } else {
                        dataContainer = $('data_container');
                    }

                    dataContainer.update();
                    $('kaufland_category_chooser').update(transport.responseText);

                    if (typeof callback == 'function') {
                        callback();
                    }
                    try {
                        dataContainer.innerHTML.evalScripts();
                    } catch (ignored) {
                    }
                }.bind(this)
            });
        },

        // ---------------------------------------

        globalStepTwo: function () {
            ListingAutoActionObj.collectData();

            var callback = function () {
                jQuery('#' + ListingAutoActionObj.getPopupMode() + 'modal_auto_action > .block_notices:first')
                        .remove();

                $(ListingAutoActionObj.getPopupMode() + 'confirm_button').show();
                $(ListingAutoActionObj.getPopupMode() + 'reset_button').show();
                $(ListingAutoActionObj.getPopupMode() + 'continue_button').hide();
            };

            ListingAutoActionObj.loadCategoryChooser(callback);
        },

        websiteStepTwo: function () {
            ListingAutoActionObj.collectData();

            var callback = function () {

                jQuery('#' + ListingAutoActionObj.getPopupMode() + 'modal_auto_action > .block_notices:first')
                        .remove();

                $(ListingAutoActionObj.getPopupMode() + 'confirm_button').show();
                $(ListingAutoActionObj.getPopupMode() + 'reset_button').show();
                $(ListingAutoActionObj.getPopupMode() + 'continue_button').hide();
            };

            ListingAutoActionObj.loadCategoryChooser(callback);
        },

        categoryStepTwo: function () {
            if (!ListingAutoActionObj.validate()) {
                return;
            }

            ListingAutoActionObj.collectData();

            var callback = function () {
                $(ListingAutoActionObj.getPopupMode() + 'confirm_button').show();
                $(ListingAutoActionObj.getPopupMode() + 'reset_button').show();
                $(ListingAutoActionObj.getPopupMode() + 'continue_button').hide();
            };

            ListingAutoActionObj.loadCategoryChooser(callback);
        },

        // ---------------------------------------

        collectData: function ($super) {
            $super();
            if (typeof KauflandCategoryChooserObj !== 'undefined') {
                ListingAutoActionObj.internalData.template_category_data = KauflandCategoryChooserObj.selectedCategory;
            }
        }

        // ---------------------------------------
    });

});
