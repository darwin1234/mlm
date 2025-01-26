<?php 
/*
    Template Name: Registration

*/
get_header();
?>
<section class="register py-5" id="register">
    <div class="container">
        <div class="row ">
            <div class="col-md-5 m-auto py-5 border">
                <h4 class="pb-4">Please fill with your details</h4>
                <form method="POST">
                <div class="mb-3">
                    <label for="fistname" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="firstname" name="first_name" aria-describedby="Firstname">
                </div>
                <div class="mb-3">
                    <label for="lastname" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="lastname" name="last_name" aria-describedby="Lastname">
                </div>
                <div class="mb-3">
                    <label for="email_address" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email_address" name="email_address" aria-describedby="email address">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password">
                </div>
                <button id="register_dealer" type="submit" name="register_dealer" class="w-100 btn btn-primary">Register</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php 
get_footer();
?>