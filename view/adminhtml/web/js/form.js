/**
* Controls the js for the EBizCharge payment form in the backend (adminhtml).
*
* @author      Century Business Solutions <support@centurybizsolutions.com>
* @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
*/
define([
	"jquery",
], function($) {
    "use strict";
    $.widget('mage.ebizcharge', {
		options: {
            clientKey: false,
			code : "ebizcharge_ebizcharge",
        },
        enableDisableSavedFields: function(disabled) {
            var fields = ["_cc_token", "_cc_cid"];
            var id;
            for (id = 0; id < fields.length; id++) {
                $('#' + this.options.code + fields[id]).prop('disabled', disabled);
            }
        },
        enableDisableUpdateFields: function(disabled) {
            var fields = ["_cc_token_update", "_cc_cid_update", "_expiration_update", "_expiration_yr_update", "_avs_street", "_avs_zip"];
            var id;
            for (id = 0; id < fields.length; id++) {
                $('#' + this.options.code + fields[id]).prop('disabled', disabled);
            }
        },
        enableDisableFields: function(disabled) {
            var fields = ["_cc_owner_new", "_cc_type_new", "_cc_number_new", "_expiration_new", "_expiration_yr_new", "_cc_cid_new", "_save_payment"];
            var id;
            for (id = 0; id < fields.length; id++) {
                $('#' + this.options.code + fields[id]).prop('disabled', disabled);
            }
        },
		enableDisablePaylaterFields: function(disabled) {
            var fields = ["_paylater"];
            var id;
            for (id = 0; id < fields.length; id++) {
                $('#' + this.options.code + fields[id]).prop('disabled', disabled);
            }
        },
		
		prepare: function(event, method) {
            if (method === 'ebizcharge_ebizcharge') {
                this.preparePayment();
            }
        },
        prepareCVC: function(token) {
            var self = this;
        },
        preparePayment: function() {
            var self = this;
			$('#edit_form').off('submitOrder').on('submitOrder', this.submitAdminOrder.bind(this));
        },
        submitAdminOrder: function(event) 
		{
			var token = $('.ebzc_option').val();

            if (token == 'saved') {
                var ccNumber = $("#ebizcharge_ebizcharge_cc_number").val();
        		if (ccNumber) {
        			//this.enableDisableFields(true);
        			$('#ebzc_method_id').val('');
        		} else {

        		}
            } else if (token == 'update') {
                var ccNumber = $("#ebizcharge_ebizcharge_cc_number").val();
                if (ccNumber) {
                    //this.enableDisableFields(true);
                    $('#ebzc_method_id').val('');
                } else {

                }
			} else if (token == 'paylater') {
                var paylater = $("#ebizcharge_ebizcharge_paylater").val();
                if (paylater) {
                    //this.enableDisableFields(true);
                    $('#ebzc_method_id').val('');
                } else {

                }	
            } else {
                var ccNumber = $("#ebizcharge_ebizcharge_cc_number_new").val();
                if (ccNumber) {
                    //this.enableDisableFields(true);
                    $('#ebzc_method_id').val('');
                } else {

                }
            }
			
            order._realSubmit();
        },
        deletePaymentMethod: function() {
            var cid = $("#ebizcharge_ebizcharge_cust_id").val();
            var mid = $("#ebizcharge_ebizcharge_cc_token_update").val();
            var deleteURL = $("#ebizcharge_ebizcharge_delete_url").val();

            if (mid && deleteURL) {
                $.post(deleteURL,
                    {cid: cid,mid: mid},
                    function(data, textStatus, jqXHR)
                    {
                        // console.log("Success");
                    }).fail(function(jqXHR, textStatus, errorThrown) 
                    {
                        // console.log("Failed"); 
                    });
                location.reload();
            } else {
                // console.log("Missing info"); 
            }             
        },
        _create: function() {
            var self = this;
            
            $('#edit_form').on('changePaymentMethod', this.prepare.bind(this));
            $('#edit_form').trigger(
                'changePaymentMethod',
                [
                    $('#edit_form').find(':radio[name="payment[method]"]:checked').val()
                ]
            );

            $("#delete-payment").click(function() {
                self.deletePaymentMethod();
            });
			
            if ($('.saved_tokens').length) 
			{
                $('#ebzc_option_saved').prop('checked', true);
				$('.show_saved_selected').show();
                $('.show_update_selected').hide();
                $('.show_new_selected').hide();
				$('.show_paylater_selected').hide();
                self.enableDisableFields(true);
                self.enableDisableSavedFields(false);
                self.enableDisableUpdateFields(true);
				self.enableDisablePaylaterFields(true);
            } 
			else if ($('.saved_tokens_update').length) 
			{
                $('#ebzc_option_update').prop('checked', true);
                $('.show_saved_selected').hide();
                $('.show_update_selected').show();
                $('.show_new_selected').hide();
				$('.show_paylater_selected').hide();
                self.enableDisableFields(true);
                self.enableDisableSavedFields(true);
                self.enableDisableUpdateFields(false);
				self.enableDisablePaylaterFields(true);
			}
			else if ($('.saved_tokens_paylater').length) 
			{
                $('#ebzc_option_paylater').prop('checked', true);
                $('.show_saved_selected').hide();
                $('.show_update_selected').hide();
                $('.show_new_selected').hide();
				$('.show_paylater_selected').show();
                self.enableDisableFields(true);
                self.enableDisableSavedFields(true);
                self.enableDisableUpdateFields(true);
				self.enableDisablePaylaterFields(false);
            }
			else 
			{
				$('#ebzc_option_new').prop('checked', true);
				$('.show_saved_selected').hide();
                $('.show_update_selected').hide();
                $('.show_new_selected').show();
				$('.show_paylater_selected').hide();
                self.enableDisableFields(false);
                self.enableDisableSavedFields(true);
                self.enableDisableUpdateFields(true);
				self.enableDisablePaylaterFields(true);
			}
			
			$('.ebzc_option').bind('change', function (e) {
                var selectBox = $(this);
                var token = selectBox.val();
                if (token == 'saved') {
                    $('.show_saved_selected').show();
                    $('.show_update_selected').hide();
                    $('.show_new_selected').hide();
					$('.show_paylater_selected').hide();
                    self.enableDisableUpdateFields(true);
					self.enableDisableSavedFields(false);
                    self.enableDisableFields(true);
					self.enableDisablePaylaterFields(true);

                    $('#ebizcharge_ebizcharge_cc_number').bind('change', function(){
                        $('#cc_last4').val($("#ebizcharge_ebizcharge_cc_number").val().slice(-4));
                    });
                } else if (token == 'update') {
                    $('.show_saved_selected').hide();
                    $('.show_update_selected').show();
                    $('.show_new_selected').hide(); 
					$('.show_paylater_selected').hide();
                    self.enableDisableUpdateFields(false);
                    self.enableDisableSavedFields(true);
                    self.enableDisableFields(true);
					self.enableDisablePaylaterFields(true);

                    $('#ebizcharge_ebizcharge_cc_number').bind('change', function(){
                        $('#cc_last4').val($("#ebizcharge_ebizcharge_cc_number").val().slice(-4));
                    });

				} else if (token == 'paylater') {
                    $('.show_saved_selected').hide();
                    $('.show_update_selected').hide();
                    $('.show_new_selected').hide(); 
					$('.show_paylater_selected').show();
                    self.enableDisableUpdateFields(true);
                    self.enableDisableSavedFields(true);
                    self.enableDisableFields(true);
					self.enableDisablePaylaterFields(false);

                    $('#ebizcharge_ebizcharge_paylater').bind('change', function(){
                        //$('#cc_last4').val($("#ebizcharge_ebizcharge_cc_number").val().slice(-4));
                    });
				} else {
                    $('.show_saved_selected').hide();
                    $('.show_update_selected').hide();
                    $('.show_new_selected').show();
					$('.show_paylater_selected').hide();
                    self.enableDisableUpdateFields(true);
                    self.enableDisableFields(false);
                    self.enableDisableSavedFields(true);
					self.enableDisablePaylaterFields(true);

                    $('#ebizcharge_ebizcharge_cc_number_new').bind('change', function(){
                        $('#cc_last4').val($("#ebizcharge_ebizcharge_cc_number_new").val().slice(-4));
                    });
                }
            });
        }
    });

    return $.mage.ebizcharge;
});
