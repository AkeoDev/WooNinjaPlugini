<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $woocommerce;
global $plugin_page;

$pending_orders = wc_get_orders( array(
	'status'     => 'any',
	'limit'      => 1,
	'meta_query' => array(
		'relation' => 'AND',
		array(
			'key'     => 'sprejemna_stevilka',
			'compare' => 'EXISTS',
		),
		array(
			'key'     => 'order_posta',
			'value'   => 'DA',
			'compare' => '=',
		),
		array(
			'key'     => 'espremnica_odposlano',
			'compare' => 'NOT EXISTS',
		),
	),
	'orderby'  => 'meta_value_num',
	'meta_key' => 'sprejemna_stevilka',
	'order'    => 'ASC',
) );

if ( ! empty( $pending_orders ) ) {
	?>
	<br>
	<a href="" class="espremnica_send_to_api button" style="text-align:center;">
		<?php esc_html_e( 'Pošlji pošiljke preko API-ja', 'wooninja-espremnica' ); ?>
	</a>
	<div class="ajax-loader-espremnica-csv" style="text-align:center; display:none;">
		<img src="<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'inc/img/ajax-loader.gif' ); ?>" alt="">
	</div>
	<br>
	<br>
<?php
}

$all_orders = wc_get_orders( array(
	'status'     => 'any',
	'limit'      => -1,
	'meta_key'   => 'sprejemna_stevilka',
	'orderby'    => 'meta_value_num',
	'order'      => 'DESC',
) );

if ( ! empty( $all_orders ) ) { ?>
	<table border="1" cellpadding="2" cellspacing="0">
		<tr style="color:#000;background:#ffd541;font-size:16px;height:34px;">
			<th colspan="8"><?php esc_html_e( 'Pregled pošiljk', 'wooninja-espremnica' ); ?></th>
		</tr>
		<tr style="background:#eee;font-weight:600;">
			<td><?php esc_html_e( 'ID naročila', 'wooninja-espremnica' ); ?></td>
			<td><?php esc_html_e( 'Ustvarjeno', 'wooninja-espremnica' ); ?></td>
			<td><?php esc_html_e( 'Zadnja sprememba', 'wooninja-espremnica' ); ?></td>
			<td><?php esc_html_e( 'Plačano', 'wooninja-espremnica' ); ?></td>
			<td><?php esc_html_e( 'Znesek', 'wooninja-espremnica' ); ?></td>
			<td><?php esc_html_e( 'Način plačila', 'wooninja-espremnica' ); ?></td>
			<td><?php esc_html_e( 'Sprejemna številka', 'wooninja-espremnica' ); ?></td>
			<td><?php esc_html_e( 'Odposlano', 'wooninja-espremnica' ); ?></td>
		</tr>
	<?php
	foreach ( $all_orders as $order ) {
		$post_id            = $order->get_id();
		$order_created      = $order->get_date_created() !== null ? date_format( $order->get_date_created(), 'j. n. Y' ) : '';
		$order_modified     = $order->get_date_modified() !== null ? date_format( $order->get_date_modified(), 'j. n. Y' ) : '';
		$order_paid         = $order->get_date_paid() !== null ? date_format( $order->get_date_paid(), 'j. n. Y' ) : '';
		$sprejemna_stevilka = $order->get_meta( 'sprejemna_stevilka' );
		$odposlano          = $order->get_meta( 'espremnica_odposlano' ) == 'DA' ? 'DA' : 'NE';

		if ( $order->get_meta( 'espremnica_odposlano' ) == 'DA' ) {
			$yesNoColour = '#2ecc71';
		} else {
			$yesNoColour = '#f95151';
		}
		?>
		<tr style="background:#fff;">
			<td style="background-image:url('<?php echo esc_url( plugin_dir_url( dirname( __FILE__ ) ) . 'inc/img/link.png' ); ?>');background-position:right 3px center;background-repeat:no-repeat;">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-orders&action=edit&id=' . $post_id ) ); ?>" style="display:block;" target="_blank"><?php echo esc_html( $post_id ); ?></a>
			</td>
			<td><?php echo esc_html( $order_created ); ?></td>
			<td><?php echo esc_html( $order_modified ); ?></td>
			<td><?php echo esc_html( $order_paid ); ?></td>
			<td><?php echo esc_html( GetPostaTotal( $order->get_total() ) ); ?></td>
			<td><?php echo esc_html( $order->get_payment_method_title() ); ?></td>
			<td><?php echo esc_html( $sprejemna_stevilka ); ?></td>
			<td style="background-color:<?php echo esc_attr( $yesNoColour ); ?>;"><?php echo esc_html( $odposlano ); ?></td>
		</tr>
		<?php
	}
	?>
	</table>
	<?php
}
