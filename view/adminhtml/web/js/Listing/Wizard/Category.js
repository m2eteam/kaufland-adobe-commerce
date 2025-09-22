define([
    'M2ECore/Plugin/Messages',
    'mage/translate',
    'Kaufland/Common',
], function (MessagesObj, $t) {

    window.KauflandListingCategory = Class.create(Common, {

        // ---------------------------------------

        gridObj: null,
        selectedProductsIds: [],

        // ---------------------------------------

        initialize: function (gridObj) {
            this.gridObj = gridObj;
        },

        // ---------------------------------------

        getChooserSelectedCategory: function () {
            return KauflandCategoryChooserObj.selectedCategory
        },

        getChooserSelectedAttributes: function () {
            return KauflandCategoryChooserObj.selectedSpecifics
        },

        editCategorySettings: function (id) {
            this.selectedProductsIds = id ? [id] : this.gridObj.getSelectedProductsArray();

            const url = Kaufland.url.get('listing_product_category_settings/edit');
            new Ajax.Request(url, {
                method: 'post',
                asynchronous: true,
                parameters: {
                    products_ids: this.selectedProductsIds.join(','),
                    storefront_id: this.gridObj.storefrontId
                },
                onSuccess: function (transport) {
                    this.openPopUp($t('Category Settings'), transport.responseText);
                }.bind(this)
            });
        },

        saveCategorySettings: function () {
            this.initFormValidation('#modal_view_action_dialog');

            if (!jQuery('#modal_view_action_dialog').valid()) {
                return;
            }

            const self = this;
            const selectedCategory = this.getChooserSelectedCategory();

            if (!this.validateCategory(selectedCategory, '#error_message_kaufland')) {
                return;
            }

            selectedCategory.specific = this.getChooserSelectedAttributes();

            new Ajax.Request(Kaufland.url.get('kaufland_listing/saveCategoryTemplate'), {
                method: 'post',
                asynchronous: true,
                parameters: {
                    products_ids: self.selectedProductsIds.join(','),
                    storefront_id: self.gridObj.storefrontId,
                    template_category_id: selectedCategory.dictionary_id,
                },
                onSuccess: function (transport) {
                    window.KauflandCategoryAttributeValidationPopup.closePopupCallback = function () {
                        self.cancelCategorySettings()
                    }
                    window.KauflandCategoryAttributeValidationPopup.open(selectedCategory.dictionary_id);
                }.bind(this)
            });
        },

        // ---------------------------------------


        cancelCategorySettings: function () {
            jQuery('#modal_view_action_dialog').modal('closeModal');
        },

        // ---------------------------------------

        openPopUp: function (title, content, params, popupId) {
            const self = this;
            params = params || {};
            popupId = popupId || 'modal_view_action_dialog';

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
                    text: $t('Cancel'),
                    attr: {id: 'cancel_button'},
                    class: 'action-dismiss',
                    click: function (event) {
                        this.closeModal(event);
                    }
                }, {
                    text: $t('Save'),
                    attr: {id: 'done_button'},
                    class: 'action-primary action-accept',
                    click: function () {
                        self.saveCategorySettings();
                    }
                }],
                closed: function () {
                    self.selectedProductsIds = [];
                    self.selectedCategoriesData = {};

                    self.gridObj.unselectAllAndReload();

                    return true;
                }
            }, params));

            this.popUp.modal('openModal');

            try {
                modalDialogMessage.innerHTML = content;
                modalDialogMessage.innerHTML.evalScripts();
            } catch (ignored) {
            }
        },

        //----------------------------------------

        modeSameSubmitData: function (url) {
            let selectedCategory = this.getChooserSelectedCategory();

            if (!this.validateCategory(selectedCategory)){
                return;
            }

            if (typeof selectedCategory !== 'undefined') {
                selectedCategory['specific'] = this.getChooserSelectedAttributes();
            }

            this.postForm(url, {category_data: Object.toJSON(selectedCategory)});
        },

        validateCategory: function (selectedCategory, containerId) {
            if (Object.keys(selectedCategory).length === 0) {
                if(containerId) {
                    MessagesObj.setContainer(containerId)
                }
                MessagesObj.clearAll();
                MessagesObj.addError($t('Please choose a category to continue.'));

                return false;
            }

            if (!selectedCategory.is_all_required_attributes_filled) {
                if(containerId) {
                    MessagesObj.setContainer(containerId)
                }
                MessagesObj.clearAll();
                MessagesObj.addError($t('Please complete all required attributes to proceed.'));

                return false;
            }

            return true;
        }
    });
});
