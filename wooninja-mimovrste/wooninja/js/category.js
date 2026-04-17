jQuery(document).ready(function($) {

    $('#mimovrste_category').select2({
        placeholder: "Išči kategorijo...",
        allowClear: true,
        width: '100%'
    });
    
    $('#mimovrste_category').change(function() {

        var categoryId = $(this).val();
        var data = {
            'action': 'fetch_category_parameters',
            'categoryId': categoryId,
            'nonce': my_ajax_object.nonce
        };

        // Show preloader
        $('#parameters_preloader').show();

        $.post(my_ajax_object.ajaxurl, data, function(response) {
            // Hide preloader
            $('#parameters_preloader').hide();

            if (response.success) {
                // Insert the HTML into the desired location
                $('#your_params_container').html(response.data);
            } else {
                alert('Error: ' + response.data);
            }
        });
    });
});
