<?php 
/*
    Template Name: Registration as Client

*/
get_header();
?>
<section class="register py-5" id="register">
    <div class="container">
        <div class="row ">
            <div class="col-md-7 py-5  m-auto">
                <h4 class="pb-4">Registration</h4>
                <form method="POST">
                    <div class="mb-4">
                        <div class="row">
                            <div class="col">
                                <input type="text" class="form-control" id="firstname" name="first_name" placeholder="First Name" aria-describedby="Firstname">
                            </div>
                            <div class="col">
                                <input type="text" class="form-control" id="lastname" name="last_name"   placeholder="Last Name"aria-describedby="Lastname">
                            </div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <input type="email" class="form-control" id="email_address" name="email_address" placeholder="Email Address" aria-describedby="email address">
                    </div>
                    <div class="mb-4">
                        <input type="password" class="form-control" name="password" id="password" placeholder="Password">
                    </div>
                    <button id="register_client" type="submit"  name="register_client" class="btn btn-primary">Register</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php 
get_footer();
?>