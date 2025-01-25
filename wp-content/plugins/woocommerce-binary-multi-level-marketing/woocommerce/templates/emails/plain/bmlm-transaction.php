<?php
/**
 * Transaction Plain Email template
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

global  $woocommerce;

echo '= ' . esc_html( $email_heading ) . " =\n\n";

esc_html_e( 'Hi', 'binary-mlm' ) . ', ' . esc_html( $data['name'] ) . "\n\n";
/* translators: %s is commission name*/
echo ( 'credit' === $data['type'] ) ?
	/* translators: %s is credit amount*/
	wp_sprintf( esc_html__( 'You have received %s in your wallet, transaction details are following...', 'binary-mlm' ), esc_html( $data['amount'] ) ) :

	wp_sprintf(
		/* translators: %s is debit amount */
		esc_html__( 'Your wallet debit with %s, transaction details are following...', 'binary-mlm' ),
		esc_html( $data['amount'] )
	) . "\n\n";

esc_html_e( 'Transaction Details', 'binary-mlm' ) . "\n\n";

esc_html_e( 'Transaction Id', 'binary-mlm' ) . ' : ' . esc_html( $data['transaction']['transaction_id'] ) . "\n\n";

esc_html_e( 'Transaction Type', 'binary-mlm' ) . ' : ' . esc_html( $data['type'] ) . "\n\n";

echo wp_sprintf(
		/* translators: %s is credit/debit type */
	esc_html__( '%s Amount', 'binary-mlm' ),
	esc_html( ucfirst( $data['type'] ) )
) . ' : ' . esc_html( $data['amount'] ) . "\n\n";


esc_html_e( 'Transaction Date', 'binary-mlm' ) . ' : ' . esc_html( gmdate( 'F d, Y h:i A', strtotime( $data['date'] ) ) ) . "\n\n";

esc_html_e( 'Reference', 'binary-mlm' ) . ' : ' . esc_html( $data['reference'] ) . "\n\n";

esc_html_e( 'Note', 'binary-mlm' ) . ' : ' . esc_html( $data['note'] ) . "\n\n";

esc_html_e( 'Current Wallet Bal.', 'binary-mlm' ) . ' : ' . esc_html( $data['wallet_bal'] ) . "\n\n";

esc_html_e( 'If you have any query, please contact us at -', 'binary-mlm' ) . "\n\n";

echo esc_html( $admin_email ) . "\n\n";

echo esc_html( $email->footer ) . "\n\n";

if ( isset( $additional_content ) && ! empty( $additional_content ) ) {

	esc_html_e( 'Additional Content :', 'binary-mlm' ) . "\n\n";

	echo esc_html( $additional_content ) . "\n\n";
}
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
