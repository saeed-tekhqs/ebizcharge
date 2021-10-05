require(['jquery'], function($) {

    $(document).ready(function() {

        jQuery('#payment_cc_number_new').change(function () {

            let cc = jQuery(this).val();
            cc = cc.replaceAll("\\D", "");
            if(!cc || !cc.length) return undefined;
            let ccType = '';
            let firstNumber = cc.charAt(0);
            if(firstNumber == '4') ccType = 'VI';
            if(firstNumber == '5') ccType = 'MC';
            if(firstNumber == '3') ccType = 'AE';
            if(firstNumber == '6') ccType = 'DI';

            jQuery('#payment_cc_type_new').val(ccType);
            if(ccType != '') {
                jQuery('#payment_cc_type_new option:not(:selected)').prop('disabled', true);
            }
        });
    });
});

require(['jquery', 'chosen'], function ($) {
    $(".chosen").chosen();
});
require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'mage/url'
    ],
    function ($, modal, url) {

        url.setBaseUrl(BASE_URL);

        var str = $('#sub_btn').text();

        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Confirm',
            buttons: [{
                text: $.mage.__('Cancel'),
                class: 'btn-background-cancel',
                click: function () {
                    this.closeModal();
                }
            }, {
                text: $.mage.__('Yes, Save'),
                class: 'btn-background-del',
                click: function () {
                    $('#form-validate').submit();
                }
            }]
        }

        var optionsConfirmDel = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Delete',
            buttons: [{
                text: $.mage.__('Cancel'),
                class: 'btn-background-cancel',
                click: function () {
                    this.closeModal();
                }
            }, {
                text: $.mage.__('Yes, Delete'),
                class: 'btn-background-del',
                click: function () {
                    $('#delsub').submit();
                }
            }]
        };

        var optionsConfirm = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Subscription Action',
            buttons: [{
                text: $.mage.__('Cancel'),
                class: 'btn-background-cancel',
                click: function () {
                    this.closeModal();
                }
            }, {
                text: $.mage.__('Yes, ' + str),
                class: 'btn-background-del',
                click: function () {
                    $('#unsub').submit();
                }
            }]
        };

        var optionsAddress = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Customer Address',
            buttons: [{
                text: $.mage.__('Cancel'),
                class: 'btn-background-cancel',
                click: function () {
                    this.closeModal();
                }
            }, {
                text: $.mage.__('Save Address'),
                class: 'btn-background-del',
                click: function () {

                    if ($('#firstname').val() == '') {
                        document.getElementById("firstname").style.borderColor = "red";
                        return false;
                    } else {
                        document.getElementById("firstname").style.borderColor = "#adadad";
                    }
                    if ($('#lastname').val() == '') {
                        document.getElementById("lastname").style.borderColor = "red";
                        return false;
                    } else {
                        document.getElementById("lastname").style.borderColor = "#adadad";
                    }
                    if ($('#street_1').val() == '') {
                        document.getElementById("street_1").style.borderColor = "red";
                        return false;
                    } else {
                        document.getElementById("street_1").style.borderColor = "#adadad";
                    }
                    if ($('#city').val() == '') {
                        document.getElementById("city").style.borderColor = "red";
                        return false;
                    } else {
                        document.getElementById("city").style.borderColor = "#adadad";
                    }
                    if ($('#region_id').val() == '') {
                        document.getElementById("region_id").style.borderColor = "red";
                        return false;
                    } else {
                        document.getElementById("region_id").style.borderColor = "#adadad";
                    }

                    if ($('#zip').val() == '') {
                        document.getElementById("zip").style.borderColor = "red";
                        return false;
                    } else {
                        document.getElementById("zip").style.borderColor = "#adadad";
                    }
                    if ($('#telephone').val() == '') {
                        document.getElementById("telephone").style.borderColor = "red";
                        return false;
                    } else {
                        document.getElementById("telephone").style.borderColor = "#adadad";
                    }

                    var customerIdAddress = $('#customerIdAddress').val();
                    var addressActionUrl = $('#addressActionUrl').val();
                    var loadCustomerAddressUrl = $('#loadCustomerAddressUrl').val();
                    url.setBaseUrl(BASE_URL);
                    $.ajax({
                        type: "POST",
                        url: addressActionUrl,
                        dataType: "html",
                        data: $("#myForm").serialize(),//only input
                        showLoader: true,
                        success: function (response) {
                            if (response == 1) {
                                $('.btn-background-cancel').click();
                                $.ajax({
                                    type: "POST",
                                    url: loadCustomerAddressUrl,
                                    dataType: "json",
                                    data: {customer_id: customerIdAddress},//only input
                                    showLoader: true,
                                    success: function (data) {
                                        $('#addressBill').empty();
                                        $('#addressShip').empty();

                                        if (data.html_data !== undefined && data.html_data) {
                                            $('#addressBill').append(data.html_data);
                                            $('#addressShip').append(data.html_data);

                                        } else {
                                            $('#addressBill').append('<option value="">No Address found</option>');
                                            $('#addressShip').append('<option value="">No Address found</option>');
                                        }

                                    }
                                });
                            }
                        }
                    });
                }
            }]
        };

        var optionsAddressAlert = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Action',
            buttons: [{
                text: $.mage.__('Ok'),
                class: 'btn-background-cancel',
                click: function () {
                    this.closeModal();
                }
            }]
        };

        require(['jquery', 'mage/url' ], function ($,url) {

            url.setBaseUrl(BASE_URL);

            $(".sub_btn").click(function () {
                var str = $('#sub_btn').text();

                str = str.toLowerCase().replace(/\b[a-z]/g, function (letter) {
                    return letter.toLowerCase();
                });

                $('#myModelConfirm').modal('openModal');
            });

            $("#address_btn").click(function () {
                var customerId = $('#selectdivCustomer').val();
                if (customerId != null) {
                    $('#customerIdAddress').val(customerId);
                    $("#myForm").trigger("reset");
                    $('#optionsAddress').modal('openModal');
                } else {

                    $('#optionsAddressAlert').html('<div>Please Select Customer First.</div>');
                    $('#optionsAddressAlert').modal('openModal');
                    document.getElementById("selectdivCustomer").style.borderColor = "red";
                }
            });
        });

        var popup = modal(options, $('#myModel'));
        var popupConfirmDel = modal(optionsConfirmDel, $('#myModelConfirmDel'));
        var popupConfirm = modal(optionsConfirm, $('#myModelConfirm'));
        var popupAddress = modal(optionsAddress, $('#optionsAddress'));
        var popupAddressAlert = modal(optionsAddressAlert, $('#optionsAddressAlert'));
    }
);

