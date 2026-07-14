<?php defined( 'ABSPATH' ) || exit; ?>
<article class="lhiwc-document lhiwc-packing-slip">
	<?php $this->template( 'header', compact( 'order', 'document', 'type' ) ); ?>
	<section class="lhiwc-meta"><h2><?php echo esc_html( $document['title'] ); ?></h2><dl><dt><?php esc_html_e( 'Order', 'lightweight-html-invoices-for-woocommerce' ); ?></dt><dd><?php echo esc_html( $order->get_order_number() ); ?></dd><dt><?php esc_html_e( 'Order date', 'lightweight-html-invoices-for-woocommerce' ); ?></dt><dd><?php echo esc_html( $document['order_date'] ); ?></dd><?php if ( $order->get_shipping_method() ) : ?><dt><?php esc_html_e( 'Shipping', 'lightweight-html-invoices-for-woocommerce' ); ?></dt><dd><?php echo esc_html( $order->get_shipping_method() ); ?></dd><?php endif; ?></dl></section>
	<?php $this->template( 'addresses', compact( 'order', 'document', 'type' ) ); ?>
	<?php do_action( 'lhiwc_before_product_table', $type, $order ); $this->template( 'product-table', compact( 'order', 'document', 'type' ) ); do_action( 'lhiwc_after_product_table', $type, $order ); ?>
	<?php if ( 'yes' === LHIWC_Settings::get( 'packing_prices' ) ) { $this->template( 'totals', compact( 'order', 'document', 'type' ) ); } ?>
	<?php $this->template( 'footer', compact( 'order', 'document', 'type' ) ); ?>
</article>

