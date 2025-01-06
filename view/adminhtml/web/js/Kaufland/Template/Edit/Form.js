define([
            'jquery',
            'Kaufland/Template/Edit'
        ],
        function (jQuery) {

            window.KauflandTemplateEdit = Class.create(TemplateEdit, {

                // ---------------------------------------

                templateNick: null,

                // ---------------------------------------

                initialize: function () {
                    jQuery.validator.addMethod('validate-title-uniqueness', function (value) {

                        let unique = false;
                        let templateId = 0;

                        if ($(KauflandTemplateEditObj.templateNick + '_id')) {
                            templateId = $(KauflandTemplateEditObj.templateNick + '_id').value;
                        }

                        new Ajax.Request(Kaufland.url.get('kaufland_template/isTitleUnique'), {
                            method: 'post',
                            asynchronous: false,
                            parameters: {
                                id_value: templateId,
                                title: value
                            },
                            onSuccess: function (transport) {
                                unique = transport.responseText.evalJSON()['unique'];
                            }
                        });

                        return unique;
                    }, Kaufland.translator.translate('Policy Title is not unique.'));
                },

                // ---------------------------------------

                initObservers: function () {
                    this.loadTemplateData(null);
                },

                // ---------------------------------------

                getComponent: function () {
                    return 'kaufland';
                },

                // ---------------------------------------

                loadTemplateData: function (callback) {
                    if (typeof this.value != 'undefined' && this.value === '') {
                        return;
                    }

                    const self = KauflandTemplateEditObj;

                    new Ajax.Request(Kaufland.url.get('kaufland_template/getTemplateHtml'), {
                        method: 'get',
                        asynchronous: true,
                        parameters: {},
                        onSuccess: function (transport) {

                            let editFormData = $('edit_form_data');
                            if (!editFormData) {
                                editFormData = document.createElement('div');
                                editFormData.id = 'edit_form_data';

                                $('edit_form').appendChild(editFormData);
                            }

                            editFormData.innerHTML = transport.responseText;
                            editFormData.innerHTML.extractScripts()
                                    .map(function (script) {
                                        try {
                                            eval(script);
                                        } catch (e) {
                                        }
                                    });

                            let titleInput = $$('input[name="' + self.templateNick + '[title]"]')[0];

                            if ($('title').value.trim() == '') {
                                $('title').value = titleInput.value;
                            }

                            callback && callback();
                        }
                    });
                },

                // ---------------------------------------

                isValidForm: function () {
                    let validationResult = true;

                    validationResult &= jQuery('#edit_form').valid();
                    validationResult &= Validation.validate($('title'));

                    let titleInput = $$('input[name="' + KauflandTemplateEditObj.templateNick + '[title]"]')[0];

                    if (titleInput) {
                        titleInput.value = $('title').value;
                    }

                    return validationResult;
                },

                // ---------------------------------------

                duplicateClick: function ($super, headId, chapter_when_duplicate_text, templateNick) {
                    $$('input[name="' + templateNick + '[id]"]')[0].value = '';

                    // we don't need it here, but parent method requires the formSubmitNew url to be defined
                    Kaufland.url.add({'formSubmitNew': ' '});

                    $super(headId, chapter_when_duplicate_text);
                },

                // ---------------------------------------

                saveAndCloseClick: function (url, confirmText) {
                    if (!this.isValidForm()) {
                        return;
                    }

                    const self = this;

                    if (this.isEnableReviseProductAttribute() &&
                            this.templateNick === Kaufland.php.constant('\\M2E\\Kaufland\\Model\\Kaufland\\Template\\Manager::TEMPLATE_SYNCHRONIZATION')) {

                        self.openPopup(function () {

                            if (confirmText && self.showConfirmMsg) {
                                self.confirm(self.templateNick, confirmText, function () {
                                    self.saveFormUsingAjax(url, self.templateNick);
                                });
                                return;
                            }

                            self.saveFormUsingAjax(url, self.templateNick);
                        });

                    } else {
                        if (confirmText && self.showConfirmMsg) {
                            self.confirm(self.templateNick, confirmText, function () {
                                self.saveFormUsingAjax(url, self.templateNick);
                            });
                            return;
                        }

                        self.saveFormUsingAjax(url, self.templateNick);
                    }
                },

                saveFormUsingAjax: function (url, templateNick) {
                    new Ajax.Request(url, {
                        method: 'post',
                        parameters: Form.serialize($('edit_form')),
                        onSuccess: function (transport) {
                            let templates = transport.responseText.evalJSON();

                            if (templates.length && templates[0].nick == templateNick) {
                                window.close();

                            } else {
                                console.error('Policy Saving Error');
                            }
                        }
                    });
                }

                // ---------------------------------------
            });
        });