/* For new payment method */


require(['jquery'], function ($) {
    $('#add-new').hide();
    $('#payop').hide();

    $("#options_add_new").prop('checked', false);

    $('#options_add_new').click(function () {
        if ($(this).prop("checked") == true) {
            $('#add-new').show();
            $('#payop').show();
            $('#selectdivPayment').prop("disabled", true);

        } else if ($(this).prop("checked") == false) {
            $('#add-new').hide();
            $('#add-new-ach').hide();
            $('#payop').hide();
            $('#selectdivPayment').prop("disabled", false);
        }
    });

    $('#start_date').click(function () {
        $("#start_date").attr("autocomplete", "off");
    });

    $('#expire_date').click(function () {
        $("#expire_date").attr("autocomplete", "off");
    });

});

require(['jquery'], function ($) {
    $("#payment_cc_number_new").bind("keypress", function (e) {
        var keyCode = e.which ? e.which : e.keyCode;
        if (!(keyCode >= 48 && keyCode <= 57)) {
            return false;
        } else {
            // nothing
        }
    });
});


    require(['jquery'], function ($) {
        if ($("#requestCC").val() == 1){
        $("#payment_cc_cid_new").bind("keypress", function (e) {
            var keyCode = e.which ? e.which : e.keyCode
            if (!(keyCode >= 48 && keyCode <= 57)) {
                return false;
            }
        });
       }
    });

require(['jquery'], function ($) {
    $("#qty").bind("keypress", function (e) {
        var keyCode = e.which ? e.which : e.keyCode
        if (!(keyCode >= 48 && keyCode <= 57)) {
            return false;
        }
    });
});

