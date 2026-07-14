<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
$settings = get_option( 'lhiwc_settings', array() );
if ( ! is_array( $settings ) || 'yes' !== ( $settings['remove_data'] ?? 'no' ) ) { return; }
delete_option( 'lhiwc_settings' );
delete_option( 'lhiwc_number_year' );
delete_option( 'lhiwc_number_lock' );
if ( class_exists( 'WooCommerce' ) && function_exists( 'wc_get_orders' ) ) {
	$page = 1;
	do {
		$orders = wc_get_orders( array( 'limit' => 100, 'page' => $page, 'return' => 'objects', 'paginate' => false ) );
		foreach ( $orders as $order ) { $order->delete_meta_data( '_lhiwc_invoice_number' ); $order->delete_meta_data( '_lhiwc_invoice_date' ); $order->save(); }
		++$page;
	} while ( count( $orders ) === 100 );
}

