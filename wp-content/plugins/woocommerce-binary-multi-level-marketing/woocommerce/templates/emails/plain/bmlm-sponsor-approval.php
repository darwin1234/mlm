<?php
/**
 * Sponsor Approval Plain Email template
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

echo '= ' . esc_html( $email_heading ) . " =\n\n";

esc_html_e( 'Hi', 'binary-mlm' ) . ', ' . esc_html( $data['name'] ) . "\n\n";

esc_html_e( 'Your account has been approved by your ordered membership product, you are eligible to earn the commissions and spread the network.', 'binary-mlm' ) . "\n\n";

esc_html_e( 'Your membership details are following...', 'binary-mlm' ) . "\n\n";

esc_html_e( 'Referral Id', 'binary-mlm' ) . ' : ' . esc_html( $data['referral_id'] ) . "\n\n";

esc_html_e( 'Sponsor Id', 'binary-mlm' ) . ' : ' . esc_html( $data['sponsor_id'] ) . "\n\n";

esc_html_e( 'Level', 'binary-mlm' ) . ' : ' . esc_html( $data['level'] ) . "\n\n";

esc_html_e( 'If you have any query, please contact us at -', 'binary-mlm' ) . "\n\n";

echo esc_html( $admin_email ) . "\n\n";

echo esc_html( $email->footer ) . "\n\n";

if ( isset( $additional_content ) && ! empty( $additional_content ) ) {

	esc_html_e( 'Additional Content :', 'binary-mlm' ) . "\n\n";

	echo esc_html( $additional_content ) . "\n\n";
}
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo esc_html( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
