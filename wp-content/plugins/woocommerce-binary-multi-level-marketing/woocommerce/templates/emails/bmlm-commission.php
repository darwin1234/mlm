<?php
/**
 * Commission Email template
 *
 * @package WooCommerce Binary Multi Level Marketing
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit(); // Exit if access directly.

do_action( 'woocommerce_email_header', $email_heading, $email );
?>
<p>
	<?php esc_html_e( 'Hi', 'binary-mlm' ); ?>, <?php echo esc_html( $data['name'] ); ?>
</p>
<p>
	<?php
	/* translators: %s is commission name*/
	echo wp_sprintf( esc_html__( 'You received a new %s, your received commission details are following...', 'binary-mlm' ), esc_html( $data['commission_name'] ) );
	?>
	<br/>
</p>
<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
			<tr>
				<th class="td" colspan="2" scope="col" style="text-align:center;"><?php esc_html_e( 'Commission Details', 'binary-mlm' ); ?></th>
			</tr>
			<tr>
				<th class="td" scope="col" style="text-align:center;"><?php esc_html_e( 'Title', 'binary-mlm' ); ?></th>
				<th class="td" scope="col" style="text-align:center;"><?php esc_html_e( 'Description', 'binary-mlm' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php esc_html_e( 'Commission Name', 'binary-mlm' ); ?>
				</td>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php echo esc_html( $data['commission_name'] ); ?>
				</td>
			</tr>

			<tr>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php esc_html_e( 'Commission Amount', 'binary-mlm' ); ?>
				</td>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php echo wp_kses_post( wc_price( $data['commission'], array( 'decimals' => 2 ) ) ); ?>
				</td>
			</tr>

			<tr>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php esc_html_e( 'Description', 'binary-mlm' ); ?>
				</td>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php echo esc_html( $data['description'] ); ?>
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

