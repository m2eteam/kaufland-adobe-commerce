define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'M2ECore/Plugin/Messages',
    'mage/translate',
    'Kaufland/Common'
], function (jQuery, modal, MessagesObj, $t) {
    window.License = Class.create(Common, {

        urlSettings: {
            change: '',
            refresh: '',
            licenseSection: ''
        },

        initialize: function (urlSettings) {
            this.urlSettings.change = urlSettings.change;
            this.urlSettings.refresh = urlSettings.refresh;
            this.urlSettings.licenseSection = urlSettings.licenseSection;
        },

        changeLicenseKeyPopup: function () {
            const self = this;

            new Ajax.Request(this.urlSettings.change, {
                method: 'get',
                asynchronous: true,
                onSuccess: function (transport) {

                    const content = transport.responseText;
                    const title = $t('Use Existing License');

                    self.openPopup(title, content, self.confirmLicenseKey.bind(self));
                }
            });
        },

        // ---------------------------------------

        confirmLicenseKey: function () {
            const self = this;

            if (!this.isValidForm()) {
                return false;
            }

            const formData = $('edit_form').serialize(true);

            new Ajax.Request(this.urlSettings.change, {
                method: 'post',
                asynchronous: true,
                parameters: formData,
                onSuccess: self.processOnSuccess
            });

            return true;
        },

        processOnSuccess: function (transport) {
            const self = window.LicenseObj;
            let result = transport.responseText;
            if (!result.isJSON()) {
                MessagesObj.addError(result);
            }

            result = JSON.parse(result);
            MessagesObj.clear();

            if (result.success) {
                MessagesObj.addSuccess(result.message);
            } else {
                MessagesObj.addError(result.message);
            }
            self.reloadLicenseTab();
        },

        // ---------------------------------------

        openPopup: function (title, content, confirmCallback, type) {
            type = type || 'popup';
            const modalPopup = $('modal_popup');
            if (modalPopup) {
                modalPopup.remove();
            }

            const modalDialogMessage = new Element('div', {
                id: 'modal_popup'
            });

            const popup = jQuery(modalDialogMessage).modal({
                title: title,
                modalClass: type === 'popup' ? 'width-500' : '',
                type: type,
                buttons: [{
                    text: $t('Cancel'),
                    class: type === 'popup' ? 'action-secondary action-dismiss' : 'action-default action-dismiss',
                    click: function () {
                        this.closeModal();
                    }
                }, {
                    text: $t('Confirm'),
                    class: 'action-primary action-accept',
                    id: 'save_popup_button',
                    click: function () {

                        if (confirmCallback) {
                            var result = confirmCallback();
                            result && this.closeModal();
                        } else {
                            this.closeModal();
                        }
                    }
                }],
                closed: function () {
                    modalDialogMessage.innerHTML = '';

                    return true;
                }
            });
            popup.modal('openModal');

            modalDialogMessage.insert(content);
            modalDialogMessage.innerHTML.evalScripts();

            this.initFormValidation(popup.find('form'));

            return popup;
        },

        // ---------------------------------------

        refreshStatus: function () {
            const self = this;
            new Ajax.Request(this.urlSettings.refresh, {
                method: 'post',
                asynchronous: true,
                onSuccess: self.processOnSuccess
            });
        },

        // ---------------------------------------

        reloadLicenseTab: function () {
            BlockNoticeObj.removeInitializedBlock('block_notice_configuration_license');

            new Ajax.Request(this.urlSettings.licenseSection, {
                method: 'get',
                asynchronous: true,
                onSuccess: function (transport) {
                    var container = $$('#container > div.admin__scope-old')[0];
                    container.innerHTML = transport.responseText;
                    container.innerHTML.evalScripts();
                    CommonObj.scrollPageToTop();
                }
            });
        }
    });
});
