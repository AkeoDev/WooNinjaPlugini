 jQuery(document).ready(function() {


	jQuery(".ime-podjetja").html( jQuery("input[name='upnImePodjetja']").val() );
	jQuery(".naslov-podjetja").html( jQuery("input[name='upnNaslovPodjetja']").val() );
	jQuery(".bic-podjetja").html( jQuery("input[name='upnBic']").val() );
	jQuery(".trr-podjetja").html( jQuery("input[name='upnTRR']").val() );


	jQuery("input[name='upnImePodjetja']").on("input", function() {
		jQuery(".ime-podjetja").html( jQuery(this).val() );
	});



	jQuery("input[name='upnNaslovPodjetja']").on("input", function() {
		jQuery(".naslov-podjetja").html( jQuery(this).val() );
	});



	jQuery("input[name='upnBic']").on("input", function() {
		jQuery(".bic-podjetja").html( jQuery(this).val() );
	});



	jQuery("input[name='upnTRR']").on("input", function() {
		jQuery(".trr-podjetja").html( jQuery(this).val() );
	});

    jQuery('.upn_object').hide();

    // Bind click handlers without using inline JavaScript
    jQuery(document).on('click', '.upn_popup', function() {
        jQuery('.upn_object').show();
    });

    jQuery(document).on('click', '.upn_object', function() {
        jQuery('.upn_object').hide();
    });

}); 
