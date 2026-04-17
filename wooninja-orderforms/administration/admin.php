<?php

add_action( 'admin_menu', 'wof_administration', 9 );

function wof_administration() {
	add_menu_page( __( 
		'Order Forms', 'wof-sm' ),
		__( 'Order Forms', 'wof-sm' ),
		'edit_posts', 'wof',
		'wof_list_data', 'dashicons-feedback' );

	$save = add_submenu_page( 
		'wof',
		__( 'Edit Order Form', 'wof-sm' ),
		__( 'Order Forms', 'wof-sm' ),
		'edit_posts', 'wof',
		'wof_list_data' );

	add_action( 'load-' . $save, 'wof_load_admin' );

	$create = add_submenu_page( 
		'wof',
		__( 'Add New Order Form', 'wof-sm' ),
		__( 'Add New', 'wof-sm' ),
		'edit_posts', 'wof--create',
		'wof_create_new' );

	add_action( 'load-' . $create, 'wof_load_admin' );

}

function wof_list_data() {

	if ( isset( $_GET["post"] ) ) {
		require_once WOF_PLUGIN_DIR . '/administration/edit-form.php';
		return;
	}

	require_once WOF_PLUGIN_DIR . '/administration/WOF_Forms.php';
?>


	<div class="wrap wrap-spletni-moduli">

	<h1><?php
		echo esc_html( __( 'Order Forms', 'wof-sm' ) );
		echo ' <a href="' . esc_url( menu_page_url( 'wof--create', false ) ) . '" class="add-new-h2">' . esc_html( __( 'Add New Order Form', 'wof-sm' ) ) . '</a>';
	?></h1>

	<?php
		$data = new WOF_Forms();
		$data->prepare_items();
	?>

	<form method="get" action="">
		<?php $data->display(); ?>
		<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
	</form>

	</div>



<?php
}


function wof_create_new() {

	require_once WOF_PLUGIN_DIR . '/administration/create-new.php';

}

function wof_load_admin() {
	global $plugin_page;
	$command = null;

	if ( isset( $_REQUEST["command"] ) ) {
		$command = $_REQUEST["command"];
	}



	if ( $command == "create" ) {
		/// Verify nonce
		$nonce = $_REQUEST['nonce'];

		if ( ! wp_verify_nonce( $nonce, 'wof_create' ) ) {
		     die( 'Security check' ); 

		} else {
			$insert = WOF::create_form( );	
		}

	}




	if ( $command == "save" ) {
		/// Verify nonce
		$nonce = $_REQUEST['nonce'];

		if ( ! wp_verify_nonce( $nonce, 'wof_save' ) ) {

		     die( 'Security check' ); 

		} else {
			$post 	= WOF::get_instance( $_POST["form_id"] );	
			$update = WOF::save_form( $post );	
		}

	}



}
