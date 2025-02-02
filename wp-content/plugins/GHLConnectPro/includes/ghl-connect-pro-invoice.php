<?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		if (isset($_POST['invoiceCreate'])){
			//check for invoice
			$Invoicechoice = isset($_POST["choiceInvoice"]) && $_POST["choiceInvoice"] === "yes" ? "yes" : "no";
			update_option('ghlconnectpro_invoice_check', $Invoicechoice);
			
		}
	}
?>

<form method="post" class="form-table">
		<?php $invoice_data=get_option('ghlconnectpro_invoice_check');?>
		<table>
			<tbody>
				
				<tr>
					<th scope="row">
						<label>Do you want Create Invoice in GHL CRM?</label>
					</th>
					<td>
						<input type="checkbox" name="choiceInvoice" <?php if ($invoice_data==='yes') echo "checked";?> value="yes">
					</td>
				</tr>			
			</tbody>	
		</table>
		<button class="ghl_connect button" type="submit" name="invoiceCreate">Update Settings</button>
</form>