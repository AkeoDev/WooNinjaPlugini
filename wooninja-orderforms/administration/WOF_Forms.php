<?php

if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class WOF_Forms extends WP_List_Table {

	public static function columns() {
		$columns = array(

			'title' => __( 'Title', 'wof-sm'  ),
			'shortcode' => __( 'Shortcode', 'wof-sm'  ),
			'author' => __( 'Author', 'wof-sm'  ),

		);
		return $columns;
	}

	function __construct() {
		parent::__construct( array(
			'singular' => 'post',
			'plural' => 'posts',
			'ajax' => false ) );
	}

	function prepare_items() {
		$current_screen = get_current_screen();
		$per_page = 10;

		$columns = $this->columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);

		$args = array(
			'posts_per_page' => $per_page,
			'orderby' => 'title',
			'order' => 'ASC',
			'offset' => ( $this->get_pagenum() - 1 ) * $per_page );

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			if ( 'title' == $_REQUEST['orderby'] )
				$args['orderby'] = 'title';
			elseif ( 'author' == $_REQUEST['orderby'] )
				$args['orderby'] = 'author';
			elseif ( 'date' == $_REQUEST['orderby'] )
				$args['orderby'] = 'date';
		}

		if ( ! empty( $_REQUEST['order'] ) ) {
			if ( 'asc' == strtolower( $_REQUEST['order'] ) )
				$args['order'] = 'ASC';
			elseif ( 'desc' == strtolower( $_REQUEST['order'] ) )
				$args['order'] = 'DESC';
		}

		$this->items = Wof::fetch_data( $args );

		$total_items = Wof::counter();
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page ) );
	}

	function get_columns() {
		return get_column_headers( get_current_screen() );
	}


	function column_default( $item, $column_name ) {
		return '';
	}

	function column_title( $item ) {

		$url = admin_url( 'admin.php?page=wof&post=' . absint( $item->id() ) );
		$edit_link = add_query_arg( array( 'action' => 'edit' ), $url );

		$actions = array(
			'edit' => sprintf( '<a href="%1$s">%2$s</a>',
				esc_url( $edit_link ),
				esc_html( __( 'Edit', 'wof-sm'  ) ) ) );


		$a = sprintf( '<a class="row-title" href="%1$s" title="%2$s">%3$s</a>',
			esc_url( $edit_link ),
			esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'wof-sm'  ),
				$item->title() ) ),
			esc_html( $item->title() ) );

		return '<strong>' . $a . '</strong> ' . $this->row_actions( $actions );
	}

	function column_author( $item ) {
		$post = get_post( $item->id() );
		$avtor = get_userdata( $post->post_author );
		return esc_html( $avtor->display_name );
	}

	function column_shortcode( $item ) {
		$shortcodes = array( $item->get_shortcode() );
		$output = '';
		foreach ( $shortcodes as $shortcode ) {
			$output .= "\n" . '<span class="shortcode"><input type="text"'
				. ' onfocus="this.select();" readonly="readonly"'
				. ' value="' . esc_attr( $shortcode ) . '"'
				. ' class="large-text code" /></span>';
		}
		return trim( $output );
	}

}