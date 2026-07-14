<?php
defined( 'ABSPATH' ) || exit;

final class LHIWC_Documents {
	private $templates = array( 'invoice', 'packing-slip', 'header', 'addresses', 'product-table', 'totals', 'footer' );

	public function render_page( $orders, $type, $print_allowed = true ) {
		nocache_headers();
		header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
		header( 'Referrer-Policy: same-origin', true );
		$prefix      = 'invoice' === $type ? 'invoice' : 'packing';
		$size        = LHIWC_Settings::get( $prefix . '_size' );
		$orientation = LHIWC_Settings::get( $prefix . '_orientation' );
		$size        = in_array( $size, array( 'a4', 'a5', '6x4' ), true ) ? $size : 'a4';
		$orientation = in_array( $orientation, array( 'portrait', 'landscape' ), true ) ? $orientation : 'portrait';
		?><!doctype html><html <?php language_attributes(); ?>><head><meta charset="<?php bloginfo( 'charset' ); ?>"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?php echo esc_html( $this->title( $type ) ); ?></title><link rel="stylesheet" href="<?php echo esc_url( LHIWC_URL . 'assets/css/documents.css?ver=' . LHIWC_VERSION ); ?>"><style><?php echo esc_html( $this->page_rule( $size, $orientation ) ); ?></style></head><body class="lhiwc-size-<?php echo esc_attr( $size ); ?> lhiwc-orientation-<?php echo esc_attr( $orientation ); ?>">
		<div class="lhiwc-controls" role="region" aria-label="<?php esc_attr_e( 'Print controls', 'lightweight-html-invoices-for-woocommerce' ); ?>">
		<?php if ( $print_allowed ) : ?><button type="button" data-lhiwc-print><?php esc_html_e( 'Print', 'lightweight-html-invoices-for-woocommerce' ); ?></button><?php endif; ?>
		<span><?php echo esc_html( strtoupper( $size ) . ' · ' . ucfirst( $orientation ) ); ?></span></div>
		<?php if ( 'yes' === LHIWC_Settings::get( 'print_guidance' ) ) : ?><p class="lhiwc-guidance"><?php esc_html_e( 'Printer and Save as PDF options are controlled by your browser and operating system.', 'lightweight-html-invoices-for-woocommerce' ); ?></p><?php endif; ?>
		<main><?php foreach ( $orders as $order ) { $this->render_document( $order, $type ); } ?></main>
		<script>window.lhiwcPrint=<?php echo wp_json_encode( array( 'auto' => $print_allowed && 'yes' === LHIWC_Settings::get( 'auto_print' ) ) ); ?>;</script><script src="<?php echo esc_url( LHIWC_URL . 'assets/js/print.js?ver=' . LHIWC_VERSION ); ?>"></script></body></html><?php
	}

	public function render_document( $order, $type ) {
		$document = $this->data( $order, $type );
		do_action( 'lhiwc_before_document', $type, $order );
		$this->template( $type, compact( 'order', 'document', 'type' ) );
		do_action( 'lhiwc_after_document', $type, $order );
	}

	public function template( $name, $vars = array() ) {
		if ( ! in_array( $name, $this->templates, true ) ) { return; }
		$theme = trailingslashit( get_stylesheet_directory() ) . 'lightweight-html-invoices/' . $name . '.php';
		$file  = is_readable( $theme ) ? $theme : LHIWC_PATH . 'templates/' . $name . '.php';
		extract( $vars, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		include $file;
	}

	private function data( $order, $type ) {
		$invoice = 'invoice' === $type;
		$date = $order->get_date_created();
		$invoice_time = absint( $order->get_meta( LHIWC_Numbering::META_DATE, true ) );
		return array(
			'title' => apply_filters( 'lhiwc_document_title', $this->title( $type ), $type, $order ),
			'invoice_number' => $invoice ? LHIWC_Numbering::get( $order ) : '',
			'invoice_date' => $invoice_time ? wp_date( LHIWC_Settings::get( 'date_format' ), $invoice_time ) : wp_date( LHIWC_Settings::get( 'date_format' ) ),
			'order_date' => $date ? wc_format_datetime( $date, LHIWC_Settings::get( 'date_format' ) ) : '',
			'logo' => $this->logo(), 'store' => $this->store(),
			'billing' => $this->address( $order, 'billing' ), 'shipping' => $this->address( $order, 'shipping' ),
		);
	}

	private function title( $type ) { return 'invoice' === $type ? __( 'Invoice', 'lightweight-html-invoices-for-woocommerce' ) : __( 'Packing Slip', 'lightweight-html-invoices-for-woocommerce' ); }
	private function page_rule( $size, $orientation ) {
		if ( '6x4' === $size ) { $dimensions = 'landscape' === $orientation ? '6in 4in' : '4in 6in'; $margin = '0.18in'; }
		else { $dimensions = strtoupper( $size ) . ' ' . $orientation; $margin = 'a5' === $size ? '8mm' : '10mm'; }
		return '@page{size:' . $dimensions . ';margin:' . $margin . ';}';
	}
	private function logo() {
		$url = '';
		$id = absint( get_theme_mod( 'custom_logo' ) );
		if ( $id ) { $url = wp_get_attachment_image_url( $id, 'medium' ); }
		if ( ! $url ) { $url = get_site_icon_url( 192 ); }
		return esc_url_raw( apply_filters( 'lhiwc_store_logo_url', $url ) );
	}
	private function store() {
		$country_state = explode( ':', (string) get_option( 'woocommerce_default_country', '' ) );
		$country = $country_state[0] ?? ''; $state = $country_state[1] ?? '';
		$countries = WC()->countries;
		$states = $countries->get_states( $country );
		$states = is_array( $states ) ? $states : array();
		return array( 'name' => get_bloginfo( 'name' ), 'address_1' => get_option( 'woocommerce_store_address' ), 'address_2' => get_option( 'woocommerce_store_address_2' ), 'city' => get_option( 'woocommerce_store_city' ), 'postcode' => get_option( 'woocommerce_store_postcode' ), 'country' => $countries->countries[ $country ] ?? $country, 'state' => $states[ $state ] ?? $state, 'phone' => LHIWC_Settings::get( 'store_phone' ), 'email' => LHIWC_Settings::get( 'store_email' ) );
	}
	private function address( $order, $type ) {
		$raw = 'billing' === $type ? $order->get_address( 'billing' ) : $order->get_address( 'shipping' );
		$formatted = $raw ? WC()->countries->get_formatted_address( $raw, "\n" ) : '';
		$phone = 'billing' === $type ? $order->get_billing_phone() : ( is_callable( array( $order, 'get_shipping_phone' ) ) ? $order->get_shipping_phone() : '' );
		if ( 'shipping' === $type && ! $phone ) { $phone = $order->get_billing_phone(); }
		return array( 'formatted' => $formatted, 'email' => 'billing' === $type ? $order->get_billing_email() : '', 'phone' => $phone );
	}
}
