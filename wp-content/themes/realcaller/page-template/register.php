<?php 
/*
    Template Name: Registration

*/
get_header();
?>
<section class="register py-5" id="register">
    <div class="container">
        <div class="row ">
            <div class="col-md-5 py-5 bg-primary text-white text-center ">
                <div class=" ">
                    <div class="card-body">
                        <img src="http://www.ansonika.com/mavia/img/registration_bg.svg" style="width:30%">
                        <h2 class="py-3">Registration</h2>
                        <p>Tation argumentum et usu, dicit viderer evertitur te has. Eu dictas concludaturque usu, facete detracto patrioque an per, lucilius pertinacia eu vel.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-7 py-5 border">
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
                <button type="submit" name="register_dealer" class="btn btn-primary">Register</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php 
get_footer();
?>