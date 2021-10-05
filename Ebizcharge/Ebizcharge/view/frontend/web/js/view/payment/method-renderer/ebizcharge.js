/**
 * Loads checkout form for Ebizcharge.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */
define(
    [
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'jquery',
        'Magento_Ui/js/model/messageList',
        'mage/translate'
    ],
    function (Component, setPaymentInformationAction, validator, additionalValidators, quote, $, messageList, $t) {
        'use strict';
        var wpConfig = window.checkoutConfig.payment.ebizcharge;
        return Component.extend({
            defaults: {
                template: 'Ebizcharge_Ebizcharge/payment/ebizcharge',
                useSavedCard: true,
                addNewCard: false,
                saveCard: false,
                updateSavedCard: false,
                paymentToken: false,
                ebzc_cust_id: '',
                ebzc_option: '',
                SecondarySort: '',
                selectedCardToken: '',
                selectedCardType: '',
                requestCC: false,
                has_token: false,
                storeInVault: false,
                useVault: false,
                additional: '',
                storedCards: [],
                ebzc_option_type: '',
                useSavedAccount: true,
                storedAccounts: [],
                useACH: true,
                useCC: false,
                routeNumber: '',
                ebzc_payment_option: 'ACH',
                achAccountTypes: [],
                addNewAccount: false,
                isAchActive: false,
                isRecurringEnabled: '',
                recurIndefinitely: false
            },
            initVars: function () {
                this.isPaymentProcessing = null;
                this.quoteBaseGrandTotals = quote.totals().base_grand_total;
            },

            initObservable: function (e) {
				self.name = this;
                this._super().observe([
                    'paymentToken',
                    'selectedCardToken',
					'selectedCardType',
                    'cardOwner',
                    'storeInVault',
                    'storedCards',
                    'useSavedCard',
                    'addNewCard',
                    'updateSavedCard',
                    'has_token',
                    'getEbzcCustId',
                    'useSavedAccount',
                    'storedAccounts',
                    'recurIndefinitely',
                    'useACH',
                    'useCC',
                    'achAccountTypes',
                    'addNewAccount',
                    'isAchActive',
                    'isRecurringEnabled',
                    'recurIndefinitely'
                ]);

                if (!this.storedAccounts.length && this.ebzc_payment_option == 'ACH') {
                    this.useSavedAccount(false);
                }

                if ((!this.storedCards.length) || (this.has_token == false) || (this.has_token == 0)) {
                    this.useSavedCard(false);
                }

                if ((this.storedCards.length) && (this.has_token)) {
                    this.paymentToken(this.storedCards[0].MethodID);
                }

                this.initMage();
                this.updateEbizSavedOption();
                this.updateEbizCCPaymentOption(2);
                return this;
            },

            initMage: function (element) {
                this.has_token = wpConfig.hasToken || false;
                this.isach_active = wpConfig.isAchActive || 0;
                this.ebzc_cust_id = wpConfig.getEbzcCustId || '';
                this.storedCards = wpConfig.storedCards || [];
                this.storedAccounts = wpConfig.storedAccounts || [];
                this.requestCC = wpConfig.requestCardCode || 0;
                this.saveCard = wpConfig.saveCard || 0;
                this.useVault = wpConfig.useVault || false;
                this.addNewCard(false);
                this.addNewAccount(false);
                this.useSavedCard(true);
                this.updateSavedCard(false);
                this.selectedCardToken(false);
                this.selectedCardType(false);
                this.useSavedAccount(true);
                this.achAccountTypes = wpConfig.getAchAccountTypes;
                this.useACH(false);
                this.useCC(false);
                this.isRecurringEnabled = wpConfig.isRecurringEnabled;
            },

            getAchStatus: function () {
                return this.isach_active;
            },
            getAchAccountTypes: function () {
                return this.achAccountTypes;
            },
            updateEbizACHPaymentOption: function () {
                this.ebzc_payment_option = 'ACH';
                this.useACH(true);
                this.useCC(false);

            },

            updateEbizCCPaymentOption: function (a) {
                if (a == 1) {
                    this.ebzc_payment_option = 'credit_card';
                    this.useACH(false);
                    this.useCC(true);
                } else {
                    this.ebzc_payment_option = 'ACH';
                    this.useACH(true);
                    this.useCC(false);
                }
            },

            getCode: function () {
                return 'ebizcharge_ebizcharge';
            },

            isActive: function () {
                return true;
            },

            getUseVault: function () {
                return this.useVault;
            },

            getRequestCardCode: function () {
                return (this.requestCC == 1);
            },

            getSaveCard: function () {
                if (this.saveCard == 1 || this.saveCard == true) {
                    return false;
                }
                return true;
            },
            // Get all saved accounts (List or null)
            getAllSavedAccounts: function () {
                this.storedAccounts = wpConfig.storedAccounts;

                if (!this.storedAccounts.length || this.storedAccounts.length == '' || this.storedAccounts == null) {
                    return false;
                } else {
                    return true;
                }
            },

            // Get all saved cards (List or null)
            getAllSavedCards: function () {
                this.storedCards = wpConfig.storedCards;
                if (!this.storedCards.length || this.storedCards.length == '' || this.storedCards == null) {
                    return false;
                } else {
                    return true;
                }
            },

            // get Count for all saved cards
            getCountSavedCards: function () {
                this.storedCards = wpConfig.storedCards;
                var userDefinedCard = null;
                if (typeof this.storedCards.length === "undefined") {
                    userDefinedCard = {
                        "CardCount": 1,
                        "CardType": this.storedCards.CardType,
                        "AllCards": []
                    };
                } else {
                    userDefinedCard = {
                        "CardCount": this.storedCards.length,
                        "CardType": this.getSelectedCardData().selectedcardType,
                        "AllCards": this.storedCards
                    };
                }
                return userDefinedCard;
            },

            getData: function () {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_type': this.creditCardType() ? this.creditCardType() : 'ACH',
                        'cc_exp_year': $('#' + this.getCode() + '_expiration_yr').val(), //this.creditCardExpYear(),
                        'cc_exp_month': $('#' + this.getCode() + '_expiration').val(), //this.creditCardExpMonth(),
                        'cc_number': this.creditCardNumber(),
                        'ach_routing': $('#' + this.getCode() + '_cc_routing').val(),
                        'ach_type': $('#' + this.getCode() + '_ach_type').val(),
                        'cc_owner': $('#' + this.getCode() + '_cc_owner').val(),
                        'ebzc_avs_street': $('#' + this.getCode() + '_avs_street').val(),
                        'ebzc_avs_zip': $('#' + this.getCode() + '_avs_zip').val(),
                        'ebzc_option': this.ebzc_option,
                        'ebzc_method_id': this.selectedCardToken(),
                        'ebzc_cust_id': this.getEbzcCustId(),
                        'ebzc_save_payment': this.storeInVault(),
                        'paymentToken': this.paymentToken(),
                        'ebzc_option_type': this.ebzc_payment_option,
                        'rec_admin': this.isRecurringEnabled
                    }
                };
            },

            // Get selected card all data
            getSelectedCardData: function () {
                var selectId = $('#' + this.getCode() + '_ebzc_method').val(); // this.selectedCardToken();
                var selectedCardType = null;
                var paymentMethod = null;
                var cards_array = this.getStoredCards();
                for (var i = 0; i < cards_array.length; i++) {
                    var obj = cards_array[i];
                    if (obj.MethodID == selectId) {
                        selectedCardType = obj.CardType;
                        paymentMethod = obj;
                        break;
                    }
                }
                return {
                    'selectedcardType': selectedCardType,
                    'SelectedCardCvv': this.creditCardVerificationNumber().length,
                    'paymentMethod': paymentMethod,
                };

            },

            // Get selected card CVV length
            getCardCvvLength: function () {
                return this.creditCardVerificationNumber().length;
            },

            // Get New method card type
            getNewCardType: function () {
                return this.creditCardType();
            },

            // Will Clear CVV field by clicking Radio buttons
            clearCvv: function () {
                $(".cvv").val('');
            },

            // Will Clear CVV field by selecting DDL options
            clearCvvById: function () {
                $(".pmethodddl").change(function () {
                    $(".cvv").val('');
                });
            },

            // Prepare and process payment information
            preparePayment: function () {
                if (!this.getUseVault()) {
                    this.updateEbizNewOption();
                }
                if (this.validate()) {
                    var self = this;
                    this.messageContainer.clear();
                    self.placeOrder();
                }
            },

            getStoredAccounts: function () {
                return this.storedAccounts;
            },

            getStoredCards: function () {
                return this.storedCards;
            },

            updateEbizSavedOption: function () {
                this.ebzc_option = 'saved';
                this.addNewCard(false);
                this.updateSavedCard(false);
                this.useSavedCard(true);
                this.paymentToken(true);
                this.clearCvv();
                this.clearCvvById();
            },

            updateEbizUpdateOption: function () {
                this.ebzc_option = 'update';
                this.useSavedCard(false);
                this.addNewCard(false);
                this.addNewAccount(false);
                this.updateSavedCard(true);
                this.paymentToken(true);
				this.clearCvv();
				this.clearCvvById();
                this.updateCardDetails();
            },

            updateCardDetails: function () {
                var selectedCard = this.getSelectedCardData();
                if(selectedCard != null && selectedCard.paymentMethod != null) {
                    var cardData = selectedCard.paymentMethod;
                    var expiryData = cardData.CardExpiration;
                    var expiry = expiryData.split('-');
                    var month = expiry[1];
                    if (month < 10) {
                        month = expiry[1].substr(1)
                    }

                    $('#' + this.getCode() + '_avs_street').val(cardData.AvsStreet);
                    $('#' + this.getCode() + '_avs_zip').val(cardData.AvsZip);
                    $('#' + this.getCode() + '_expiration_yr').val(expiry[0]);
                    $('#' + this.getCode() + '_expiration').val(month);
                }
            },

            cardChanged: function (l) {
                if (typeof l != 'undefined') {
                    this.updateCardDetails();
                }
            },

            updateEbizNewOption: function () {
                this.ebzc_option = 'new';
                this.useSavedCard(false);
                this.updateSavedCard(false);
                this.addNewCard(true);
                this.addNewAccount(true);
                this.paymentToken(false);
                var matchdiv = $('.divSaved').html();
                if (typeof matchdiv === "undefined") {
                    // if statment here
                } else {
                    this.clearCvv();
                }
            },

            getEbzcCustId: function () {
                return this.ebzc_cust_id;
            },

            validate: function () {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            }

        });
    }
);
