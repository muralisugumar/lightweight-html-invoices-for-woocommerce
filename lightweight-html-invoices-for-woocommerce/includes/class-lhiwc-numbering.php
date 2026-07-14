<?php
defined( 'ABSPATH' ) || exit;

final class LHIWC_Numbering {
	const META_NUMBER = '_lhiwc_invoice_number';
	const META_DATE   = '_lhiwc_invoice_date';

	public static function get( $order ) {
		if ( 'sequential' !== LHIWC_Settings::get( 'number_method' ) ) {
			return apply_filters( 'lhiwc_invoice_number', $order->get_order_number(), $order );
		}
		$existing = $order->get_meta( self::META_NUMBER, true );
		if ( $existing ) {
			return apply_filters( 'lhiwc_invoice_number', $existing, $order );
		}
		$lock = 'lhiwc_number_lock';
		$acquired = add_option( $lock, time(), '', false );
		if ( ! $acquired ) {
			$stamp = absint( get_option( $lock ) );
			if ( $stamp && time() - $stamp > 30 ) {
				delete_option( $lock );
			}
			for ( $i = 0; $i < 20 && ! $acquired; $i++ ) {
				$acquired = add_option( $lock, time(), '', false );
				usleep( 50000 );
			}
		}
		if ( ! $acquired ) {
			wp_die( esc_html__( 'An invoice number is currently being assigned. Please try again.', 'lightweight-html-invoices-for-woocommerce' ), esc_html__( 'Invoice temporarily unavailable', 'lightweight-html-invoices-for-woocommerce' ), array( 'response' => 503, 'back_link' => true ) );
		}
		$settings = wp_parse_args( get_option( LHIWC_Settings::OPTION, array() ), LHIWC_Settings::defaults() );
		$year     = gmdate( 'Y' );
		if ( 'yes' === $settings['reset_annually'] && get_option( 'lhiwc_number_year' ) !== $year ) {
			$settings['next_number'] = 1;
			update_option( 'lhiwc_number_year', $year, false );
		}
		$number = absint( $settings['next_number'] );
		$formatted = sanitize_text_field( $settings['number_prefix'] ) . str_pad( (string) $number, absint( $settings['number_padding'] ), '0', STR_PAD_LEFT ) . sanitize_text_field( $settings['number_suffix'] );
		$order->update_meta_data( self::META_NUMBER, $formatted );
		$order->update_meta_data( self::META_DATE, time() );
		$order->save();
		$settings['next_number'] = $number + 1;
		update_option( LHIWC_Settings::OPTION, $settings );
		delete_option( $lock );
		return apply_filters( 'lhiwc_invoice_number', $formatted, $order );
	}
}
