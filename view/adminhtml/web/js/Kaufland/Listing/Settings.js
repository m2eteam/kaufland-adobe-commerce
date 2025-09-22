define([
    'Kaufland/Common'
], function () {
    window.KauflandListingSettings = Class.create(Common, {

        // ---------------------------------------

        initialize: function () {
            this.checkGenerateSKUMode();
        },

        initObservers: function () {

            $('template_selling_format_id').observe('change', function () {
                if ($('template_selling_format_id').value) {
                    $('edit_selling_format_template_link').show();
                } else {
                    $('edit_selling_format_template_link').hide();
                }
            });
            $('template_selling_format_id').simulate('change');

            $('template_selling_format_id').observe('change', function () {
                KauflandListingSettingsObj.checkSellingFormatMessages();
                KauflandListingSettingsObj.hideEmptyOption(this);
            });
            if ($('template_selling_format_id').value) {
                $('template_selling_format_id').simulate('change');
            }

            $('template_synchronization_id').observe('change', function () {
                if ($('template_synchronization_id').value) {
                    $('edit_synchronization_template_link').show();
                } else {
                    $('edit_synchronization_template_link').hide();
                }
            });
            $('template_synchronization_id').simulate('change');

            $('template_synchronization_id').observe('change', function () {
                KauflandListingSettingsObj.hideEmptyOption(this);
            });
            if ($('template_synchronization_id').value) {
                $('template_synchronization_id').simulate('change');
            }

            $('template_shipping_id').observe('change', function () {
                if ($('template_shipping_id').value) {
                    $('edit_shipping_template_link').show();
                } else {
                    $('edit_shipping_template_link').hide();
                }
            });
            $('template_shipping_id').simulate('change');

            $('template_shipping_id').observe('change', function () {
                KauflandListingSettingsObj.hideEmptyOption(this);
            });
            if ($('template_shipping_id').value) {
                $('template_shipping_id').simulate('change');
            }

            $('sku_mode_select').observe('change', function() {
                var self = KauflandListingSettingsObj;

                $('sku_custom_attribute').value = '';
                if (this.value == 2) {
                    self.updateHiddenValue(this, $('sku_custom_attribute'));
                }

                $('sku_mode').value = this.value;
            });

            if ($('sku_modification_mode').value == 0) {
                $('sku_modification_custom_value_tr').hide();
            }
            $('sku_modification_mode_select').observe('change', function() {
                $('sku_modification_mode').value = this.value;

                if ($('sku_modification_mode_select').value == 0) {
                    $('sku_modification_custom_value').value = '';
                    $('sku_modification_custom_value_tr').hide();
                } else {
                    $('sku_modification_custom_value_tr').show();
                }
            }).simulate('change');

            $('generate_sku_mode').observe('change', function() {
                if (this.value == '1') {
                    $('sku_mode_select').disable();
                    $('sku_modification_mode_select').disable();
                    $('sku_modification_custom_value_tr').hide();
                } else {
                    $('sku_mode_select').enable();
                    $('sku_modification_mode_select').enable();
                    if ($('sku_modification_mode').value != 0) {
                        $('sku_modification_custom_value_tr').show();
                    }
                }
            });

            $('template_description_id').observe('change', function () {
                if ($('template_description_id').value) {
                    $('edit_description_template_link').show();
                } else {
                    $('edit_description_template_link').hide();
                }
            });
            $('template_description_id').simulate('change');

            $('template_description_id').observe('change', function () {
                KauflandListingSettingsObj.hideEmptyOption(this);
            });
            if ($('template_description_id').value) {
                $('template_description_id').simulate('change');
            }
        },

        checkGenerateSKUMode: function() {
            if ($('generate_sku_mode').value == '1') {
                $('sku_mode_select').disable();
                $('sku_modification_mode_select').disable();
                $('sku_modification_custom_value_tr').hide();
            } else {
                $('sku_mode_select').enable();
                $('sku_modification_mode_select').enable();
                if ($('sku_modification_mode').value != 0) {
                    $('sku_modification_custom_value_tr').show();
                }
            }
        },

        // ---------------------------------------

        saveClick: function (url) {
            if (typeof categories_selected_items != 'undefined') {
                array_unique(categories_selected_items);

                var selectedCategories = implode(',', categories_selected_items);

                $('selected_categories').value = selectedCategories;
            }

            if (typeof url == 'undefined' || url == '') {
                url = Kaufland.url.formSubmit + 'back/' + base64_encode('list') + '/';
            }

            this.submitForm(url);
        },

        // ---------------------------------------

        checkSellingFormatMessages: function () {
            const storeId = $('store_id').value;
            const storefrontId = $('storefront_id').value;

            if (storeId.empty() || storeId < 0 || storefrontId.empty() || storefrontId < 0) {
                return;
            }

            const id = $('template_selling_format_id').value,
                    nick = 'selling_format',
                    container = 'template_selling_format_messages',
                    callback = function () {
                        var refresh = $(container).down('a.refresh-messages');
                        if (refresh) {
                            refresh.observe('click', function () {
                                this.checkSellingFormatMessages();
                            }.bind(this));
                        }
                    }.bind(this);

            TemplateManagerObj.checkMessages(
                    id,
                    nick,
                    '',
                    storeId,
                    container,
                    callback,
                    storefrontId
            );
        },

        // ---------------------------------------

        reload: function (url, id) {
            new Ajax.Request(url, {
                asynchronous: false,
                onSuccess: function (transport) {

                    var data = transport.responseText.evalJSON(true);

                    var options = '';

                    var firstItemValue = '';
                    var currentValue = $(id).value;

                    data.each(function (paris) {
                        var key = (typeof paris.key != 'undefined') ? paris.key : paris.id;
                        var val = (typeof paris.value != 'undefined') ? paris.value : paris.title;
                        options += '<option value="' + key + '">' + val + '</option>\n';

                        if (firstItemValue == '') {
                            firstItemValue = key;
                        }
                    });

                    $(id).update();
                    $(id).insert(options);

                    if (currentValue != '') {
                        $(id).value = currentValue;
                    } else {
                        if (Kaufland.formData[id] > 0) {
                            $(id).value = Kaufland.formData[id];
                        } else {
                            $(id).value = firstItemValue;
                        }
                    }

                    $(id).simulate('change');
                }
            });
        },

        // ---------------------------------------

        addNewTemplate: function (url, callback) {
            var win = window.open(url);

            var intervalId = setInterval(function () {
                if (!win.closed) {
                    return;
                }

                clearInterval(intervalId);

                callback && callback();

            }, 1000);
        },

        editTemplate: function (url, id, callback) {
            var win = window.open(url + 'id/' + id);

            var intervalId = setInterval(function () {
                if (!win.closed) {
                    return;
                }

                clearInterval(intervalId);

                callback && callback();

            }, 1000);
        },

        // ---------------------------------------


        newSellingFormatTemplateCallback: function () {
            var noteEl = $('template_selling_format_note');

            KauflandListingSettingsObj.reload(Kaufland.url.get('getSellingFormatTemplates'), 'template_selling_format_id');
            if ($('template_selling_format_id').children.length > 0) {
                $('template_selling_format_id').show();
                noteEl && $('template_selling_format_note').show();
                $('template_selling_format_label').hide();
            } else {
                $('template_selling_format_id').hide();
                noteEl && $('template_selling_format_note').hide();
                $('template_selling_format_label').show();
            }
        },

        newSynchronizationTemplateCallback: function () {
            var noteEl = $('template_synchronization_note');

            KauflandListingSettingsObj.reload(Kaufland.url.get('getSynchronizationTemplates'), 'template_synchronization_id');
            if ($('template_synchronization_id').children.length > 0) {
                $('template_synchronization_id').show();
                noteEl && $('template_synchronization_note').show();
                $('template_synchronization_label').hide();
            } else {
                $('template_synchronization_id').hide();
                noteEl && $('template_synchronization_note').hide();
                $('template_synchronization_label').show();
            }
        },

        newShippingTemplateCallback: function () {
            var noteEl = $('template_shipping_note');

            KauflandListingSettingsObj.reload(Kaufland.url.get('getShippingTemplates'), 'template_shipping_id');
            if ($('template_shipping_id').children.length > 0) {
                $('template_shipping_id').show();
                noteEl && $('template_shipping_note').show();
                $('template_shipping_label').hide();
            } else {
                $('template_shipping_id').hide();
                noteEl && $('template_shipping_note').hide();
                $('template_shipping_label').show();
            }
        },

        newDescriptionTemplateCallback: function () {
            var noteEl = $('template_description_note');

            KauflandListingSettingsObj.reload(Kaufland.url.get('getDescriptionTemplates'), 'template_description_id');
            if ($('template_description_id').children.length > 0) {
                $('template_description_id').show();
                noteEl && $('template_description_note').show();
                $('template_description_label').hide();
            } else {
                $('template_description_id').hide();
                noteEl && $('template_description_note').hide();
                $('template_description_label').show();
            }
        }

        // ---------------------------------------
    });
});
