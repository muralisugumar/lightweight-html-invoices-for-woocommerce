<?php defined( 'ABSPATH' ) || exit; $totals = $order->get_order_item_totals(); ?>
<table class="lhiwc-totals"><tbody><?php foreach ( $totals as $total ) : ?><tr><th scope="row"><?php echo wp_kses_post( $total['label'] ); ?></th><td class="num"><?php echo wp_kses_post( $total['value'] ); ?></td></tr><?php endforeach; ?></tbody></table>

