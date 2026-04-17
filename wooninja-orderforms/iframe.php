<?php

require_once('../../../wp-load.php');
require_once('includes/wof.php');

$wof_id = isset( $_GET["wof_id"] ) ? absint( $_GET["wof_id"] ) : 0;
echo do_shortcode( "[wof id='" . $wof_id . "']" );