<?php
/*
*
* prevent directory listing
*
*/
require_once('../../../../wp-load.php');

header("Location: " . get_site_url() );