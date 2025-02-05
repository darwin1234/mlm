<?php get_header('registration');?>
<div>
<form id="registration_client_form" style="display:block!important;" method="POST">
        <div class="container">
            <div id="sponsorFrm" class="row">
                <div class="col-md-8 m-auto block">
                        <img  class="w-60 m-auto" src="<?php echo  bloginfo('template_url');?>/assets/images/logo.png">
                        <h2 class="text-center">Dealer's Registration</h2>
                        <div class="row">
                            <div class="col">
                                <label>First Name</label>
                                <input type="text" class="form-control" name="first_name">
                            </div>
                            <div class="col">
                                 <label>Last Name</label>
                                <input type="text" class="form-control" name="last_name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label>Company Name</label>
                                <input type="text" class="form-control" name="ds_company_name">
                            </div>
                            <div class="col">
                                <label>Business Name</label>
                                <input type="text" class="form-control" name="ds_business_name">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label>Phone Number</label>
                                <input type="text" class="form-control" name="ds_phone">
                            </div>
                            <div class="col">
                                <label>Address</label>
                                <input type="text" class="form-control" name="ds_address">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label>City</label>
                                <input type="text" class="form-control" name="ds_city">
                            </div>
                            <div class="col">
                                <label>State</label>
                                <input type="text" class="form-control" name="ds_state">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label>Postal Code</label>
                                <input type="text" class="form-control" name="ds_postal_code">
                            </div>
                            <div class="col">
                                <label>Country</label>
                                <input type="text" class="form-control" name="ds_country">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label>Email Address</label> 
                                    <input type="email" name="email_address" class="form-control">   
                                <label>Password</label> 
                                    <input type="password" name="password" class="form-control woocommerce-Input woocommerce-Input--text input--text">   
                            </div>
                        </div>
                        <?php
                            if(isset($_GET['sponsor'])){
                                $parent_id = (int)sanitize_text_field($_GET['sponsor']);
                                $refferal_id = get_user_meta((int)$_GET['sponsor'], 'bmlm_sponsor_id', true );
                            }
		                ?>
                        <inpuy type="text" name="parent_id" value="<?php echo $parent_id; ?>">
                        <input type="hidden" name="dealer_form_xxxx" value="1">
                        <div class="woocommerce-form-row form-row">
                            <button type="submit" name="submit"  class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit">Register Now</button>
                        </div>

                </div>
            </div>
        </div>
    </form>
</div>
<?php get_footer('registration');?>