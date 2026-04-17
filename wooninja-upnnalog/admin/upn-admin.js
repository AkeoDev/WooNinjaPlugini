jQuery(document).ready(function($) {
    //jQuery("input[name='payment_option']").change(function() {
 var paymentOption = jQuery("input[name='payment_option']:checked").val();
// Set initial form visibility based on selected payment option
if (paymentOption == 'upay') {
    jQuery('#wrap-upay-data').show();
    jQuery('#form-table_upn').hide();
    jQuery('#form-table-upay').show();
    jQuery('.upay-button').show();
    jQuery('.upn-button').hide();
    jQuery('.title-upn').hide();
    jQuery('.title-upay').show();

} else if (paymentOption == 'default_upn') {
    jQuery('#wrap-upay-data').hide();
    jQuery('#form-table_upn').show();
    jQuery('#form-table-upay').hide();
    jQuery('.upay-button').hide();
    jQuery('.upn-button').show();
    jQuery('.title-upn').show();
    jQuery('.title-upay').hide();
    
} 
//});
    $("input[name='payment_option']").on('change', function(e) {
        var paymentOption = $(this).val();
        //jQuery('#wrap-upay-data').show();
        $.ajax({
            url: ajax_call.ajaxurl,
            type: 'POST',
            data: {
                'action': 'save_payment_option',
                'payment_option': paymentOption,
                'command': 'shrani-metodo',
                'security': ajax_call.nonce
            },
            beforeSend: function(){
                // Show image container
                $("#loader").show();
                jQuery('#form-table-upay').addClass('upn_overlay')
            },
            success: function(response) {
                // handle success
                $("#loader").hide();
                jQuery('#form-table-upay').removeClass('upn_overlay');
                jQuery('#form-table_upn').hide();
                if (paymentOption == 'upay') {
                
                    jQuery('#wrap-upay-data').show();
                    jQuery('#form-table_upn').hide();
                    jQuery('#form-table-upay').show();
                    jQuery('.upay-button').show();						
                    jQuery('.upn-button').hide();
                  } else if (paymentOption == 'default_upn') {
                    jQuery('#wrap-upay-data').hide();
                    jQuery('#form-table_upn').show();
                    jQuery('.upn-button').show();
                    jQuery('#form-table-upay').hide();
                    jQuery('.upay-button').hide();

                  }
                console.log(response);
            },
            error: function(error){
                // handle error
                console.log(error);
            }
        });
    });
    // Change uPay type
    var checkTypeConnection = jQuery("input[name='uPayType']:checked").val();

    if (checkTypeConnection == 'uPayLive') {
        jQuery("#uPayTestConnection").hide();
        jQuery("#uPayLiveConnection").show();
    } else if (checkTypeConnection == 'uPayTest') {
        jQuery("#uPayLiveConnection").hide();
        jQuery("#uPayTestConnection").show();
    }
    $("input[name='uPayType']").on('change', function(e) {
        var paymentOptionType = $(this).val();
        $.ajax({
            url: ajax_call.ajaxurl,
            type: 'POST',
            data: {
                'action' : 'upay_connection',
                'uPayType' : paymentOptionType,
                'command' : 'shrani-api-podatki',
                'security': ajax_call.nonce
            },
            beforeSend: function() {
                $('#loader').show();
                jQuery('#uPayFormType').addClass('upn_overlay');
            },
            success: function(response) {
                $('#loader').hide();
                jQuery('#form-table-upay').removeClass('upn_overlay');

                if (paymentOptionType == 'uPayLive') {

                    jQuery("#uPayTestConnection").hide();
                    jQuery("#uPayLiveConnection").show();

                } else if (paymentOptionType == 'uPayTest') {

                    jQuery("#uPayLiveConnection").hide();
                    jQuery("#uPayTestConnection").show();

                }
            }
        })
    })
});

