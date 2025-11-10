define([
    'mage/translate',
    'jquery',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
    'underscore',
    'jquery/validate',
    'Kaufland/Common',
    'prototype'
], function ($t, jQuery, alert, modal, _) {
    window.ListingAutoAction = Class.create(Common, {

        // ---------------------------------------

        internalData: {},

        popupMode: '',
        currentkPopup: {},

        magentoCategoryIdsFromOtherGroups: {},
        magentoCategoryTreeChangeEventInProgress: false,

        // ---------------------------------------

        getController: function () {
            throw Error('Method should be overrided and return controller')
        },

        // ---------------------------------------

        initialize: function () {
            jQuery.validator.addMethod('M2ePro-validate-mode', function () {
                return $$('input[name="auto_mode"]').any(function (el) {
                    return el.checked;
                })
            }, $t('This is a required field.'));

            jQuery.validator.addMethod('M2ePro-validate-category-selection', function () {
                return categories_selected_items.length > 0
            }, $t('You must select at least 1 Category.'));

            jQuery.validator.addMethod('M2ePro-validate-category-group-title', function (value, element) {

                var unique = true;

                new Ajax.Request(Kaufland.url.get(ListingAutoActionObj.getController() + '/isCategoryGroupTitleUnique'), {
                    method: 'get',
                    asynchronous: false,
                    parameters: {
                        group_id: $('group_id').value,
                        title: $('group_title').value
                    },
                    onSuccess: function (transport) {
                        unique = transport.responseText.evalJSON()['unique'];
                    }
                });

                return unique;
            }, $t('Rule with the same Title already exists.'));
        },

        clear: function () {
            this.internalData = {};
            this.magentoCategoryTreeChangeEventInProgress = false;
        },

        // ---------------------------------------

        setPopupMode: function (mode) {
            if (mode == Kaufland.php.constant('M2E_Kaufland_Model_Listing::AUTO_MODE_GLOBAL')) {
                this.popupMode = 'global';
            } else if (mode == Kaufland.php.constant('M2E_Kaufland_Model_Listing::AUTO_MODE_WEBSITE')) {
                this.popupMode = 'website';
            } else if (mode == Kaufland.php.constant('M2E_Kaufland_Model_Listing::AUTO_MODE_CATEGORY')) {
                this.popupMode = 'category';
            } else {
                this.popupMode = '';
            }

            return this;
        },

        getPopupMode: function () {
            return this.popupMode != '' ? this.popupMode + '_' : '';
        },

        // ---------------------------------------

        loadAutoActionHtml: function (mode) {
            mode = mode || null;

            new Ajax.Request(Kaufland.url.get(ListingAutoActionObj.getController() + '/index'), {
                method: 'get',
                asynchronous: true,
                parameters: {
                    auto_mode: mode || null
                },
                onSuccess: function (transport) {

                    var responseData = JSON.parse(transport.responseText);
                    var title = $t('Auto Add/Remove Rules');

                    this.clear();
                    if (mode) {
                        this.setPopupMode(mode);
                    } else {
                        this.setPopupMode(responseData.mode);
                    }
                    this.openPopUp(title, responseData.html);
                }.bind(this)
            });
        },

        // ---------------------------------------

        openPopUp: function (title, content) {
            var popupMode = this.getPopupMode(),
                    popupData = {};

            if (popupMode.indexOf('global') != -1) {
                popupData = [
                    {
                        label: $t('Continue'),
                        class: 'next continue_button primary forward',
                        attr: {style: 'display: none', id: popupMode + 'continue_button'},
                        callback: ListingAutoActionObj.globalStepTwo
                    },
                    {
                        label: $t('Reset Auto Rules'),
                        attr: {style: 'display: none', id: popupMode + 'reset_button'},
                        callback: function () {
                            ListingAutoActionObj.reset(false, function () {
                                ListingAutoActionObj.global_popup.modal('closeModal');
                            });
                        }
                    },
                    {
                        label: $t('Complete'),
                        class: 'confirm_button primary',
                        attr: {id: popupMode + 'confirm_button'},
                        callback: function () {
                            ListingAutoActionObj.confirm();
                        }
                    }
                ];
            } else if (popupMode.indexOf('website') != -1) {
                popupData = [
                    {
                        label: $t('Continue'),
                        class: 'next continue_button primary forward',
                        attr: {style: 'display: none', id: popupMode + 'continue_button'},
                        callback: ListingAutoActionObj.websiteStepTwo
                    },
                    {
                        label: $t('Reset Auto Rules'),
                        attr: {style: 'display: none', id: popupMode + 'reset_button'},
                        callback: function () {
                            ListingAutoActionObj.reset(false, function () {
                                ListingAutoActionObj.website_popup.modal('closeModal');
                            });
                        }
                    },
                    {
                        label: $t('Complete'),
                        class: 'confirm_button primary',
                        attr: {id: popupMode + 'confirm_button'},
                        callback: function () {
                            ListingAutoActionObj.confirm();
                        }
                    }
                ];
            } else if (popupMode.indexOf('category') != -1) {
                popupData = [
                    {
                        label: $t('Close'),
                        class: 'next close_button',
                        attr: {style: 'display:none', id: popupMode + 'close_button'},
                        closeModal: true
                    },
                    {
                        label: $t('Back'),
                        attr: {id: popupMode + 'cancel_button'},
                        class: 'back',
                        closeModal: true
                    },
                    {
                        label: $t('Reset Auto Rules'),
                        attr: {style: 'display: none', id: popupMode + 'reset_button'},
                        callback: function () {
                            ListingAutoActionObj.reset(false, function () {
                                ListingAutoActionObj.category_popup.modal('closeModal');
                            });
                        }
                    },
                    {
                        label: $t('Add New Rule'),
                        class: 'add_button add primary',
                        attr: {id: 'add_button'},
                        callback: function () {
                            ListingAutoActionObj.categoryStepOne();
                        }
                    }
                ];
            } else {
                popupData = [
                    {
                        label: $t('Cancel'),
                        closeModal: true
                    },
                    {
                        label: $t('Continue'),
                        class: 'next continue_button primary forward',
                        attr: {id: 'continue_button'},
                        callback: function () {
                            var contentWrapper = jQuery('#block-content-wrapper');
                            contentWrapper.wrap('<form></form>');

                            if (!contentWrapper.parent().validation().valid()) {
                                return;
                            }
                            contentWrapper.unwrap();

                            ListingAutoActionObj.loadAutoActionHtml(
                                    $$('input[name="auto_mode"]:checked')[0].value
                            );

                            this.closeModal();
                        }
                    }
                ];
            }

            this._createPopup(popupMode, title, content, popupData);
        },

        _createPopup: function (mode, title, content, popupData) {
            var self = this,
                    modalDialogMessage = $(mode + 'modal_auto_action'),
                    buttonsConfig = [];

            if (!modalDialogMessage) {
                modalDialogMessage = new Element('div', {
                    id: mode + 'modal_auto_action'
                });
            }

            modalDialogMessage.innerHTML = '';

            if (typeof self[mode + 'popup'] == 'undefined') {
                _.each(popupData, function (buttonConfig) {
                    var tmpConfig = {
                        text: buttonConfig.label || '',
                        class: buttonConfig.class || '',
                        attr: buttonConfig.attr || {},
                        click: function () {
                            buttonConfig.callback && buttonConfig.callback.call(this);
                            buttonConfig.closeModal && self[mode + 'popup'].modal('closeModal');
                        }
                    };

                    buttonsConfig.push(tmpConfig);
                });

                self[mode + 'popup'] = jQuery(modalDialogMessage).modal({
                    title: title + ' <span id="additional_autoaction_title" style="font-size: inherit;"></span>',
                    type: 'slide',
                    buttons: buttonsConfig,
                    closed: function () {
                        ListingAutoActionObj.clear();

                        var node = this.up('.modal-slide').previousSibling.previousSibling;
                        if (node.nodeType == node.COMMENT_NODE) {
                            node.remove();
                        }

                        this.up('.modal-slide').remove();
                        delete self[mode + 'popup'];

                        return true;
                    }
                });
            }

            self[mode + 'popup'].modal('openModal');
            self.currentPopup = self[mode + 'popup'];

            modalDialogMessage.innerHTML = content;
            modalDialogMessage.innerHTML.evalScripts();

            var additionalTitleEl = $('additional_autoaction_title_text');

            $('additional_autoaction_title').innerHTML = additionalTitleEl ? '(' + additionalTitleEl.innerHTML + ')' : '';
        },

        // ---------------------------------------

        addingModeChange: function () {
            $('continue_button').hide();
            $('confirm_button').show();

            if (this.value != Kaufland.php.constant('M2E_Kaufland_Model_Listing::ADDING_MODE_NONE')) {
                $$('[id$="adding_add_not_visible_field"]')[0].show();
            } else {
                $$('[id$="adding_add_not_visible"]')[0].value = Kaufland.php.constant('M2E_Kaufland_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES');
                $$('[id$="adding_add_not_visible_field"]')[0].hide();
            }
        },

        // ---------------------------------------

        loadAutoCategoryForm: function (groupId, callback) {
            var popupMode = this.getPopupMode();

            new Ajax.Request(Kaufland.url.get(ListingAutoActionObj.getController() + '/getAutoCategoryFormHtml'), {
                method: 'get',
                asynchronous: true,
                parameters: {
                    group_id: groupId || null
                },
                onSuccess: function (transport) {

                    this._createPopup(
                            'category_child_',
                            $t('Add/Edit Categories Rule'),
                            transport.responseText,
                            [{
                                label: $t('Back'),
                                attr: {id: popupMode + 'cancel_button'},
                                class: 'back',
                                callback: function () {
                                    listingAutoActionModeCategoryGroupGridJsObject.doFilter();
                                },
                                closeModal: true
                            },
                                {
                                    label: $t('Continue'),
                                    class: 'next continue_button primary forward',
                                    attr: {style: 'display: none', id: popupMode + 'continue_button'},
                                    callback: function () {
                                        ListingAutoActionObj.categoryStepTwo();
                                    },
                                },
                                {
                                    label: $t('Complete'),
                                    class: 'confirm_button primary',
                                    attr: {id: popupMode + 'confirm_button'},
                                    callback: function () {
                                        ListingAutoActionObj.confirm();
                                        $(popupMode + 'close_button').hide();
                                        $(popupMode + 'cancel_button').hide();
                                        $(popupMode + 'reset_button').show();
                                    }
                                }]
                    );

                    this.magentoCategoryTreeChangeEventInProgress = false;

                    if (typeof callback == 'function') {
                        callback();
                    }
                }.bind(this)
            });
        },

        magentoCategorySelectCallback: function (selectedCategories) {
            if (this.magentoCategoryTreeChangeEventInProgress) {
                return;
            }

            this.magentoCategoryTreeChangeEventInProgress = true;

            var latestCategory = selectedCategories[selectedCategories.length - 1];

            if (!latestCategory || typeof this.magentoCategoryIdsFromOtherGroups[latestCategory] == 'undefined') {
                this.magentoCategoryTreeChangeEventInProgress = false;
                return;
            }

            var template = $('dialog_confirm_container');

            if (!template) {
                template = new Element('div', {
                    id: 'dialog_confirm_container'
                });
            }

            template.innerHTML = $('dialog_confirm_content').innerHTML
                    .replace('%s', this.magentoCategoryIdsFromOtherGroups[latestCategory].title);

            jQuery(template).confirm({
                title: $t('Remove Category'),
                actions: {
                    confirm: function () {
                        new Ajax.Request(Kaufland.url.get(ListingAutoActionObj.getController() + '/deleteCategory'), {
                            method: 'post',
                            asynchronous: true,
                            parameters: {
                                group_id: ListingAutoActionObj.magentoCategoryIdsFromOtherGroups[latestCategory].id,
                                category_id: latestCategory
                            },
                            onSuccess: function (transport) {
                                delete ListingAutoActionObj.magentoCategoryIdsFromOtherGroups[latestCategory];
                            }
                        });

                        return true;
                    },
                    cancel: function () {``
                        tree.uncheck_node(latestCategory);
                    }
                },
                buttons: [{
                    text: $t('Cancel'),
                    class: 'action-secondary action-dismiss',
                    click: function (event) {
                        this.closeModal(event);
                    }
                }, {
                    text: $t('Confirm'),
                    class: 'action-primary action-accept',
                    click: function (event) {
                        this.closeModal(event, true);
                    }
                }],
                closed: function () {
                    ListingAutoActionObj.magentoCategoryTreeChangeEventInProgress = false;
                }
            });
        },

        // ---------------------------------------

        isCategoryAlreadyUsed: function (categoryId) {
            return this.magentoCategoryUsedIds.indexOf(categoryId) != -1;
        },

        categoryCancel: function () {
            ListingAutoActionObj.loadAutoActionHtml(
                    Kaufland.php.constant('M2E_Kaufland_Model_Listing::AUTO_MODE_CATEGORY')
            );
        },

        categoryStepOne: function (groupId) {
            var mode = ListingAutoActionObj.getPopupMode();
            this.loadAutoCategoryForm(groupId, function () {
                $(mode + 'reset_button').hide();
                $(mode + 'close_button').hide();
                $(mode + 'cancel_button').show();
            });
        },

        // ---------------------------------------

        categoryDeleteGroup: function (groupId) {
            this.confirmPopup($t('Are you sure?'), {
                confirmCallback: function () {
                    new Ajax.Request(Kaufland.url.get(ListingAutoActionObj.getController() + '/deleteCategoryGroup'), {
                        method: 'post',
                        asynchronous: true,
                        parameters: {
                            group_id: groupId
                        },
                        onSuccess: function (transport) {
                            listingAutoActionModeCategoryGroupGridJsObject.doFilter();
                        }
                    });
                }
            });
        },

        // ---------------------------------------

        validate: function () {
            if ($('auto_mode')) {
                var autoMode = $('auto_mode').value;
                if (
                        autoMode == Kaufland.php.constant('M2E_Kaufland_Model_Listing::AUTO_MODE_WEBSITE')
                        && $('auto_website_adding_mode').value
                        == Kaufland.php.constant('M2E_Kaufland_Model_Listing::ADDING_MODE_NONE')
                        && $('auto_website_deleting_mode').value
                        == Kaufland.php.constant('M2E_Kaufland_Model_Listing::DELETING_MODE_NONE')
                ) {
                    this.alertSelectAvailableOptions();

                    return false;
                }

                if (
                        autoMode == Kaufland.php.constant('M2E_Kaufland_Model_Listing::AUTO_MODE_CATEGORY')
                        && $('adding_mode').value == Kaufland.php.constant('M2E_Kaufland_Model_Listing::ADDING_MODE_NONE')
                        && $('deleting_mode').value == Kaufland.php.constant('M2E_Kaufland_Model_Listing::DELETING_MODE_NONE')
                ) {
                    this.alertSelectAvailableOptions();

                    return false;
                }
            }

            var validationResult = true;

            this.currentPopup.find('form').each(function () {
                validationResult = validationResult && jQuery(this).validation().valid();
            });

            return validationResult;
        },

        alertSelectAvailableOptions: function () {
            alert({
                title: $t('Rule not created'),
                content: $t('Please select at least one action from the available options'),
            });
        },

        confirm: function () {
            if (!ListingAutoActionObj.validate()) {
                return;
            }

            ListingAutoActionObj.collectData();

            var callback;
            if (ListingAutoActionObj.internalData.auto_mode == Kaufland.php.constant('M2E_Kaufland_Model_Listing::AUTO_MODE_CATEGORY')) {
                callback = function () {
                    this.category_child_popup.modal('closeModal');

                    listingAutoActionModeCategoryGroupGridJsObject.doFilter();
                }.bind(this);
            } else {
                callback = function () {
                    this.currentPopup.modal('closeModal');
                }.bind(this);
            }

            ListingAutoActionObj.submitData(callback);
        },

        collectData: function () {
            if ($('auto_mode')) {
                switch (parseInt($('auto_mode').value)) {
                    case Kaufland.php.constant('M2E_Kaufland_Model_Listing::AUTO_MODE_GLOBAL'):
                        ListingAutoActionObj.internalData = {
                            auto_mode: $('auto_mode').value,
                            auto_global_adding_mode: $('auto_global_adding_mode').value,
                            auto_global_adding_add_not_visible: $('auto_global_adding_add_not_visible').value
                        };
                        break;

                    case Kaufland.php.constant('M2E_Kaufland_Model_Listing::AUTO_MODE_WEBSITE'):
                        ListingAutoActionObj.internalData = {
                            auto_mode: $('auto_mode').value,
                            auto_website_adding_mode: $('auto_website_adding_mode').value,
                            auto_website_adding_add_not_visible: $('auto_website_adding_add_not_visible').value,
                            auto_website_deleting_mode: $('auto_website_deleting_mode').value
                        };
                        break;

                    case Kaufland.php.constant('M2E_Kaufland_Model_Listing::AUTO_MODE_CATEGORY'):
                        ListingAutoActionObj.internalData = {
                            id: $('group_id').value,
                            title: $('group_title').value,
                            auto_mode: $('auto_mode').value,
                            adding_mode: $('adding_mode').value,
                            adding_add_not_visible: $('adding_add_not_visible').value,
                            deleting_mode: $('deleting_mode').value,
                            categories: categories_selected_items
                        };
                        break;
                }
            }
        },

        submitData: function (callback) {
            var data = this.internalData;

            new Ajax.Request(Kaufland.url.get(ListingAutoActionObj.getController() + '/save'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    auto_action_data: Object.toJSON(data)
                },
                onSuccess: function (transport) {
                    if (typeof callback == 'function') {
                        callback();
                    }
                }
            });
        },

        reset: function (skipConfirmation, callback, closeCallback) {
            skipConfirmation = skipConfirmation || false;

            var confirmCallback = function () {
                new Ajax.Request(Kaufland.url.get(ListingAutoActionObj.getController() + '/reset'), {
                    method: 'post',
                    asynchronous: true,
                    parameters: {},
                    onSuccess: function (transport) {
                        callback && callback();
                        ListingAutoActionObj.loadAutoActionHtml();
                    }
                });
            };

            if (skipConfirmation) {
                confirmCallback();
                return
            }

            ListingAutoActionObj.confirmPopup($t('Are you sure?'), {
                confirmCallback: confirmCallback,
                closeCallback: closeCallback
            });
        },

        // ---------------------------------------

        confirmPopup: function (message, configData) {
            jQuery('<div>').confirm({
                title: message,
                actions: {
                    always: function () {
                        configData.closeCallback && configData.closeCallback();
                    },
                    confirm: function () {
                        configData.confirmCallback && configData.confirmCallback();
                        return true;
                    },
                    buttons: [
                        {text: $t('Cancel')},
                        {text: $t('Confirm'), class: 'primary'}
                    ]
                }
            });
        }

        // ---------------------------------------
    });

});
