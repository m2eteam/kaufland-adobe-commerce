define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'M2ECore/Plugin/Messages',
    'mage/translate',
    'Kaufland/Grid'
], function (jQuery, modal, MessageObj, $t) {

    window.ListingWizardCategoryModeManuallyGrid = Class.create(Grid, {

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

        extractIdFromUrl: function () {
            var urlParts = window.location.href.split('/');
            var idIndex = urlParts.indexOf('id');
            if (idIndex !== -1 && idIndex < urlParts.length - 1) {
                return parseInt(urlParts[idIndex + 1]);
            }
        },

        // ---------------------------------------

        editCategories: function () {
            var self = this;

            this.selectedMagentoCategoryIds = this.getSelectedProductsString();
            let wizard_id = self.extractIdFromUrl();

            new Ajax.Request(Kaufland.url.get('listing_wizard_category/chooserBlockModeManually'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    products_ids: this.selectedMagentoCategoryIds,
                    id: wizard_id
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
            var self = this;
            let wizard_id = self.extractIdFromUrl();

            this.selectedProductsIds = id ? [id] : this.getSelectedProductsArray();

            new Ajax.Request(Kaufland.url.get('listing_wizard_category/resetModeManually'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    products_ids: this.selectedProductsIds.join(','),
                    id: wizard_id
                },
                onSuccess: function (transport) {
                    this.getGridObj().doFilter();
                    this.unselectAll();
                }.bind(this)
            });
        },

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
            var self = this;
            let wizard_id = self.extractIdFromUrl();

            new Ajax.Request(Kaufland.url.get('listing_wizard_category/saveModeManually'), {
                method: 'post',
                parameters: {
                    products_ids: this.getSelectedProductsString(),
                    template_data: Object.toJSON(templateData),
                    id: wizard_id
                },
                onSuccess: function (transport) {

                    jQuery('#modal_view_action_dialog').modal('closeModal');
                    this.unselectAllAndReload();
                }.bind(this)
            });
        },

        completeCategoriesDataStep: function (validateCategory, validateSpecifics) {
            MessageObj.clear();
            var self = this;
            let wizard_id = self.extractIdFromUrl();

            new Ajax.Request(Kaufland.url.get('listing_wizard_category/validateModeManually'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    validate_category: validateCategory,
                    validate_specifics: validateSpecifics,
                    id: wizard_id
                },
                onSuccess: function (transport) {

                    var response = transport.responseText.evalJSON();

                    if (response['validation']) {
                        return setLocation(
                            Kaufland.url.get('listing_wizard_category/assignModeManually', {'id': wizard_id})
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

        validateCategory: function (selectedCategory, containerId) {
            if (Object.keys(selectedCategory).length === 0) {
                if(containerId) {
                    MessageObj.setContainer(containerId)
                }
                MessageObj.clearAll();
                MessageObj.addError($t('Please choose a category to continue.'));

                return false;
            }

            if (!selectedCategory.is_all_required_attributes_filled) {
                if(containerId) {
                    MessageObj.setContainer(containerId)
                }
                MessageObj.clearAll();
                MessageObj.addError($t('Please complete all required attributes to proceed.'));

                return false;
            }

            return true;
        }

        // ---------------------------------------
    });

});
