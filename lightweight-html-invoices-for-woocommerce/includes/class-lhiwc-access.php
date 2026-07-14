<?php
defined( 'ABSPATH' ) || exit;

final class LHIWC_Access {
	private $documents;
	public function __construct( $documents ) {
		$this->documents = $documents;
		add_action( 'admin_post_lhiwc_document', array( $this, 'admin_document' ) );
		add_action( 'admin_post_lhiwc_customer_document', array( $this, 'customer_document' ) );
		add_filter( 'woocommerce_admin_order_actions', array( $this, 'order_actions' ), 20, 2 );
		add_action( 'woocommerce_order_actions', array( $this, 'order_action_options' ) );
		add_action( 'woocommerce_order_action_lhiwc_invoice', array( $this, 'redirect_order_action_invoice' ) );
		add_action( 'woocommerce_order_action_lhiwc_packing', array( $this, 'redirect_order_action_packing' ) );
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'order_edit_links' ) );
		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'bulk_actions' ) );
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( $this, 'bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'handle_bulk' ), 10, 3 );
		add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', array( $this, 'handle_bulk' ), 10, 3 );
		add_filter( 'woocommerce_my_account_my_orders_actions', array( $this, 'account_actions' ), 20, 2 );
		add_action( 'woocommerce_order_details_after_order_table', array( $this, 'account_order_links' ) );
		add_action( 'admin_head-edit.php', array( $this, 'admin_action_css' ) );
		add_action( 'admin_head-woocommerce_page_wc-orders', array( $this, 'admin_action_css' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_links_script' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'customer_links_script' ) );
	}

	public function admin_document() {
		if ( ! is_user_logged_in() ) { $this->error( __( 'You must be logged in.', 'lightweight-html-invoices-for-woocommerce' ), 401 ); }
		$type = $this->type( isset( $_GET['document'] ) ? sanitize_key( wp_unslash( $_GET['document'] ) ) : '' );
		$ids = isset( $_GET['order_ids'] ) ? array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $_GET['order_ids'] ) ) ) ) ) : array( absint( $_GET['order_id'] ?? 0 ) );
		check_admin_referer( $this->nonce_action( $type, $ids ) );
		if ( ! $this->admin_capability() ) { $this->error( __( 'You are not allowed to view these documents.', 'lightweight-html-invoices-for-woocommerce' ), 403 ); }
		$maximum = max( 1, absint( apply_filters( 'lhiwc_bulk_print_maximum', LHIWC_Settings::get( 'bulk_max' ) ) ) );
		if ( ! $ids || count( $ids ) > $maximum ) { $this->error( __( 'The bulk selection is empty or exceeds the allowed limit.', 'lightweight-html-invoices-for-woocommerce' ), 400 ); }
		$orders = array();
		foreach ( array_unique( $ids ) as $id ) { $order = wc_get_order( $id ); if ( ! $order ) { $this->error( __( 'An order could not be found.', 'lightweight-html-invoices-for-woocommerce' ), 404 ); } $orders[] = $order; }
		$this->ensure_enabled( $type );
		$this->documents->render_page( $orders, $type, true ); exit;
	}

	public function customer_document() {
		if ( ! is_user_logged_in() ) { $this->error( __( 'You must be logged in.', 'lightweight-html-invoices-for-woocommerce' ), 401 ); }
		$type = $this->type( isset( $_GET['document'] ) ? sanitize_key( wp_unslash( $_GET['document'] ) ) : '' );
		$id = absint( $_GET['order_id'] ?? 0 ); $order = wc_get_order( $id );
		if ( ! $order ) { $this->error( __( 'The order could not be found.', 'lightweight-html-invoices-for-woocommerce' ), 404 ); }
		check_admin_referer( 'lhiwc_customer_' . $type . '_' . $id );
		$key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : '';
		if ( 0 === $order->get_customer_id() || get_current_user_id() !== (int) $order->get_customer_id() || ! hash_equals( $order->get_order_key(), $key ) || ! $this->customer_allowed( $order, $type ) ) {
			$this->error( __( 'You are not allowed to view this document.', 'lightweight-html-invoices-for-woocommerce' ), 403 );
		}
		$print = 'invoice' !== $type || 'yes' === LHIWC_Settings::get( 'customer_invoice_print' );
		$this->documents->render_page( array( $order ), $type, $print ); exit;
	}

	public function order_actions( $actions, $order ) {
		if ( $this->enabled( 'invoice' ) ) { $actions['lhiwc_invoice'] = array( 'url' => $this->admin_url( $order->get_id(), 'invoice' ), 'name' => __( 'View Invoice', 'lightweight-html-invoices-for-woocommerce' ), 'action' => 'lhiwc-invoice' ); }
		if ( $this->enabled( 'packing-slip' ) ) { $actions['lhiwc_packing'] = array( 'url' => $this->admin_url( $order->get_id(), 'packing-slip' ), 'name' => __( 'View Packing Slip', 'lightweight-html-invoices-for-woocommerce' ), 'action' => 'lhiwc-packing' ); }
		return $actions;
	}
	public function order_action_options( $actions ) { if ( $this->enabled( 'invoice' ) ) { $actions['lhiwc_invoice'] = __( 'Open invoice', 'lightweight-html-invoices-for-woocommerce' ); } if ( $this->enabled( 'packing-slip' ) ) { $actions['lhiwc_packing'] = __( 'Open packing slip', 'lightweight-html-invoices-for-woocommerce' ); } return $actions; }
	public function redirect_order_action_invoice( $order ) { $this->redirect_notice( $this->admin_url( $order->get_id(), 'invoice' ) ); }
	public function redirect_order_action_packing( $order ) { $this->redirect_notice( $this->admin_url( $order->get_id(), 'packing-slip' ) ); }
	private function redirect_notice( $url ) { WC_Admin_Meta_Boxes::add_error( sprintf( '<a href="%1$s" target="_blank">%2$s</a>', esc_url( $url ), esc_html__( 'Open the generated document in a new tab.', 'lightweight-html-invoices-for-woocommerce' ) ) ); }
	public function order_edit_links( $order ) { if ( ! $this->admin_capability() ) { return; } echo '<p class="form-field form-field-wide">'; foreach ( array( 'invoice' => __( 'View / Print Invoice', 'lightweight-html-invoices-for-woocommerce' ), 'packing-slip' => __( 'View / Print Packing Slip', 'lightweight-html-invoices-for-woocommerce' ) ) as $type => $label ) { if ( $this->enabled( $type ) ) { printf( '<a class="button" target="_blank" href="%s">%s</a> ', esc_url( $this->admin_url( $order->get_id(), $type ) ), esc_html( $label ) ); } } echo '</p>'; }
	public function bulk_actions( $actions ) { if ( $this->enabled( 'invoice' ) ) { $actions['lhiwc_invoices'] = __( 'Print invoices', 'lightweight-html-invoices-for-woocommerce' ); } if ( $this->enabled( 'packing-slip' ) ) { $actions['lhiwc_packing'] = __( 'Print packing slips', 'lightweight-html-invoices-for-woocommerce' ); } return $actions; }
	public function handle_bulk( $redirect, $action, $ids ) { $type = 'lhiwc_invoices' === $action ? 'invoice' : ( 'lhiwc_packing' === $action ? 'packing-slip' : '' ); if ( ! $type ) { return $redirect; } $ids = array_values( array_filter( array_map( 'absint', $ids ) ) ); return wp_nonce_url( add_query_arg( array( 'action' => 'lhiwc_document', 'document' => $type, 'order_ids' => implode( ',', $ids ) ), admin_url( 'admin-post.php' ) ), $this->nonce_action( $type, $ids ) ); }
	public function account_actions( $actions, $order ) { foreach ( array( 'invoice', 'packing-slip' ) as $type ) { if ( $this->customer_allowed( $order, $type ) ) { $actions['lhiwc_' . $type] = array( 'url' => $this->customer_url( $order, $type ), 'name' => 'invoice' === $type ? LHIWC_Settings::get( 'invoice_label' ) : LHIWC_Settings::get( 'packing_label' ) ); } } return $actions; }
	public function account_order_links( $order ) { $actions = $this->account_actions( array(), $order ); if ( $actions ) { echo '<p class="lhiwc-account-actions">'; foreach ( $actions as $action ) { printf( '<a class="button" target="_blank" rel="noopener" href="%s">%s</a> ', esc_url( $action['url'] ), esc_html( $action['name'] ) ); } echo '</p>'; } }
	private function customer_allowed( $order, $type ) { $setting = 'invoice' === $type ? 'customer_invoice' : 'customer_packing'; $statuses = (array) apply_filters( 'lhiwc_customer_order_statuses', LHIWC_Settings::get( 'customer_statuses' ), $type ); return $this->enabled( $type ) && 'yes' === LHIWC_Settings::get( $setting ) && in_array( $order->get_status(), $statuses, true ); }
	private function customer_url( $order, $type ) { return wp_nonce_url( add_query_arg( array( 'action' => 'lhiwc_customer_document', 'document' => $type, 'order_id' => $order->get_id(), 'key' => $order->get_order_key() ), admin_url( 'admin-post.php' ) ), 'lhiwc_customer_' . $type . '_' . $order->get_id() ); }
	private function admin_url( $id, $type ) { $ids = array( absint( $id ) ); return wp_nonce_url( add_query_arg( array( 'action' => 'lhiwc_document', 'document' => $type, 'order_id' => $id ), admin_url( 'admin-post.php' ) ), $this->nonce_action( $type, $ids ) ); }
	private function nonce_action( $type, $ids ) { return 'lhiwc_' . $type . '_' . implode( '-', array_map( 'absint', $ids ) ); }
	private function type( $type ) { if ( ! in_array( $type, array( 'invoice', 'packing-slip' ), true ) ) { $this->error( __( 'Unsupported document type.', 'lightweight-html-invoices-for-woocommerce' ), 400 ); } return $type; }
	private function enabled( $type ) { return 'yes' === LHIWC_Settings::get( 'enabled' ) && 'yes' === LHIWC_Settings::get( 'invoice' === $type ? 'invoice_enabled' : 'packing_enabled' ); }
	private function ensure_enabled( $type ) { if ( ! $this->enabled( $type ) ) { $this->error( __( 'This document type is disabled.', 'lightweight-html-invoices-for-woocommerce' ), 403 ); } }
	private function admin_capability() { return current_user_can( 'manage_woocommerce' ) || current_user_can( 'edit_shop_orders' ); }
	private function error( $message, $status ) { wp_die( esc_html( $message ), esc_html__( 'Document unavailable', 'lightweight-html-invoices-for-woocommerce' ), array( 'response' => absint( $status ), 'back_link' => true ) ); }
	public function admin_action_css() { echo '<style>.wc-action-button-lhiwc-invoice::after{content:"\\f491"!important}.wc-action-button-lhiwc-packing::after{content:"\\f480"!important}</style>'; }
	public function admin_links_script( $hook ) {
		$allowed = array( 'edit.php', 'post.php', 'woocommerce_page_wc-orders' );
		if ( in_array( $hook, $allowed, true ) ) {
			wp_enqueue_script( 'lhiwc-document-links', LHIWC_URL . 'assets/js/document-links.js', array(), LHIWC_VERSION, true );
		}
	}
	public function customer_links_script() {
		if ( is_account_page() ) {
			wp_enqueue_script( 'lhiwc-document-links', LHIWC_URL . 'assets/js/document-links.js', array(), LHIWC_VERSION, true );
		}
	}
}
