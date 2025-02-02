<?php
  if ( ! defined( 'ABSPATH' ) ) exit; 
?>

<div id="ghlconnectpro-options">
	<h1> <?php esc_html_e('Customize Your Woocommerce Order Status', 'ghl-connect-pro'); ?> </h1>
	<hr />

	<form id="ghlconnectpro-settings-form" method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

		<?php wp_nonce_field('ghl-connect-pro'); ?>

		<input type="hidden" name="action" value="ghlconnectpro_admin_settings">

		<table class="form-table" role="presentation">

			<tbody>

				<tr>
					<th scope="row">
						<label> <?php esc_html_e( 'Trigger for Physical Products:', 'ghl-connect-pro' ); ?> </label>

					</th>
					<td>
						<select name='ghlconnectpro_order_status'>
							<?php 
							echo wp_kses(
                            ghlconnectpro_fetch_all_order_statuses(),
                            array(
                                'option'      => array(
                                    'value'  => array(),
									'selected'=>array()
                                )
                            )
                        ); ?>
						</select>
					</td>
				</tr>
				
				<!--for downlodable products-->
				<tr>
					<th scope="row">
						<label> <?php esc_html_e( 'Trigger for Downloadable Products:', 'ghl-connect-pro' ); ?> </label>

					</th>
					<td>
						<select name='ghlconnectpro_order_status_downloadable'>
							<?php 
							echo wp_kses(
                            ghlconnectpro_fetch_all_order_statuses_downloadable(),
                            array(
                                'option'      => array(
                                    'value'  => array(),
									'selected'=>array()
                                )
                            )
                        ); ?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>

		<div>
		<button class="ghl_connect button" type="submit" name="ghl_trigger">Update Settings</button>
		</div>

	</form>
</div>

<?php

//fetch the order status for pysical.
function ghlconnectpro_fetch_all_order_statuses() {

	$order_statuses = wc_get_order_statuses();
	$ghlconnectpro_order_status = get_option('ghlconnectpro_order_status');
	$selected = !empty($ghlconnectpro_order_status) ? $ghlconnectpro_order_status : 'wc-processing';

	$statuses = "";
	foreach ( $order_statuses as $key => $status ) {

		$selected_status = ( $selected == $key ) ? 'selected' : '';
		$statuses .= "<option value='{$key}' {$selected_status}> {$status} </option>";
	}

	return $statuses;
}

//fetch the order status for downlodable.
function ghlconnectpro_fetch_all_order_statuses_downloadable() {

	$order_statuses = wc_get_order_statuses();
	$ghlconnectpro_order_status = get_option('ghlconnectpro_order_status_downloadable');
	$selected = !empty($ghlconnectpro_order_status) ? $ghlconnectpro_order_status : 'wc-processing';

	$statuses = "";
	foreach ( $order_statuses as $key => $status ) {

		$selected_status = ( $selected == $key ) ? 'selected' : '';
		$statuses .= "<option value='{$key}' {$selected_status}> {$status} </option>";
	}

	return $statuses;
}