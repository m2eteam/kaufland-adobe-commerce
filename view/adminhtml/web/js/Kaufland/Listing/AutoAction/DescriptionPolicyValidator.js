define([
    'jquery',
    'mage/translate',
    'mage/storage',
    'Kaufland/Common'
], function ($, $t, storage) {
    return Class.create(Common, {
        validateUrl: undefined,
        saveDescriptionTemplateUrl: undefined,
        modalPopup: undefined,
        successCallback: undefined,

        initialize: function (config) {
            this.validateUrl = config.validateUrl;
            this.saveDescriptionTemplateUrl = config.saveDescriptionTemplateUrl;
            this.successCallback = config.successCallback;
        },

        validate: function () {
            const popup = this.getPopup();

            this.getPopupContent().then((content) => {
                if (!content) {
                    popup.modal('closeModal');
                    this.successCallback();

                    return;
                }

                popup.html('').html(content).modal('openModal');
            });
        },

        reloadPopup: function () {
            const popup = this.getPopup();

            return this.getPopupContent().then((content) => {
                if (!content) {
                    popup.modal('closeModal');
                    this.successCallback();

                    return;
                }

                popup.html('').html(content)
            })
        },

        getPopupContent: function () {

            $('body').trigger('processStart');

            return storage.get(this.validateUrl)
                    .always(() => {
                        $('body').trigger('processStop');
                    })
                    .then((response) => {
                        let popupContent = undefined;
                        if (response.hasOwnProperty('popup_content')) {
                            popupContent = response.popup_content;
                        }

                        if (!popupContent) {
                            return null;
                        }

                        const content = $(popupContent)
                        content.on('click', '.add-button', this.addButtonEvent.bind(this));
                        content.on('click', '.edit-button', this.editButtonEvent.bind(this));
                        content.find('form').mage('validation', {});

                        return content;
                    });
        },

        getPopup: function () {

            const popupId = 'description_modal_auto_action'
            this.modalPopup = $('#' + popupId);

            if (this.modalPopup.length === 0) {
                this.modalPopup = $(`<div id="${popupId}">`)
            }

            this.modalPopup.modal({
                title: $t('Assign a Description Policy'),
                type: 'slide',
                buttons: [
                    {
                        text: $t('Cancel'),
                    },
                    {
                        text: $t('Continue'),
                        class: 'primary forward',
                        click: () => {
                            if (!this.modalPopup.find('form').valid()) {
                                return;
                            }

                            this.saveDescriptionTemplate().then(() => {
                                this.reloadPopup()
                            })
                        }
                    }
                ],
            })

            return this.modalPopup;
        },

        saveDescriptionTemplate: function () {
            let formData = this.modalPopup.find('form').serializeArray();
            formData.push({
                name: 'form_key',
                value: FORM_KEY
            });

            return storage.post(
                    this.saveDescriptionTemplateUrl,
                    formData,
                    true,
                    'application/x-www-form-urlencoded'
            );
        },

        addButtonEvent: function (event) {
            const addUrl = $(event.currentTarget).attr('data-url');
            this.openAndWatchUrlInNewTab(addUrl, () => {
                this.reloadPopup()
            });
        },

        editButtonEvent: function (event) {
            let editUrl = $(event.currentTarget).attr('data-url');
            let templateId = $('#template_description_id').val()

            editUrl += `id/${templateId}/`

            this.openAndWatchUrlInNewTab(editUrl, () => {
                this.reloadPopup()
            });
        },

        openAndWatchUrlInNewTab: function (url, callback) {
            let win = window.open(url);
            const intervalId = setInterval(function () {
                if (!win || win.closed) {
                    clearInterval(intervalId);
                    callback && callback();
                }
            }, 1000);
        }
    });
});
