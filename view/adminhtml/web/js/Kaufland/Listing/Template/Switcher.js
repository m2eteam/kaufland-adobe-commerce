define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Kaufland/Common'
], function (jQuery, modal) {

    window.KauflandListingTemplateSwitcher = Class.create(Common, {

        // ---------------------------------------

        storeId: null,
        listingProductIds: '',

        // ---------------------------------------

        initialize: function () {
            jQuery.validator.addMethod('Kaufland-validate-kaufland-template-title', function (value, el) {

                var mode = base64_decode(value).evalJSON().mode;

                return mode !== null;
            }, Kaufland.translator.translate('This is a required field.'));

            jQuery.validator.addMethod('Kaufland-validate-kaufland-template-title', function (value, el) {

                var templateNick = el.name.substr(0, el.name.indexOf('['));

                return KauflandListingTemplateSwitcherObj.isTemplateTitleUnique(templateNick, value);
            }, Kaufland.translator.translate('Policy with the same Title already exists.'));
        },

        // ---------------------------------------

        getSwitcherNickByElementId: function (id) {
            return id.replace('template_', '');
        },

        getSwitcherElementId: function (templateNick) {
            return 'template_' + templateNick;
        },

        getSwitcher: function (templateNick) {
            return $(this.getSwitcherElementId(templateNick));
        },

        getSwitcherValueId: function (templateNick) {
            var switcher = this.getSwitcher(templateNick);
            var switcherValue = base64_decode(switcher.value).evalJSON();

            return switcherValue.id;
        },

        getSwitcherValueMode: function (templateNick) {
            var switcher = this.getSwitcher(templateNick);
            var switcherValue = base64_decode(switcher.value).evalJSON();

            return switcherValue.mode;
        },

        isSwitcherValueModeEmpty: function (templateNick) {
            return this.getSwitcherValueMode(templateNick) === null;
        },

        isSwitcherValueModeParent: function (templateNick) {
            return this.getSwitcherValueMode(templateNick) == Kaufland.php.constant('M2E_Kaufland_Model_Kaufland_Template_Manager::MODE_PARENT');
        },

        isSwitcherValueModeCustom: function (templateNick) {
            return this.getSwitcherValueMode(templateNick) == Kaufland.php.constant('M2E_Kaufland_Model_Kaufland_Template_Manager::MODE_CUSTOM');
        },

        isSwitcherValueModeTemplate: function (templateNick) {
            return this.getSwitcherValueMode(templateNick) == Kaufland.php.constant('M2E_Kaufland_Model_Kaufland_Template_Manager::MODE_TEMPLATE');
        },

        isExistSynchronizationTab: function () {
            return typeof KauflandTemplateSynchronizationHandlerObj != 'undefined';
        },

        isNeededSaveWatermarkImage: function (ajaxResponse) {
            var isDescriptionTemplate = false;

            return isDescriptionTemplate && $('watermark_image').value != '';
        },

        getTemplateDataContainer: function (templateNick) {
            return $('template_' + templateNick + '_data_container');
        },

        // ---------------------------------------

        change: function () {
            var templateNick = KauflandListingTemplateSwitcherObj.getSwitcherNickByElementId(this.id);
            var templateMode = KauflandListingTemplateSwitcherObj.getSwitcherValueMode(templateNick);

            KauflandListingTemplateSwitcherObj.clearMessages(templateNick);

            switch (templateMode) {
                case Kaufland.php.constant('M2E_Kaufland_Model_Kaufland_Template_Manager::MODE_PARENT'):
                    KauflandListingTemplateSwitcherObj.clearContent(templateNick);
                    break;

                case Kaufland.php.constant('M2E_Kaufland_Model_Kaufland_Template_Manager::MODE_CUSTOM'):
                    KauflandListingTemplateSwitcherObj.reloadContent(templateNick);
                    break;

                case Kaufland.php.constant('M2E_Kaufland_Model_Kaufland_Template_Manager::MODE_TEMPLATE'):
                    KauflandListingTemplateSwitcherObj.clearContent(templateNick);
                    KauflandListingTemplateSwitcherObj.checkMessages(templateNick);
                    break;
            }

            KauflandListingTemplateSwitcherObj.hideEmptyOption(KauflandListingTemplateSwitcherObj.getSwitcher(templateNick));

            if (!KauflandListingTemplateSwitcherObj.isSwitcherValueModeCustom(templateNick)) {
                KauflandListingTemplateSwitcherObj.updateButtonsVisibility(templateNick);
                KauflandListingTemplateSwitcherObj.updateEditVisibility(templateNick);
                KauflandListingTemplateSwitcherObj.updateTemplateLabelVisibility(templateNick);
            }
        },

        // ---------------------------------------

        clearMessages: function (templateNick) {
            $('template_switcher_' + templateNick + '_messages').innerHTML = '';
        },

        checkMessages: function (templateNick) {

            var id = this.getSwitcherValueId(templateNick),
                    nick = templateNick,
                    data = Form.serialize(this.getTemplateDataContainer(templateNick).id)
                            + '&listing_product_ids=' + this.listingProductIds,
                    storeId = this.storeId,
                    container = 'template_switcher_' + templateNick + '_messages',
                    callback = function () {
                        var refresh = $(container).down('a.refresh-messages');
                        if (refresh) {
                            refresh.observe('click', function () {
                                this.checkMessages(templateNick);
                            }.bind(this))
                        }
                    }.bind(this);

            TemplateManagerObj.checkMessages(
                    id,
                    nick,
                    data,
                    storeId,
                    container,
                    callback
            );
        },

        // ---------------------------------------

        updateEditVisibility: function (templateNick) {
            var tdEdit = $('template_' + templateNick + '_edit');

            if (!tdEdit) {
                return;
            }

            if (this.isSwitcherValueModeTemplate(templateNick)) {
                tdEdit.show();
            } else {
                tdEdit.hide();
            }
        },

        updateButtonsVisibility: function (templateNick) {
            var divButtonsContainer = $('template_' + templateNick + '_buttons_container');

            if (!divButtonsContainer) {
                return;
            }

            if (this.isSwitcherValueModeCustom(templateNick)) {
                divButtonsContainer.show();
            } else {
                divButtonsContainer.hide();
            }
        },

        updateTemplateLabelVisibility: function (templateNick) {
            var labelContainer = $('template_' + templateNick + '_nick_label');
            var templateLabel = labelContainer.down('span.template');

            labelContainer.hide();
            templateLabel && templateLabel.hide();

            if (this.isSwitcherValueModeTemplate(templateNick)) {
                labelContainer.show();
                templateLabel && templateLabel.show();
            }

            if (this.isSwitcherValueModeEmpty(templateNick)) {
                labelContainer.hide();
            }
        },

        // ---------------------------------------

        saveSwitchers: function (callback) {
            var validationResult = jQuery('#edit_form').valid();

            if (KauflandListingTemplateSwitcherObj.isExistSynchronizationTab()) {
                KauflandTemplateSynchronizationHandlerObj.checkVirtualTabValidation();
            }

            if (!validationResult) {
                return;
            }

            $('edit_form').request({
                method: 'post',
                asynchronous: true,
                parameters: {},
                onSuccess: function (transport) {

                    var response = transport.responseText.evalJSON();

                    response.each(function (template) {
                        KauflandListingTemplateSwitcherObj.afterCustomSaveAsTemplate(template.nick, template.id, template.title);
                    });

                    var params = {};

                    $$('.template-switcher').each(function (switcher) {
                        params[switcher.name] = switcher.value;
                    });

                    if ($('kauflandListingTemplateEditTabs')) {
                        params['tab'] = jQuery('#kauflandListingTemplateEditTabs').data().mageTabs.active.find('a')[0].id.split('_').pop();
                    }

                    if (KauflandListingTemplateSwitcherObj.isNeededSaveWatermarkImage(response)) {
                        KauflandTemplateDescriptionObj.saveWatermarkImage(callback, params);
                    } else if (typeof callback == 'function') {
                        callback(params);
                    }

                }.bind(this)
            });
        },

        // ---------------------------------------

        isTemplateTitleUnique: function (templateNick, templateTitle) {
            var unique = true;

            new Ajax.Request(Kaufland.url.get('Kaufland_template/isTitleUnique'), {
                method: 'get',
                asynchronous: false,
                parameters: {
                    nick: templateNick,
                    title: templateTitle
                },
                onSuccess: function (transport) {
                    unique = transport.responseText.evalJSON()['unique'];
                }
            });

            return unique;
        },

        // ---------------------------------------

        customSaveAsTemplate: function (templateNick) {
            new Ajax.Request(Kaufland.url.get('Kaufland_template/newTemplateHtml'), {
                method: 'GET',
                parameters: {
                    nick: templateNick
                },
                onSuccess: (function (transport) {
                    if ($('new_template_form_' + templateNick)) {
                        $('new_template_form_' + templateNick).remove();
                    }

                    $('html-body').insert({bottom: transport.responseText});

                    var form = jQuery('#new_template_form_' + templateNick);

                    form.form().validation();

                    modal({
                        title: Kaufland.translator.translate('Save as New Policy'),
                        type: 'popup',
                        buttons: [{
                            text: Kaufland.translator.translate('Cancel'),
                            class: 'action-secondary action-dismiss',
                            click: function () {
                                form.modal('closeModal');
                                $('new_template_form_' + templateNick).remove()
                            }
                        }, {
                            text: Kaufland.translator.translate('Save'),
                            class: 'action-primary action-accept',
                            click: function () {
                                if (!form.form().valid()) {
                                    return false;
                                }

                                $$('input[name="' + templateNick + '[id]"]')[0].value = '';
                                $$('input[name="' + templateNick + '[is_custom_template]"]')[0].value = 0;
                                $$('input[name="' + templateNick + '[title]"]')[0].value = $('template_title_' + templateNick).value;

                                $('edit_form').request({
                                    method: 'post',
                                    asynchronous: true,
                                    parameters: {
                                        nick: templateNick
                                    },
                                    onSuccess: function (transport) {

                                        var response = transport.responseText.evalJSON();

                                        response.each(function (template) {
                                            KauflandListingTemplateSwitcherObj.addToSwitcher(template.nick, template.id, template.title);
                                            KauflandListingTemplateSwitcherObj.clearContent(template.nick);
                                            KauflandListingTemplateSwitcherObj.updateButtonsVisibility(template.nick);
                                            KauflandListingTemplateSwitcherObj.updateEditVisibility(template.nick);
                                            KauflandListingTemplateSwitcherObj.updateTemplateLabelVisibility(template.nick);
                                            KauflandListingTemplateSwitcherObj.checkMessages(template.nick);
                                        });
                                    }.bind(this)
                                });

                                form.modal('closeModal');
                            }
                        }]
                    }, form);

                    form.modal('openModal');
                }).bind(this)
            });
        },

        afterCustomSaveAsTemplate: function (templateNick, templateId, templateTitle) {
            $$('input[name="' + templateNick + '[id]"]')[0].value = templateId;
            $$('input[name="' + templateNick + '[title]"]')[0].value = templateTitle;

            var switcher = KauflandListingTemplateSwitcherObj.getSwitcher(templateNick);

            switcher.down('.template-switcher-custom-option').value = base64_encode(
                    Object.toJSON({
                        mode: Kaufland.php.constant('M2E_Kaufland_Model_Kaufland_Template_Manager::MODE_CUSTOM'),
                        nick: templateNick,
                        id: templateId
                    })
            );
        },

        // ---------------------------------------

        editTemplate: function (templateNick) {
            var templateId = this.getSwitcherValueId(templateNick);

            window.open(
                    Kaufland.url.get('Kaufland_template/edit', {
                        id: templateId,
                        nick: templateNick,
                        close_on_save: 1
                    }), '_blank'
            );
        },

        // ---------------------------------------

        customizeTemplate: function (templateNick) {
            this.clearMessages(templateNick);
            this.reloadContent(templateNick, function () {
                $$('input[name="' + templateNick + '[id]"]')[0].value = KauflandListingTemplateSwitcherObj.getSwitcherValueId(templateNick);
                $$('input[name="' + templateNick + '[is_custom_template]"]')[0].value = 1;
                $$('input[name="' + templateNick + '[title]"]')[0].value += ' [' + Kaufland.translator.translate('Customized') + ']';
            });
            this.getSwitcher(templateNick).selectedIndex = 0;
        },

        // ---------------------------------------

        clearContent: function (templateNick) {
            this.getTemplateDataContainer(templateNick).innerHTML = '';
            this.getTemplateDataContainer(templateNick).hide();
        },

        // ---------------------------------------

        reloadContent: function (templateNick, callback) {
            var id = this.getSwitcherValueId(templateNick);
            var self = this;

            new Ajax.Request(Kaufland.url.get('Kaufland_template/getTemplateHtml'), {
                method: 'get',
                asynchronous: true,
                parameters: {
                    id: id,
                    nick: templateNick
                },
                onSuccess: function (transport) {

                    BlockNoticeObj.initializedBlocks = [];

                    self.getTemplateDataContainer(templateNick).replace(transport.responseText);
                    self.getTemplateDataContainer(templateNick).show();

                    self.updateButtonsVisibility(templateNick);
                    self.updateEditVisibility(templateNick);
                    self.updateTemplateLabelVisibility(templateNick);

                    if (typeof callback == 'function') {
                        callback();
                    }

                }
            });
        },

        // ---------------------------------------

        addToSwitcher: function (templateNick, templateId, templateTitle) {
            var switcher = this.getSwitcher(templateNick);
            var optionGroup = this.getTemplatesGroup(templateNick);

            var optionValue = {
                mode: Kaufland.php.constant('M2E_Kaufland_Model_Kaufland_Template_Manager::MODE_TEMPLATE'),
                nick: templateNick,
                id: templateId
            };
            var option = document.createElement('option');

            option.value = base64_encode(Object.toJSON(optionValue));
            option.innerHTML = templateTitle;

            $(optionGroup).appendChild(option);

            switcher.up('td').show();
            switcher.value = option.value;
        },

        getTemplatesGroup: function (templateNick) {
            var switcher = this.getSwitcher(templateNick);
            var optionGroup = switcher.down('optgroup.templates-group');

            if (typeof optionGroup != 'undefined') {
                return optionGroup;
            }

            optionGroup = document.createElement('optgroup');
            optionGroup.className = 'templates-group';
            optionGroup.label = Kaufland.translator.translate('Policies');

            switcher.appendChild(optionGroup);

            return optionGroup;
        }

        // ---------------------------------------
    });

});
