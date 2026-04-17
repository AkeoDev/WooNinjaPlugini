jQuery(document).ready(function($) {
    $('.variations_form').on('change', 'select', function() {
        var attribute_value = $(this).val();
        var attribute_name = $(this).attr('name');
        var product_id = $('.variations_form').data('product_id');
        $.ajax({
            url: message_display_lowest_price.ajax_url,
            type: 'POST',
            data: {
                action: 'get_lowest_price',
                attribute_name: attribute_name,
                attribute_value: attribute_value,
                product_id: product_id,
            },
            success: function(response) {
                $("#custom-message").html(response);
            }
        });
    });

});