require(['jquery'], function ($) {
    if ($('#rec_indefinitely').prop("checked") == true) {
        $('#expire_date').prop("disabled", true);
    }

    $('.save_btn').click(function (e) {
        var days = 0;

        if (!$("#selectdivProduct").val()) {
            $('#product_msg').show();
            do_addcss.focus('product_msg');
            return false;
        } else {
            $('#product_msg').hide();
        }

        if (($("#qty").val() == '') || ($("#qty").val() < 1)) {
            $('#product_qty').show();
            do_addcss.focus('product_qty');
            return false;
        } else {
            $('#product_qty').hide();
        }

        if (!$("#selectdivCustomer").val()) {
            $('#customer_msg').show();
            do_addcss.focus('customer_msg');
            return false;
        } else {
            $('#customer_msg').hide();
        }

        if (($("#selectdivPayment").val() == '') && ($("#selectdivPayment").prop('disabled') == false)) {
            $('#payment_empty').show();
            do_addcss.focus('payment_empty');
            return false;
        } else {
            $('#payment_empty').hide();
        }

        if($('#shippingMethod').val() == '') {
            $('#shipping_empty').show();
            do_addcss.focus('shipping_empty');
            return false;
        } else {
            $('#shipping_empty').hide();
        }
        var cond;
        if ($("#requestCC").val() == 1) {
          cond = ($("#payment_cc_cid_new").val() == '')
        } else {
          cond =true;
        }

        if ($('#options_add_new').prop("checked") == true && $('#ebiz_option').val() == 'credit_card') {
            if (
                ($("#payment_cc_owner_new").val() == '') ||
                ($("#payment_cc_type_new").val() == '') ||
                ($("#payment_cc_number_new").val() == '') ||
                ($("#payment_exp_new").val() == '') ||
                ($("#payment_exp_yr_new").val() == '')

        || ($("#payment_avs_street_new").val() == '')
            || ($("#payment_avs_zip_new").val() == '') || cond
        ) {
                $('#payment_msg').show();
                do_addcss.focus('payment_msg');
                return false;
            } else {
                $('#payment_msg').hide();
                //return true;
            }

            // check for credit card number
            var numLength = $("#payment_cc_number_new").val().length;

            if (numLength != 16) {
                $('#payment_msgcNum').show();
                do_addcss.focus('payment_msgcNum');
                return false;
            } else {
                $('#payment_msgcNum').hide();
            }
             if ($("#requestCC").val() == 1) {
                // check for CVV
                var cvvLength = $("#payment_cc_cid_new").val().length;
                if (cvvLength < 3 || cvvLength > 4) {
                    $('#payment_msgCvv').show();
                    do_addcss.focus('payment_msgCvv');
                    return false;
                } else {
                    $('#payment_msgCvv').hide();
                }
             }
        }



        if ($('#options_add_new').prop("checked") == true && $('#ebiz_option').val() == 'ACH') {
            if (
                ($("#payment_cc_owner_new_ach").val() == '') ||
                ($("#payment_cc_type_new_ach").val() == '') ||
                ($("#payment_cc_number_new_ach").val() == '') ||
                ($("#payment_cc_routing_new_ach").val() == '')

            ) {
                $('#payment_msg_ach').show();
                do_addcss.focus('payment_msg_ach');
                return false;

            } else {
                $('#payment_msg_ach').hide();
            }

            var numLength = $("#payment_cc_number_new_ach").val().length;

            if (numLength < 9 || numLength > 14) {
                $('#payment_msgcNumAch').show();
                do_addcss.focus('payment_msgcNumAch');
                return false;

            } else {
                $('#payment_msgcNumAch').hide();
            }
            var numLengthRout = $("#payment_cc_cid_new_ach").val().length;
            if (numLengthRout != 9) {
                $('#payment_msgcNumRoutAch').show();
                do_addcss.focus('payment_msgcNumRoutAch');
                return false;

            } else {
                $('#payment_msgcNumRoutAch').hide();
            }
        }

        if ($('#rec_indefinitely').prop("checked") == false) {

            var frequency = $("#freqId").val();
            var sdate = $("#start_date").val();
            var edate = $("#expire_date").val();

            switch (frequency) {
                case "daily":
                    days = 1;
                    break;
                case "weekly":
                    days = 7;
                    break;
                case "bi-weekly":
                case "bi-monthly":
                    days = 14;
                    break;
                case "four-week":
                    days = 28;
                    break;
                case "monthly":
                    days = 30;
                    break;
                case "two-month":
                    days = 60;
                    break;
                case "quarterly":
                case "three-month":
                case "90-days":
                    days = 90;
                    break;
                case "four-month":
                    days = 120;
                    break;
                case "five-month":
                    days = 150;
                    break;
                case "bi-annually":
                case "six-month":
                case "180-days":
                    days = 180;
                    break;
                case "annually":
                    days = 365;
                    break;
                default:
                    days = 30;
            }

            var frequencyDays = days;
            const startDate = sdate;
            const endDate = edate;

            const diffInMs = new Date(endDate) - new Date(startDate)
            const diffInDays = diffInMs / (1000 * 60 * 60 * 24);

            if ($("#freqId").val() == '') {
                $('#Frecheck').html("<div>Please select frequency.</div>");
            } else if ($("#start_date").val() == '') {
                $('#Frecheck').html("<div>Please select valid start date.</div>");
            } else if (Math.round(diffInDays) < Math.round(frequencyDays)) {
                $('#Frecheck').html("<div>Please select valid dates for (" + frequency + ") frequency.</div>");

            } else {
                $('body').trigger('processStart');
                $('#form-validate').submit();
            }
        } else {
            $('body').trigger('processStart');
            $('#form-validate').submit();
        }

    });

});

