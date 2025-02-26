<?php 
/*
    Template Name: Registration

*/

?>
<?php  get_header('dashboard');?>
<section class="register" id="register">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo bloginfo('template_url');?>/assets/images/logo.png" class="w-50">
            </div>
            <div class="col-md-6"></div>
        </div>
    </div>
    <div id="registration_dealder" class="container">
        <div class="row ">
            <div class="col-md-7 m-auto py-5">
                <h1 class="pb-4">Dealer Registration</h1>
                <p>Nisl blandit molestie aliquam viverra sapien congue odio.</p>
                <form method="POST">
                <div class="row">   
                    <div class="col mb-3">
                        <input type="text" class="form-control" id="company_name" name="ds_company_name" placeholder="Company Name" aria-describedby="email address" required>
                    </div>
                    <div class="col mb-3">
                        <input type="text" class="form-control" id="business_name" name="ds_business_name" placeholder="Business Name" aria-describedby="email address" required>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="row">
                        <div class="col">
                            <input type="text" class="form-control" id="firstname" name="first_name" placeholder="First Name" aria-describedby="Firstname" required>
                        </div>
                        <div class="col">
                            <input type="text" class="form-control" id="lastname" name="last_name"   placeholder="Last Name"aria-describedby="Lastname" required>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <input type="email" class="form-control" id="email_address" name="email_address" placeholder="Email address" aria-describedby="email address" required>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                    </div>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="ds_phone" id="ds_phone" placeholder="Contact Number">
                </div>
                <button id="register_dealer" type="submit" name="register_dealer_no_tree" class="w-100 btn btn-primary">Register</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php  get_footer('login');?>