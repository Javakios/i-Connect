jQuery(document).ready(function ($) {
    $('#new-request-form').submit(function (e) {
        e.preventDefault();
        var request_name = $('#request_name').val();
        var service = $('#service').val();
        var sqlName = $('#sqlName').val();
        var request_type = $('#request_type').val();
        var security = $('#_wpnonce').val(); // Get the nonce value
        console.log(request_type);
        $.ajax({
            url: ajax_object.ajax_url,
            type: 'post',
            data: {
                action: 'create_new_request_type',
                request_name: request_name,
                service: service,
                sqlName: sqlName,
                request_type:request_type,
                security: security // Include the nonce in the data sent
            },
            success: function (response) {
                console.log(response);
            }
        });
    });
});