require([
    'jquery',
    'mage/mage',
    'mage/calendar'
], function ($) {
    let minDate = new Date();
    minDate.setDate(minDate.getDate() + 1);

    $('#dates').dateRange({
        buttonText: 'Select Date',
        dateFormat: 'Y-mm-dd',
        minDate: minDate,
        from: {
            id: 'start_date'
        },
        to: {
            id: 'expire_date'
        }
    });

    $('#rec_indefinitely').click(function () {
        if ($(this).prop("checked") == true) {
            $('#expire_date').prop("disabled", true);
        } else if ($(this).prop("checked") == false) {
            $('#expire_date').prop("disabled", false);
        }
    });

    $('#selectdivPayment').change(function () {
        $('#payment_method_name').val($('#selectdivPayment option:selected').text());
    });

});

require(['jquery', 'chosen'], function ($) {
    $("#selectdivCustomer").change(function () {
        var customerId = $("#selectdivCustomer").val();
        var loadCustomerUrl = $('#loadCustomerUrl').val();
        var loadCustomerAddressUrl = $('#loadCustomerAddressUrl').val();

        $.ajax({
            method: "POST",
            url: loadCustomerUrl,
            data: {customer_id: customerId},
            dataType: "json",
            showLoader: true,
            success: function (data) {
                $('#selectdivPayment').empty();

                if (data.html_data !== undefined && data.html_data) {
                    $('#selectdivPayment').append(data.html_data);
                    $('#payment_method_name').val($('#selectdivPayment option:selected').text());
                } else {
                    $('#selectdivPayment').append('<option value="">No payment method found</option>');
                }
            },
            complete: function () {
            },
            error: function (result) {
                $('#selectdivPayment').empty();
            }
        });

        $.ajax({
            type: "POST",
            url: loadCustomerAddressUrl,
            dataType: "json",
            data: {customer_id: customerId},//only input
            showLoader: true,
            success: function (data) {
                $('#addressBill').empty();
                $('#addressShip').empty();
                if (data.html_data !== undefined && data.html_data) {
                    $('#addressBill').append(data.html_data);
                    $('#addressBill').trigger("chosen:updated");
                    $('#addressShip').append(data.html_data);
                    $('#addressShip').trigger("chosen:updated");

                } else {
                    $('#addressBill').append('<option value="">No Address found</option>');
                    $('#addressBill').trigger("chosen:updated");
                    $('#addressShip').append('<option value="">No Address found</option>');
                    $('#addressShip').trigger("chosen:updated");
                }
            }
        });
    });
});

require(['jquery'], function (jQuery) {
    do_addcss = {
        focus: function (id) {
            //id
            jQuery('html, body').animate({
                scrollTop: jQuery("#" + id).offset().top
            }, 100);
        }
    }
});

require(['jquery'], function ($) {
    $('.ebizs_option').on('click ready change', function () {

        if (this.value == 'credit_card') {
            $('#add-new').show();
            $('#add-new-ach').hide();
            $('#ebiz_option').val('credit_card');

        } else if (this.value == 'ACH') {
            $('#add-new-ach :input').removeClass("required-entry");
            $('#add-new').hide();
            $('#add-new-ach').show();
            $('#ebiz_option').val('ACH');
        }
    });
});
function goBack() {
    window.history.back();
}
