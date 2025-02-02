<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if (isset($_POST['global_tag'])) {
			// Capture the raw input
			$raw_tags = $_POST['global_tags'];

			// Split the input string into an array, trimming whitespace and removing empty values
			$ghlconnectpro_globTags = array_filter(array_map('trim', explode(',', $raw_tags)));
			// Update the option with the array of tags
			update_option('ghlconnectpro_globTags', $ghlconnectpro_globTags);
			update_option('ghlconnectpro_global', $raw_tags);

			$Order_Status = isset($_POST["order_status"]) && $_POST["order_status"] === "yes" ? "yes" : "no";
			update_option('ghlconnectpro_order_check', $Order_Status);
		}
	}
?>
<form method="post" class="form-table">
    <?php $globTags = get_option('ghlconnectpro_global');
	$orderTags = get_option('ghlconnectpro_order_check');
		?>
    <table>
        <tbody>
            <tr>
                <th scope="row">
                    <label>Add Global Tags?</label>
                </th>
                <td>

                    <input type="text" name="global_tags" class="global-tags-input"
                        value="<?php echo esc_attr($globTags); ?>">
                    <p class="glob-desc">This tags get fired when there is no products specific tags.</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label>Do you want send order status as a tag?</label>
                </th>
                <td>
                    <input type="checkbox" name="order_status" <?php if ($orderTags==='yes') echo "checked";?>
                        value="yes">
                </td>
            </tr>
        </tbody>
    </table>
    <button class="ghl_connect button" type="submit" name="global_tag">Update Settings</button>
</form>