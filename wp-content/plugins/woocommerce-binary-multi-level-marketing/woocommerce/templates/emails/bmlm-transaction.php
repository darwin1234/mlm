<?php
/**
 * Transaction Email template
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
	<?php
	if ( 'credit' === $data['type'] ) {
		/* translators: %s is credit amount*/
		echo wp_sprintf( esc_html__( 'You have received %s in your wallet, transaction details are following...', 'binary-mlm' ), wp_kses_post( wc_price( $data['amount'], array( 'decimals' => 2 ) ) ) );
	} else {
		/* translators: %s is debit amount */
		echo wp_sprintf( esc_html__( 'Your wallet debit with %s, transaction details are following...', 'binary-mlm' ), wp_kses_post( wc_price( $data['amount'], array( 'decimals' => 2 ) ) ) );
	}
	?>
	<br/>
</p>
<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
			<tr>
				<th class="td" colspan="2" scope="col" style="text-align:center;"><?php esc_html_e( 'Transaction Details', 'binary-mlm' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php esc_html_e( 'Transaction Id', 'binary-mlm' ); ?>
				</td>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php echo esc_html( $data['transaction']['transaction_id'] ); ?>
				</td>
			</tr>
			<tr>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php esc_html_e( 'Transaction Type', 'binary-mlm' ); ?>
				</td>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php echo esc_html( ucfirst( $data['type'] ) ); ?>
				</td>
			</tr>

			<tr>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php
				/* translators: %s is credit/debit type */
				echo wp_sprintf( esc_html__( '%s Amount', 'binary-mlm' ), esc_html( ucfirst( $data['type'] ) ) );
				?>
				</td>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php echo wp_kses_post( wc_price( $data['amount'], array( 'decimals' => 2 ) ) ); ?>
				</td>
			</tr>
			<tr>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php esc_html_e( 'Transaction Date', 'binary-mlm' ); ?>
				</td>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php echo esc_html( gmdate( 'F d, Y h:i A', strtotime( $data['date'] ) ) ); ?>
				</td>
			</tr>

			<tr>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php esc_html_e( 'Reference', 'binary-mlm' ); ?>
				</td>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php echo esc_html( $data['reference'] ); ?>
				</td>
			</tr>

			<tr>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php esc_html_e( 'Note', 'binary-mlm' ); ?>
				</td>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php echo esc_html( $data['note'] ); ?>
				</td>
			</tr>

			<tr>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php esc_html_e( 'Current Wallet Bal.', 'binary-mlm' ); ?>
				</td>
				<td class="td" style="text-align:center; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
				<?php echo wp_kses_post( wc_price( $data['wallet_bal'], array( 'decimals' => 2 ) ) ); ?>
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
