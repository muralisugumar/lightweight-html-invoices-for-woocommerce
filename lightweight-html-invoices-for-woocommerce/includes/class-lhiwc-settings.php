<?php
defined( 'ABSPATH' ) || exit;

/**
 * Dedicated settings screen under the WooCommerce menu.
 */
final class LHIWC_Settings {
	const OPTION = 'lhiwc_settings';
	const PAGE   = 'lhiwc-settings';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu' ), 60 );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
	}

	public static function add_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Invoices & Packing Slips', 'lightweight-html-invoices-for-woocommerce' ),
			__( 'Invoices & Packing Slips', 'lightweight-html-invoices-for-woocommerce' ),
			'manage_woocommerce',
			self::PAGE,
			array( __CLASS__, 'render' )
		);
	}

	public static function register() {
		register_setting(
			'lhiwc_settings_group',
			self::OPTION,
			array( 'type' => 'array', 'sanitize_callback' => array( __CLASS__, 'sanitize' ), 'default' => self::defaults() )
		);
	}

	public static function enqueue( $hook ) {
		if ( 'woocommerce_page_' . self::PAGE !== $hook ) { return; }
		wp_enqueue_style( 'lhiwc-admin', LHIWC_URL . 'assets/css/admin.css', array(), LHIWC_VERSION );
	}

	public static function defaults() {
		return array(
			'enabled' => 'yes', 'show_logo' => 'yes', 'store_phone' => '', 'store_email' => get_option( 'admin_email' ),
			'tax_number' => '', 'company_number' => '', 'date_format' => get_option( 'date_format' ), 'auto_print' => 'no', 'print_guidance' => 'yes',
			'invoice_enabled' => 'yes', 'invoice_size' => 'a4', 'invoice_orientation' => 'portrait', 'number_method' => 'order',
			'number_prefix' => '', 'number_suffix' => '', 'number_padding' => 5, 'next_number' => 1, 'reset_annually' => 'no',
			'invoice_sku' => 'yes', 'billing_email' => 'yes', 'billing_phone' => 'yes', 'shipping_address' => 'yes',
			'payment_method' => 'yes', 'transaction_id' => 'no', 'tax_breakdown' => 'yes', 'invoice_note' => 'yes', 'invoice_footer' => '',
			'packing_enabled' => 'yes', 'packing_size' => 'a4', 'packing_orientation' => 'portrait', 'packing_logo' => 'yes',
			'packing_billing' => 'no', 'packing_sku' => 'yes', 'packing_phone' => 'yes', 'packing_weight' => 'no', 'packing_thumbnails' => 'no', 'packing_prices' => 'no', 'packing_note' => 'yes', 'packing_footer' => '',
			'customer_invoice' => 'yes', 'customer_invoice_print' => 'yes', 'customer_packing' => 'no',
			'customer_statuses' => array( 'processing', 'completed' ), 'invoice_label' => __( 'View invoice', 'lightweight-html-invoices-for-woocommerce' ),
			'packing_label' => __( 'View packing slip', 'lightweight-html-invoices-for-woocommerce' ), 'bulk_max' => 50,
			'remove_data' => 'no', 'debug' => 'no',
		);
	}

	public static function all() {
		$value = get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $value ) ? $value : array(), self::defaults() );
	}

	public static function get( $key ) {
		$settings = self::all();
		return array_key_exists( $key, $settings ) ? $settings[ $key ] : null;
	}

	public static function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$defaults = self::defaults();
		$out      = array();
		$booleans = array( 'enabled', 'show_logo', 'auto_print', 'print_guidance', 'invoice_enabled', 'reset_annually', 'invoice_sku', 'billing_email', 'billing_phone', 'shipping_address', 'payment_method', 'transaction_id', 'tax_breakdown', 'invoice_note', 'packing_enabled', 'packing_logo', 'packing_billing', 'packing_sku', 'packing_phone', 'packing_weight', 'packing_thumbnails', 'packing_prices', 'packing_note', 'customer_invoice', 'customer_invoice_print', 'customer_packing', 'remove_data', 'debug' );
		foreach ( $booleans as $key ) { $out[ $key ] = isset( $input[ $key ] ) && 'yes' === $input[ $key ] ? 'yes' : 'no'; }
		foreach ( array( 'invoice_size', 'packing_size' ) as $key ) { $out[ $key ] = in_array( $input[ $key ] ?? '', array( 'a4', 'a5', '6x4' ), true ) ? $input[ $key ] : $defaults[ $key ]; }
		foreach ( array( 'invoice_orientation', 'packing_orientation' ) as $key ) { $out[ $key ] = in_array( $input[ $key ] ?? '', array( 'portrait', 'landscape' ), true ) ? $input[ $key ] : 'portrait'; }
		$out['number_method'] = in_array( $input['number_method'] ?? '', array( 'order', 'sequential' ), true ) ? $input['number_method'] : 'order';
		foreach ( array( 'store_phone', 'tax_number', 'company_number', 'date_format', 'number_prefix', 'number_suffix', 'invoice_label', 'packing_label' ) as $key ) { $out[ $key ] = sanitize_text_field( $input[ $key ] ?? '' ); }
		$out['store_email']       = sanitize_email( $input['store_email'] ?? '' );
		$out['invoice_footer']    = wp_kses_post( $input['invoice_footer'] ?? '' );
		$out['packing_footer']    = wp_kses_post( $input['packing_footer'] ?? '' );
		$out['number_padding']    = min( 12, max( 1, absint( $input['number_padding'] ?? 5 ) ) );
		$out['next_number']       = max( 1, absint( $input['next_number'] ?? 1 ) );
		$out['bulk_max']          = min( 200, max( 1, absint( $input['bulk_max'] ?? 50 ) ) );
		$valid                    = array_map( array( __CLASS__, 'strip_status_prefix' ), array_keys( wc_get_order_statuses() ) );
		$out['customer_statuses'] = array_values( array_intersect( array_map( 'sanitize_key', (array) ( $input['customer_statuses'] ?? array() ) ), $valid ) );
		add_settings_error( self::OPTION, 'lhiwc_saved', __( 'Settings saved.', 'lightweight-html-invoices-for-woocommerce' ), 'updated' );
		return $out;
	}

	public static function strip_status_prefix( $status ) { return str_replace( 'wc-', '', $status ); }

	public static function render() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( esc_html__( 'You are not allowed to manage these settings.', 'lightweight-html-invoices-for-woocommerce' ) ); }
		$s = self::all();
		?><div class="wrap lhiwc-settings"><h1><?php esc_html_e( 'Invoices & Packing Slips', 'lightweight-html-invoices-for-woocommerce' ); ?></h1><?php settings_errors( self::OPTION ); ?><form method="post" action="options.php"><?php settings_fields( 'lhiwc_settings_group' ); ?>
		<?php self::section( __( 'General', 'lightweight-html-invoices-for-woocommerce' ) ); self::checkbox( 'enabled', __( 'Enable plugin functionality', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::checkbox( 'show_logo', __( 'Show store logo', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::text( 'store_phone', __( 'Store phone', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::email( 'store_email', __( 'Store email', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::text( 'tax_number', __( 'Tax or GST number', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::text( 'company_number', __( 'Company registration number', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::text( 'date_format', __( 'Date format', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::checkbox( 'auto_print', __( 'Open print dialog automatically', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::checkbox( 'print_guidance', __( 'Show browser-print guidance', 'lightweight-html-invoices-for-woocommerce' ), $s ); ?>
		<?php self::section( __( 'Invoice', 'lightweight-html-invoices-for-woocommerce' ) ); self::checkbox( 'invoice_enabled', __( 'Enable invoices', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::paper_fields( 'invoice', $s ); self::select( 'number_method', __( 'Invoice numbering', 'lightweight-html-invoices-for-woocommerce' ), array( 'order' => __( 'Use order number', 'lightweight-html-invoices-for-woocommerce' ), 'sequential' => __( 'Sequential', 'lightweight-html-invoices-for-woocommerce' ) ), $s ); self::text( 'number_prefix', __( 'Prefix', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::text( 'number_suffix', __( 'Suffix', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::number( 'number_padding', __( 'Minimum number padding', 'lightweight-html-invoices-for-woocommerce' ), $s, 1, 12 ); self::number( 'next_number', __( 'Next invoice number', 'lightweight-html-invoices-for-woocommerce' ), $s, 1 ); self::checkbox( 'reset_annually', __( 'Reset numbering annually', 'lightweight-html-invoices-for-woocommerce' ), $s ); foreach ( array( 'invoice_sku' => __( 'Show SKU', 'lightweight-html-invoices-for-woocommerce' ), 'billing_email' => __( 'Show billing email', 'lightweight-html-invoices-for-woocommerce' ), 'billing_phone' => __( 'Show billing phone', 'lightweight-html-invoices-for-woocommerce' ), 'shipping_address' => __( 'Show shipping address', 'lightweight-html-invoices-for-woocommerce' ), 'payment_method' => __( 'Show payment method', 'lightweight-html-invoices-for-woocommerce' ), 'transaction_id' => __( 'Show transaction ID', 'lightweight-html-invoices-for-woocommerce' ), 'tax_breakdown' => __( 'Show tax breakdown', 'lightweight-html-invoices-for-woocommerce' ), 'invoice_note' => __( 'Show customer note', 'lightweight-html-invoices-for-woocommerce' ) ) as $k => $label ) { self::checkbox( $k, $label, $s ); } self::textarea( 'invoice_footer', __( 'Invoice footer', 'lightweight-html-invoices-for-woocommerce' ), $s ); ?>
		<?php self::section( __( 'Packing Slip', 'lightweight-html-invoices-for-woocommerce' ) ); self::checkbox( 'packing_enabled', __( 'Enable packing slips', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::paper_fields( 'packing', $s ); foreach ( array( 'packing_logo' => __( 'Show logo', 'lightweight-html-invoices-for-woocommerce' ), 'packing_billing' => __( 'Show billing address', 'lightweight-html-invoices-for-woocommerce' ), 'packing_sku' => __( 'Show SKU', 'lightweight-html-invoices-for-woocommerce' ), 'packing_phone' => __( 'Show customer mobile number', 'lightweight-html-invoices-for-woocommerce' ), 'packing_weight' => __( 'Show product weight', 'lightweight-html-invoices-for-woocommerce' ), 'packing_thumbnails' => __( 'Show product thumbnails', 'lightweight-html-invoices-for-woocommerce' ), 'packing_prices' => __( 'Show prices', 'lightweight-html-invoices-for-woocommerce' ), 'packing_note' => __( 'Show customer note', 'lightweight-html-invoices-for-woocommerce' ) ) as $k => $label ) { self::checkbox( $k, $label, $s ); } self::textarea( 'packing_footer', __( 'Packing-slip footer', 'lightweight-html-invoices-for-woocommerce' ), $s ); ?>
		<?php self::section( __( 'Customer Access', 'lightweight-html-invoices-for-woocommerce' ) ); self::checkbox( 'customer_invoice', __( 'Allow customers to view invoices', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::checkbox( 'customer_invoice_print', __( 'Allow customers to print invoices', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::checkbox( 'customer_packing', __( 'Allow customers to view packing slips', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::statuses( $s ); self::text( 'invoice_label', __( 'Invoice button label', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::text( 'packing_label', __( 'Packing-slip button label', 'lightweight-html-invoices-for-woocommerce' ), $s ); ?>
		<?php self::section( __( 'Advanced', 'lightweight-html-invoices-for-woocommerce' ) ); self::number( 'bulk_max', __( 'Bulk print maximum', 'lightweight-html-invoices-for-woocommerce' ), $s, 1, 200 ); self::checkbox( 'remove_data', __( 'Remove plugin data on uninstall', 'lightweight-html-invoices-for-woocommerce' ), $s ); self::checkbox( 'debug', __( 'Debug mode', 'lightweight-html-invoices-for-woocommerce' ), $s ); echo '</tbody></table>'; submit_button(); ?></form></div><?php
	}

	private static function section( $title ) { static $open = false; if ( $open ) { echo '</tbody></table>'; } echo '<h2>' . esc_html( $title ) . '</h2><table class="form-table" role="presentation"><tbody>'; $open = true; }
	private static function row( $key, $label, $control ) { printf( '<tr><th scope="row"><label for="lhiwc-%1$s">%2$s</label></th><td>%3$s</td></tr>', esc_attr( $key ), esc_html( $label ), $control ); }
	private static function name( $key ) { return self::OPTION . '[' . $key . ']'; }
	private static function checkbox( $key, $label, $s ) { self::row( $key, $label, sprintf( '<label><input id="lhiwc-%1$s" type="checkbox" name="%2$s" value="yes" %3$s> %4$s</label>', esc_attr( $key ), esc_attr( self::name( $key ) ), checked( 'yes', $s[ $key ], false ), esc_html__( 'Enabled', 'lightweight-html-invoices-for-woocommerce' ) ) ); }
	private static function text( $key, $label, $s ) { self::row( $key, $label, sprintf( '<input id="lhiwc-%1$s" class="regular-text" type="text" name="%2$s" value="%3$s">', esc_attr( $key ), esc_attr( self::name( $key ) ), esc_attr( $s[ $key ] ) ) ); }
	private static function email( $key, $label, $s ) { self::row( $key, $label, sprintf( '<input id="lhiwc-%1$s" class="regular-text" type="email" name="%2$s" value="%3$s">', esc_attr( $key ), esc_attr( self::name( $key ) ), esc_attr( $s[ $key ] ) ) ); }
	private static function number( $key, $label, $s, $min, $max = null ) { $maximum = null === $max ? '' : ' max="' . absint( $max ) . '"'; self::row( $key, $label, sprintf( '<input id="lhiwc-%1$s" class="small-text" type="number" name="%2$s" value="%3$d" min="%4$d"%5$s>', esc_attr( $key ), esc_attr( self::name( $key ) ), absint( $s[ $key ] ), absint( $min ), $maximum ) ); }
	private static function textarea( $key, $label, $s ) { self::row( $key, $label, sprintf( '<textarea id="lhiwc-%1$s" class="large-text" rows="4" name="%2$s">%3$s</textarea>', esc_attr( $key ), esc_attr( self::name( $key ) ), esc_textarea( $s[ $key ] ) ) ); }
	private static function select( $key, $label, $options, $s ) { $html = '<select id="lhiwc-' . esc_attr( $key ) . '" name="' . esc_attr( self::name( $key ) ) . '">'; foreach ( $options as $value => $text ) { $html .= '<option value="' . esc_attr( $value ) . '" ' . selected( $s[ $key ], $value, false ) . '>' . esc_html( $text ) . '</option>'; } self::row( $key, $label, $html . '</select>' ); }
	private static function paper_fields( $prefix, $s ) { self::select( $prefix . '_size', __( 'Paper size', 'lightweight-html-invoices-for-woocommerce' ), array( 'a4' => 'A4', 'a5' => 'A5', '6x4' => '6 × 4 in' ), $s ); self::select( $prefix . '_orientation', __( 'Orientation', 'lightweight-html-invoices-for-woocommerce' ), array( 'portrait' => __( 'Portrait', 'lightweight-html-invoices-for-woocommerce' ), 'landscape' => __( 'Landscape', 'lightweight-html-invoices-for-woocommerce' ) ), $s ); }
	private static function statuses( $s ) { $html = '<fieldset id="lhiwc-customer_statuses">'; foreach ( wc_get_order_statuses() as $key => $label ) { $value = self::strip_status_prefix( $key ); $html .= sprintf( '<label><input type="checkbox" name="%1$s[]" value="%2$s" %3$s> %4$s</label><br>', esc_attr( self::name( 'customer_statuses' ) ), esc_attr( $value ), checked( in_array( $value, (array) $s['customer_statuses'], true ), true, false ), esc_html( $label ) ); } self::row( 'customer_statuses', __( 'Eligible order statuses', 'lightweight-html-invoices-for-woocommerce' ), $html . '</fieldset>' ); }
}
