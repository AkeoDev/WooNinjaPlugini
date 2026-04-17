jQuery( document ).ready( function() {
	/// Woocommerce Order Forms

	/// We have to check if user selected custom redirect for thankyou page
	function checkRedirect() {
		if ( jQuery("#wof_redirect").length > 0 ) {

			if ( jQuery("#wof_redirect").val() == "custom" ) {
				jQuery("#custom_redirect").fadeIn();
			};

			/// Listen if redirect changes
			jQuery("#wof_redirect").on("change", function() {
				console.log("change");
				if ( jQuery(this).val() == "custom" ) {
					jQuery("#custom_redirect").fadeIn();
				} else {
					jQuery("#custom_redirect").fadeOut();
				}
			})

		};
	}

	checkRedirect();

	if ( jQuery(".codemirror").length > 0 ) {

	    var mixedMode = {
	        name: "htmlmixed",
	    };
		HTML = document.getElementsByClassName("codemirror");
		var editor = CodeMirror.fromTextArea(HTML[0], {
		    lineNumbers: true,
		    mode: mixedMode,
	 	    viewportMargin: Infinity,
		    styleActiveLine: true,
		    matchBrackets: true
		});	

		CSS = document.getElementsByClassName("codemirror_css");
		var editor = CodeMirror.fromTextArea(CSS[0], {
		    lineNumbers: true,
		    mode: {
		    	name: "css"
		    },
	 	    viewportMargin: Infinity,
		    styleActiveLine: true,
		    matchBrackets: true
		});

	};

});