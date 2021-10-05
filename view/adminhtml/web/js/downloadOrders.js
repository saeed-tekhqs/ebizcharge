require([
    "jquery",
    "mage/url",
    'domReady!'
    ], function($, url){
    function main(config) {
        $(document).ready(function () {
            $('#ebiz-date-picker').hover(function() {
                $(this).attr("autocomplete", "off");
            });
            url.setBaseUrl(BASE_URL);
            let startDate = false;
            $("#ebiz-date-picker").on("change",function(){
                startDate = $(this).val();
            });
            $(document).on('click', '#download-orders', function (e){
                if (!startDate) return false;
                let urlLink = url.build('recurrings/createorderaction');
                $.ajax({
                    showLoader: true,
                    url: urlLink,
                    data: {form_key: window.FORM_KEY, start_date: startDate},
                    type: "POST",
                    dataType: 'json',
                    success: function (data) {
                        location.reload();
                    },
                    error: function (request, status, error) {
                        location.reload();
                    }
                });
            });
        });
    }
return main();
});
