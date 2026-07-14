<?php
defined( 'ABSPATH' ) || exit;

final class LHIWC_Plugin {
	private static $instance;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function declare_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', LHIWC_FILE, true );
		}
	}

	private function __construct() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_notices', array( $this, 'dependency_notice' ) );
			return;
		}
		LHIWC_Settings::init();
		$documents = new LHIWC_Documents();
		new LHIWC_Access( $documents );
	}

	public function dependency_notice() {
		if ( current_user_can( 'activate_plugins' ) ) {
			echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__( 'Lightweight HTML Invoices & Packing Slips requires WooCommerce to be active.', 'lightweight-html-invoices-for-woocommerce' ) . '</p></div>';
		}
	}
}

