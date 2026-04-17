<?php

/// Register WOF post type
function wof_register_post_types() {
	if ( class_exists( 'WOF' ) ) {
		WOF::register_post_type();
		return true;
	} else {
		return false;
	}
}