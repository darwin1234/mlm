<?php
/**
 * Commission Plain Email template
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

echo '= ' . esc_html( $email_heading ) . " =\n\n";

esc_html_e( 'Hi', 'binary-mlm' ) . ', ' . esc_html( $data['name'] ) . "\n\n";
/* translators: %s is commission name*/
echo wp_sprintf( esc_html__( 'You received a new %s, your received commission details are following...', 'binary-mlm' ), esc_html( $data['commission_name'] ) ) . "\n\n";


esc_html_e( 'Commission Name', 'binary-mlm' ) . ' : ' . esc_html( $data['commission_name'] ) . "\n\n";

esc_html_e( 'Commission Amount', 'binary-mlm' ) . ' : ' . esc_html( $data['commission'] ) . "\n\n";

esc_html_e( 'Description', 'binary-mlm' ) . ' : ' . esc_html( $data['description'] ) . "\n\n";

esc_html_e( 'If you have any query, please contact us at -', 'binary-mlm' ) . "\n\n";

echo esc_html( $admin_email ) . "\n\n";

echo esc_html( $email->footer ) . "\n\n";

if ( isset( $additional_content ) && ! empty( $additional_content ) ) {

	esc_html_e( 'Additional Content :', 'binary-mlm' ) . "\n\n";

	echo esc_html( $additional_content ) . "\n\n";
}
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
