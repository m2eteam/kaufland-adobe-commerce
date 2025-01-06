define([
            'jquery',
            'Magento_Ui/js/modal/modal',
            'Magento_Ui/js/modal/alert',
            'Magento_Ui/js/modal/confirm',
            'mage/translate',
            'Kaufland/Plugin/Storage',
            'Kaufland/Common',
            'Kaufland/General/PhpFunctions'
        ],
        function ($,modal, alert, confirm, $t, localStorage) {
            window.TemplateEdit = Class.create(Common, {

                // ---------------------------------------

                showConfirmMsg: true,
                skipSaveConfirmationPostFix: '_skip_save_confirmation',

                // ---------------------------------------

                getComponent: function () {
                    alert('abstract getComponent');
                },

                // ---------------------------------------

                confirm: function (templateNick, confirmText, okCallback) {
                    const self = this;
                    let skipConfirmation = localStorage.get(this.getComponent() + '_template_' + templateNick + self.skipSaveConfirmationPostFix);

                    if (!confirmText || skipConfirmation) {
                        okCallback();
                        return;
                    }

                    confirm({
                        title: Kaufland.translator.translate('Save Policy'),
                        content: confirmText + '<div class="admin__field admin__field-option" style="position: absolute; bottom: 43px; left: 28px;">' +
                                '<input class="admin__control-checkbox" type="checkbox" id="do_not_show_again" name="do_not_show_again">&nbsp;' + '<label for="do_not_show_again" class="admin__field-label"><span>' + Kaufland.translator.translate('Do not show any more') + '</span></label>' + '</div>',
                        buttons: [{
                            text: Kaufland.translator.translate('Cancel'),
                            class: 'action-secondary action-dismiss',
                            click: function (event) {
                                this.closeModal(event);
                            }
                        }, {
                            text: Kaufland.translator.translate('Confirm'),
                            class: 'action-primary action-accept',
                            click: function (event) {
                                this.closeModal(event, true);
                            }
                        }],
                        actions: {
                            confirm: function () {
                                if ($('do_not_show_again').checked) {
                                    localStorage.set(self.getComponent() + '_template_' + templateNick + self.skipSaveConfirmationPostFix, 1);
                                }

                                okCallback();
                            },
                            cancel: function () {
                                return false;
                            }
                        }
                    });
                },

                // ---------------------------------------

                deleteClick: function () {
                    Common.prototype.confirm({
                        actions: {
                            confirm: function () {
                                setLocation(Kaufland.url.get('deleteAction'));
                            },
                            cancel: function () {
                                return false;
                            }
                        }
                    });
                },

                duplicateClick: function ($super, $headId, chapter_when_duplicate_text) {
                    this.showConfirmMsg = false;

                    $super($headId, chapter_when_duplicate_text);
                },

                saveClick: function ($super, url, confirmText, templateNick) {
                    if (!this.isValidForm()) {
                        return;
                    }

                    if (this.isEnableReviseProductAttribute() &&
                            this.templateNick === Kaufland.php.constant('\\M2E\\Kaufland\\Model\\Kaufland\\Template\\Manager::TEMPLATE_SYNCHRONIZATION')) {

                        this.openPopup(function () {

                            if (confirmText && this.showConfirmMsg) {
                                this.confirm(templateNick, confirmText, function () {
                                    $super(url, true);
                                });
                                return;
                            }

                            $super(url, true);
                        }.bind(this));

                    } else {
                        if (confirmText && this.showConfirmMsg) {
                            this.confirm(templateNick, confirmText, function () {
                                $super(url, true);
                            });
                            return;
                        }

                        $super(url, true);
                    }
                },

                saveAndEditClick: function ($super, url, tabsId, confirmText, templateNick) {
                    if (!this.isValidForm()) {
                        return;
                    }

                    if (this.isEnableReviseProductAttribute() &&
                            this.templateNick === Kaufland.php.constant('\\M2E\\Kaufland\\Model\\Kaufland\\Template\\Manager::TEMPLATE_SYNCHRONIZATION')) {

                        this.openPopup(function () {

                            if (confirmText && this.showConfirmMsg) {
                                this.confirm(templateNick, confirmText, function () {
                                    $super(url, tabsId, true);
                                });
                                return;
                            }

                            $super(url, tabsId, true);
                        }.bind(this));

                    } else {
                        if (confirmText && this.showConfirmMsg) {
                            this.confirm(templateNick, confirmText, function () {
                                $super(url, tabsId, true);
                            });
                            return;
                        }

                        $super(url, tabsId, true);
                    }
                },

                isEnableReviseProductAttribute: function () {
                    return $('#revise_update_title').val() == 1 ||
                            $('#revise_update_description').val() == 1 ||
                            $('#revise_update_images').val() == 1 ||
                            $('#revise_update_categories').val() == 1;
                },

                openPopup: function (callback) {
                    const text = $t(
                            'To Revise Product data in Kaufland catalog, ensure that a valid Description Policy is assigned to all M2E Listings ' +
                            'using these Synchronization rules and that a proper Kaufland category is set for the Products.'
                    );
                    let popupHtml = $('<div />').html(text);

                    const options = {
                        title: $t('Revise Product Data'),
                        content: popupHtml,
                        type: 'popup',
                        buttons: [{
                            text: 'OK',
                            class: 'action-primary',
                            click: function () {
                                popupHtml.modal('closeModal');
                                if (typeof callback === 'function') {
                                    callback();
                                }
                            }
                        }],
                        modalClass: 'synchronization-revise-popup-class'
                    };

                    popupHtml.modal(options);

                    popupHtml.modal('openModal');
                },

                // ---------------------------------------

                forgetSkipSaveConfirmation: function () {
                    localStorage.removeAllByPostfix(this.skipSaveConfirmationPostFix);
                }

                // ---------------------------------------
            });
        });
