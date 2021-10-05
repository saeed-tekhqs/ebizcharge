require([
    "jquery"
], function($){
//<![CDATA[
    $(document).ready(function() {
        console.log('jquery loaded from custom method by rizwan recur edit');
    });
//]]>
});
require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function ($, modal) {
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
                text: $.mage.__('Yes, Update'),
                class: 'btn-background-del',
                click: function () {
                    $('#form-validate').submit();
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
        require(['jquery'], function ($) {

            $(".sub_btn").click(function () {
                var str = $('#sub_btn').text();

                str = str.toLowerCase().replace(/\b[a-z]/g, function (letter) {
                    return letter.toLowerCase();
                });

                $('#myModelConfirm').html('<div>Are you sure you want to' + str + ' to this product?</div>');
                $('#myModelConfirm').modal('openModal');
            });

        });
        var popup = modal(options, $('#myModel'));
        var popupConfirm = modal(optionsConfirm, $('#myModelConfirm'));
    }
);

require(['jquery'], function ($) {
    if ($('#rec_indefinitely').prop("checked") == true) {
        //console.log("Indefinitely checkbox is checked.");
        $('#expire_date').prop("disabled", true);
    }

    $('.save_btn').click(function (e) {
        var days = 0;

        if ($('#options_add_new').prop("checked") == true) {
            if (
                ($("#payment_cc_owner_new").val() == '') ||
                ($("#payment_cc_type_new").val() == '') ||
                ($("#payment_cc_number_new").val() == '') ||
                ($("#payment_exp_new").val() == '') ||
                ($("#payment_exp_yr_new").val() == '') ||
                ($("#payment_cc_cid_new").val() == '')
            ) {
                $('#payment_msg').show();
                return false;
            } else {
                $('#payment_msg').hide();
            }
            // check for credit card number
            var numLength = $("#payment_cc_number_new").val().length;

            if (numLength != 16) {
                $('#payment_msgcNum').show();
                event.preventDefault();
                return false;
            } else {
                $('#payment_msgcNum').hide();
            }
            // check for CVV
            var cvvLength = $("#payment_cc_cid_new").val().length;

            if (cvvLength < 3 || cvvLength > 4) {
                $('#payment_msgCvv').show();
                event.preventDefault();
                return false;
            } else {
                $('#payment_msgCvv').hide();
            }
        }

        if ($('#rec_indefinitely').prop("checked") == false) {
            var frequency = $(".selectdiv").val();
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

            if (Math.round(diffInDays) < Math.round(frequencyDays)) {
                $('#Frecheck').html("<div>Please select valid dates for (" + frequency + ") frequency.</div>");

            } else {

                $('#Frecheck').html("");
                var str = $('#sub_btn').text();

                $('#myModel').html('<div>Do you want to update the changes you made to this subscription?</div>');
                $('#myModel').modal('openModal');

            }
        } else {
            $('#Frecheck').html("");
            var str = $('#sub_btn').text();

            $('#myModel').html('<div>Do you want to update the changes you made to this subscription?</div>');
            $('#myModel').modal('openModal');

        }
    });
});

// For date selection
require([
    'jquery',
    'mage/mage',
    'mage/calendar'
], function ($) {

    let minDate = new Date();
    minDate.setDate(minDate.getDate() + 1);

    $('#dates').dateRange({
        buttonText: '',
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
            //console.log("Indefinitely checkbox is checked.");
            $('#expire_date').prop("disabled", true);
        } else if ($(this).prop("checked") == false) {
            //console.log("Indefinitely checkbox is unchecked.");
            $('#expire_date').prop("disabled", false);
        }
    });

    $('#start_date').click(function () {
        $("#start_date").attr("autocomplete", "off");
    });

    $('#expire_date').click(function () {
        $("#expire_date").attr("autocomplete", "off");
    });
});

/* For ne payment method */
require(['jquery', 'domReady!'], function ($) {
    $('#add-new').hide();
    $("#options_add_new").attr('checked', false);
    //let ischeckBoxChecked = $('#options_rec_activate').is(":checked");

    $('#options_add_new').click(function () {
        if ($(this).prop("checked") == true) {
            $('#add-new').show();
            $('#selectdivPayment').prop("disabled", true);
        } else if ($(this).prop("checked") == false) {
            $('#add-new').hide();
            $('#selectdivPayment').prop("disabled", false);
        }
    });
    // set payment method name on ready
    $('#payment_method_name').val($('#selectdivPayment option:selected').text());

    $('#selectdivPayment').change(function () {
        $('#payment_method_name').val($('#selectdivPayment option:selected').text());
    });

});

require(['jquery'], function ($) {
    $("#payment_cc_number_new").bind("keypress", function (e) {
        var keyCode = e.which ? e.which : e.keyCode

        if (!(keyCode >= 48 && keyCode <= 57)) {
            event.preventDefault();
            return false;
        }
    });
});

require(['jquery'], function ($) {
    $("#payment_cc_cid_new").bind("keypress", function (e) {
        var keyCode = e.which ? e.which : e.keyCode
        if (!(keyCode >= 48 && keyCode <= 57)) {
            event.preventDefault();
            return false;
        }
    });
});
function unsub() {
    document.getElementById('unsub').submit();
}
