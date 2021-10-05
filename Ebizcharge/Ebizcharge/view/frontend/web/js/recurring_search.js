require([
    "jquery"
], function($){
//<![CDATA[
    $(document).ready(function() {
        //console.log('jquery loaded from custom method by rizwan test');
    });
//]]>
});

require([
    'jquery',
     'mage/url'
    ],
    function ($, url) {
        url.setBaseUrl(BASE_URL);
        $(".print_btn").click(function () {
            var tid = $(this).attr('id');
            var receiptRefNum = $('#receiptRefNum').val();

            $.ajax({
                method: "POST",
                url: url.build('ebizcharge/recurrings/printaction'),
                data: {
                    tid: tid,
                    rid: receiptRefNum
                },
                dataType: "json",
                showLoader: true,
                success: function (data) {
                    if (data.html_data !== undefined) {
                        var newWin = window.open('', '_blank', 'width=400,height=400');
                        newWin.document.open();
                        newWin.document.write('<html><body onload="window.print()">' + atob(data.html_data) + '</html>');
                        newWin.print();
                        newWin.close();
                        //$.ajaxQ.abortAll();
                    }
                },
                error: function (result) {
                    alert("No response from WSDL");
                }
            })
        })
        $(".email_btn").click(function () {


            var tid = $(this).attr('id');
            var email = $(this).attr('data-id');
            var receiptRefNum = $('#receiptRefNum').val();

            $.ajax({
                method: "POST",
                url: url.build('ebizcharge/recurrings/emailaction'),
                data: {
                    tid: tid,
                    rid: receiptRefNum,
                    email: email
                },
                dataType: "json",
                showLoader: true,
                success: function (data) {
                    if (data.html_data !== undefined) {
                        //alert(data.html_data);
                        if (data.html_data == 1) {
                            $('#msg').html("<div>Email sent!</div>");
                            $('#msg').addClass("message-success success message");
                        } else {
                            $('#msg').html("<div>Email not sent!</div>");
                            $('#msg').addClass("message-error error message");
                        }
                    }
                },
                error: function (result) {
                    alert("No response from WSDL");
                }
            })
        });
    });
require(['jquery'], function ($) {
        $("#searchIn").on("keyup", function () {
            var value = $(this).val().toLowerCase();
            $("#myBody tr").filter(function () {
                $(this).toggle(parseInt($(this).text().toLowerCase().indexOf(value)) > -1)
            });
        });
    });
require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal'
    ],
    function ($, modal) {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Delete',
            buttons: [{
                text: $.mage.__('OK'),
                class: '',
                click: function () {
                    this.closeModal();
                }
            }]


        };
        var optionsConfirm = {
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
                    $('#reclist').submit();


                }
            }]
        };

        var popup = modal(options, $('#myModel'));
        var popupConfirm = modal(optionsConfirm, $('#myModelConfirm'));

    }
);
require(['jquery'], function ($) {

    $(".del_btn").click(function () {
        var ids = [];
        if (($('[name="del_id"]:checked').length > 0)) {
            $.each($("input[name='del_id']:checked"), function () {
                ids.push($(this).val());

            });

            $('#internal_id').val(ids.join(", "));
            $('#myModelConfirm').html('<div>Are you sure you want to delete subscription(s)?</div>');
            $('#myModelConfirm').modal('openModal');

            //$('#reclist').submit();
        } else {
            $('#myModel').html('<div>Please select at least one subscription to delete.</div>');
            $('#myModel').modal('openModal');

        }
    });


});
function openPage(url) {
        if (url != "")
            window.location = url;
    }
