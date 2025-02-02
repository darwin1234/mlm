<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['license_key'])) {
        $lic=$_POST['license_key'];
        update_option('ghl_connect_pro_license',$lic);
    }
}
?>
    <div class="license_key_container">
        <?php
            $licShow=get_option('ghl_connect_pro_license');
        ?>
        <h2>Enter License Key </h2>
        <form id="ghl-license-key-form1" method="post" action="">
            <input type="text" id="license-key-input" name="license_key" required value="<?php echo esc_attr($licShow); ?>">
            <input type="submit" value="Activate" class="ghl_connect button">
        </form>
    </div>
<?php