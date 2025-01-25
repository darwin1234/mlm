<?php
/**
 * Sponsor Approval Email template
 *
 * @package WooCommerce Binary Multi Level Marketing
 */

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

do_action( 'woocommerce_email_header', $email_heading, $email );
?>
<p>
	<?php esc_html_e( 'Hi', 'binary-mlm' ); ?>, <?php echo esc_html( $data['name'] ); ?>
</p>
<p>
	<?php esc_html_e( 'Your account has been approved by your ordered membership product, you are eligible to earn the commissions and spread the network.', 'binary-mlm' ); ?>
	<br/>
<?php esc_html_e( 'Your membership details are following...', 'binary-mlm' ); ?>
</p>
<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
			<tr>
				<th class="td" scope="col" style="text-align:center;"><?php esc_html_e( 'Referral Id', 'binary-mlm' ); ?></th>
				<th class="td" scope="col" style="text-align:center;"><?php esc_html_e( 'Sponsor Id', 'binary-mlm' ); ?></th>
				<th class="td" scope="col" style="text-align:center;"><?php esc_html_e( 'Level', 'binary-mlm' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php echo esc_html( $data['referral_id'] ); ?>
				</td>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php echo esc_html( $data['sponsor_id'] ); ?>
				</td>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php echo esc_html( $data['level'] ); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<br/>
	<p><?php esc_html_e( 'If you have any query, please contact us at -', 'binary-mlm' ); ?><a href="mailto:<?php echo esc_attr( $admin_email ); ?>"><?php echo esc_html( $admin_email ); ?></a><p>
	<p><?php echo esc_html( $email->footer ); ?></p>
<?php
if ( isset( $additional_content ) && ! empty( $additional_content ) ) {
	?>
	<p> <strong><?php esc_html_e( 'Additional Content : ', 'binary-mlm' ); ?></strong>
	<?php echo esc_html( $additional_content ); ?></p>
	<?php
}

do_action( 'woocommerce_email_footer', $email );

