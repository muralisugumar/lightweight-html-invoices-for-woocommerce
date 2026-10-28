<?php defined( 'ABSPATH' ) || exit; ?>
<article class="lhiwc-document lhiwc-invoice">
	<?php $this->template( 'header', compact( 'order', 'document', 'type' ) ); ?>
	<section class="lhiwc-meta"><h2><?php echo esc_html( $document['title'] ); ?></h2><dl><dt><?php esc_html_e( 'Invoice number', 'lightweight-html-invoices-for-woocommerce' ); ?></dt><dd><?php echo esc_html( $document['invoice_number'] ); ?></dd><dt><?php esc_html_e( 'Invoice date', 'lightweight-html-invoices-for-woocommerce' ); ?></dt><dd><?php echo esc_html( $document['invoice_date'] ); ?></dd><dt><?php esc_html_e( 'Order', 'lightweight-html-invoices-for-woocommerce' ); ?></dt><dd><?php echo esc_html( $order->get_order_number() ); ?></dd><dt><?php esc_html_e( 'Order date', 'lightweight-html-invoices-for-woocommerce' ); ?></dt><dd><?php echo esc_html( $document['order_date'] ); ?></dd><dt><?php esc_html_e( 'Status', 'lightweight-html-invoices-for-woocommerce' ); ?></dt><dd><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></dd></dl></section>
	<?php $this->template( 'addresses', compact( 'order', 'document', 'type' ) ); ?>
	<?php do_action( 'lhiwc_before_product_table', $type, $order ); $this->template( 'product-table', compact( 'order', 'document', 'type' ) ); do_action( 'lhiwc_after_product_table', $type, $order ); ?>
	<?php $this->template( 'totals', compact( 'order', 'document', 'type' ) ); ?>
	<?php if ( 'yes' === LHIWC_Settings::get( 'transaction_id' ) && $order->get_transaction_id() ) : ?><p class="lhiwc-transaction"><strong><?php esc_html_e( 'Transaction ID:', 'lightweight-html-invoices-for-woocommerce' ); ?></strong> <?php echo esc_html( $order->get_transaction_id() ); ?></p><?php endif; ?>
	<?php $this->template( 'footer', compact( 'order', 'document', 'type' ) ); ?>
</article>

