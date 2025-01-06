define([
    'underscore',
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'M2ECore/Plugin/Messages',
], function (_, jQuery, $t,modal, MessageObj) {
    'use strict';

    window.KauflandListingCreateGeneral = Class.create({

        accounts: null,
        selectedAccountId: null,

        // ---------------------------------------

        initialize: function (storefronts) {
            const self = this;

            CommonObj.setValidationCheckRepetitionValue(
                    'Kaufland-listing-title',
                    $t('The specified Title is already used for other Listing. Listing Title must be unique.'),
                    'Listing', 'title', 'id', null
            );

            self.initAccount();
            self.initStorefront(storefronts);
        },

        initAccount: function () {
            const self = this;

            $('add_account_button').observe('click', function () {
                let popup = jQuery('#account_credentials');

                modal({
                    'type': 'popup',
                    'modalClass': 'custom-popup',
                    'responsive': true,
                    'innerScroll': true,
                    'buttons': []
                }, popup);

                popup.modal('openModal');

                jQuery('body').on('submit', '#account_credentials', function (e) {
                    e.preventDefault();

                    let postUrl = Kaufland.url.get('kaufland_account/create');
                    jQuery.ajax({
                        type: 'POST',
                        url: postUrl,
                        showLoader: true,
                        dataType: 'json',
                        data: popup.serialize(),
                        success: function (response) {
                            jQuery('#account_credentials').modal('closeModal');

                            if (response.redirectUrl) {
                                setLocation(response.redirectUrl);
                            }
                        },
                        error: function () {
                            jQuery('#account_credentials').modal('closeModal');
                        }
                    });
                });

                self.renderAccounts()
            });

            $('account_id').observe('change', function () {
                self.selectedAccountId = $('account_id').value || self.selectedAccountId;

                if (_.isNull(self.selectedAccountId)) {
                    return;
                }

                new Ajax.Request(Kaufland.url.get('kaufland_account/getStorefrontsForAccount'), {
                    method: 'post',
                    parameters: {account_id: self.selectedAccountId},
                    onSuccess: function (transport) {
                        let response = JSON.parse(transport.responseText);
                        if (response.result) {
                            self.refreshStorefronts(response.storefronts);
                            return;
                        }
                        throw response.message;
                    }
                })
            });

            self.renderAccounts();
        },

        refreshStorefronts: function (storefronts) {
            const select = jQuery('#storefront_id');
            let selectedStorefrontId = +select.val();
            select.find('option').remove();

            storefronts.each(function (storefront) {
                select.append(new Option(storefront.storefront_name, storefront.id))

                if (selectedStorefrontId === storefront.id) {
                    select.val(selectedStorefrontId);
                }
            })
        },

        renderAccounts: function (callback) {
            let self = this;


            let accountAddBtn = $('add_account_button');
            let accountLabelEl = $('account_label');
            let accountSelectEl = $('account_id');
            let storefrontSelectField = $('storefront_id').up('.field');

            new Ajax.Request(Kaufland.url.get('general/getAccounts'), {
                method: 'get',
                onSuccess: function (transport) {
                    let accounts = transport.responseText.evalJSON();

                    if (_.isNull(self.accounts)) {
                        self.accounts = accounts;
                    }

                    if (_.isNull(self.selectedAccountId)) {
                        self.selectedAccountId = $('account_id').value;
                    }

                    let isAccountsChanged = !self.isAccountsEqual(accounts);

                    if (isAccountsChanged) {
                        self.accounts = accounts;
                    }

                    if (accounts.length === 0) {
                        accountAddBtn.down('span').update($t('Add'));
                        accountLabelEl.update($t('Account not found, please create it.'));
                        accountLabelEl.show();
                        accountSelectEl.hide();
                        storefrontSelectField.hide();
                        return;
                    }

                    accountSelectEl.update();
                    accountSelectEl.appendChild(new Element('option', {style: 'display: none'}));
                    accounts.each(function (account) {
                        accountSelectEl.appendChild(new Element('option', {value: account.id})).insert(account.title);
                    });

                    accountAddBtn.down('span').update($t('Add Another'));

                    if (accounts.length === 1) {
                        let account = _.first(accounts);

                        $('account_id').value = account.id;
                        self.selectedAccountId = account.id;

                        let accountElement;

                        if (Kaufland.formData.wizard) {
                            accountElement = new Element('span').update(account.title);
                        } else {
                            let accountLink = Kaufland.url.get('kaufland_account/edit', {
                                'id': account.id,
                                close_on_save: 1
                            });
                            accountElement = new Element('a', {
                                'href': accountLink,
                                'target': '_blank'
                            }).update(account.title);
                        }

                        accountLabelEl.update(accountElement);

                        accountLabelEl.show();
                        accountSelectEl.dispatchEvent(new Event('change'));
                        accountSelectEl.hide();
                        storefrontSelectField.show();
                    } else if (isAccountsChanged) {
                        self.selectedAccountId = _.last(accounts).id;

                        accountLabelEl.hide();
                        accountSelectEl.show();
                        accountSelectEl.dispatchEvent(new Event('change'));
                        storefrontSelectField.show();
                    }

                    accountSelectEl.setValue(self.selectedAccountId);

                    callback && callback();
                }
            });
        },

        initStorefront: function () {
            $$('.next_step_button').each(function (btn) {
                btn.observe('click', function () {
                    if (jQuery('#edit_form').valid()) {
                        CommonObj.saveClick(Kaufland.url.get('kaufland_listing_create/index'), true);
                    }
                });
            });
        },

        isAccountsEqual: function (newAccounts) {
            if (!newAccounts.length && !this.accounts.length) {
                return true;
            }

            if (newAccounts.length !== this.accounts.length) {
                return false;
            }

            return _.every(this.accounts, function (account) {
                return _.where(newAccounts, account).length > 0;
            });
        }

        // ---------------------------------------
    });
});
