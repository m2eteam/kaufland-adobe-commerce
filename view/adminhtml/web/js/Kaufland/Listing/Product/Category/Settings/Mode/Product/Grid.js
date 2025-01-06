define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'M2ECore/Plugin/Messages',
    'Kaufland/Grid'
], function (jQuery, modal, MessageObj) {

    window.KauflandListingProductCategorySettingsModeProductGrid = Class.create(Grid, {

        // ---------------------------------------

        productIdCellIndex: 1,
        productTitleCellIndex: 2,

        // ---------------------------------------

        prepareActions: function () {

            this.actions = {
                editCategoriesAction: function (id) {
                    id && this.selectByRowId(id);
                    this.editCategories();
                }.bind(this),

                resetCategoriesAction: function (id) {
                    this.resetCategories(id);
                }.bind(this),

                removeItemAction: function (id) {
                    var ids = id ? [id] : this.getSelectedProductsArray();
                    this.removeItems(ids);
                }.bind(this)
            };
        },

        // ---------------------------------------

        editCategories: function () {
            this.selectedMagentoCategoryIds = this.getSelectedProductsString();

            new Ajax.Request(Kaufland.url.get('kaufland_listing_product_category_settings/getChooserBlockHtml'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    products_ids: this.selectedMagentoCategoryIds,
                },
                onSuccess: function (transport) {
                    this.openPopUp(
                            Kaufland.translator.translate('Category Settings'),
                            transport.responseText
                    );
                }.bind(this)
            });
        },

        resetCategories: function (id) {
            if (id && !confirm('Are you sure?')) {
                return;
            }

            this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

            new Ajax.Request(Kaufland.url.get('kaufland_listing_product_category_settings/stepTwoReset'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    products_ids: this.selectedProductsIds.join(',')
                },
                onSuccess: function (transport) {
                    this.getGridObj().doFilter();
                    this.unselectAll();
                }.bind(this)
            });
        },

        // ---------------------------------------

        removeItems: function (id) {
            var self = this,
                confirmAction;

            confirmAction = function() {
                self.selectedProductsIds = id ? [id] : self.getSelectedProductsArray();

                var url = M2ePro.url.get('ebay_listing_product_category_settings/stepTwoDeleteProductsModeProduct');
                new Ajax.Request(url, {
                    method: 'post',
                    parameters: {
                        products_ids: self.selectedProductsIds.join(',')
                    },
                    onSuccess: function () {
                        self.unselectAllAndReload();
                    }
                });
            };

            if (id) {
                self.confirm({
                    actions: {
                        confirm: function () {
                            confirmAction();
                        },
                        cancel: function () {
                            return false;
                        }
                    }
                });
            } else {
                confirmAction();
            }
        },

        // ---------------------------------------

        // confirm: function ($super, config) {
        //     var action = '';
        //
        //     $$('select#' + this.gridId + '_massaction-select option').each(function (o) {
        //         if (o.selected && o.value != '') {
        //             action = o.value;
        //         }
        //     });
        //
        //     $super(config);
        // },

        validateCategories: function (isAlLeasOneCategorySelected, showErrorMessage) {
            MessageObj.setContainer('#anchor-content');
            MessageObj.clear();
            var button = $('listing_category_continue_btn');
            if (parseInt(isAlLeasOneCategorySelected)) {
                button.addClassName('disabled');
                button.disable();
                if (parseInt(showErrorMessage)) {
                    MessageObj.addWarning(Kaufland.translator.translate('select_relevant_category'));
                }
            } else {
                button.removeClassName('disabled');
                button.enable();
                MessageObj.clear();
            }
        },

        openPopUp: function (title, content) {
            const self = this;
            let popupId = 'modal_view_action_dialog';

            let modalDialogMessage = $(popupId);

            if (!modalDialogMessage) {
                modalDialogMessage = new Element('form', {
                    id: popupId
                });
            }

            modalDialogMessage.innerHTML = '';

            this.popUp = jQuery(modalDialogMessage).modal(Object.extend({
                title: title,
                type: 'slide',
                buttons: [{
                    text: Kaufland.translator.translate('Cancel'),
                    attr: {id: 'cancel_button'},
                    class: 'action-dismiss',
                    click: function (event) {
                        self.unselectAllAndReload();
                        this.closeModal(event);
                        $(popupId).remove()
                    }
                }, {
                    text: Kaufland.translator.translate('Save'),
                    attr: {id: 'done_button'},
                    class: 'action-primary action-accept',
                    click: function (event) {
                        self.confirmCategoriesData();
                    }
                }]
            }));

            this.popUp.modal('openModal');

            try {
                modalDialogMessage.innerHTML = content;
                modalDialogMessage.innerHTML.evalScripts();
            } catch (ignored) {
            }
        },

        confirmCategoriesData: function () {
            this.initFormValidation('#modal_view_action_dialog');

            if (!jQuery('#modal_view_action_dialog').valid()) {
                return;
            }

            let selectedCategory = KauflandCategoryChooserObj.selectedCategory;

            this.saveCategoriesData(selectedCategory);
        },

        saveCategoriesData: function (templateData) {
            new Ajax.Request(Kaufland.url.get('kaufland_listing_product_category_settings/stepTwoSaveToSession'), {
                method: 'post',
                parameters: {
                    products_ids: this.getSelectedProductsString(),
                    template_data: Object.toJSON(templateData)
                },
                onSuccess: function (transport) {

                    jQuery('#modal_view_action_dialog').modal('closeModal');
                    this.unselectAllAndReload();
                }.bind(this)
            });
        },

        completeCategoriesDataStep: function (validateCategory, validateSpecifics) {
            MessageObj.clear();

            new Ajax.Request(Kaufland.url.get('kaufland_listing_product_category_settings/stepTwoModeValidate'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    validate_category: validateCategory,
                    validate_specifics: validateSpecifics
                },
                onSuccess: function (transport) {

                    var response = transport.responseText.evalJSON();

                    if (response['validation']) {
                        return setLocation(
                                Kaufland.url.get(
                                        'kaufland_listing_product_category_settings/step_assign_category'
                                )
                        );
                    }

                    if (response['message']) {
                        return MessageObj.addError(response['message']);
                    }

                    $('next_step_warning_popup_content').select('span.total_count').each(function (el) {
                        $(el).update(response['total_count']);
                    });

                    $('next_step_warning_popup_content').select('span.failed_count').each(function (el) {
                        $(el).update(response['failed_count']);
                    });

                    var popup = jQuery('#next_step_warning_popup_content');

                    modal({
                        title: Kaufland.translator.translate('Set Kaufland Category'),
                        type: 'popup',
                        buttons: [{
                            text: Kaufland.translator.translate('Cancel'),
                            class: 'action-secondary action-dismiss',
                            click: function () {
                                this.closeModal();
                            }
                        }, {
                            text: Kaufland.translator.translate('Continue'),
                            class: 'action-primary action-accept forward',
                            id: 'save_popup_button',
                            click: function () {
                                this.closeModal();
                                setLocation(Kaufland.url.get('kaufland_listing_product_category_settings/step_3'));
                            }
                        }]
                    }, popup);

                    popup.modal('openModal');

                }.bind(this)
            });
        },

        // ---------------------------------------
    });

});
