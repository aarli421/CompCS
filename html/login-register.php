<?php
require '../templates/header.php';
?>
<style>
    .background {
        background-image: url("images/bestbg.png");
        background-repeat: no-repeat, repeat;
        background-size: cover;
    }
</style>
<style>
    .alert {
        position: relative;
        padding: 20px;
        background-color: #f44336;
        color: white;
        opacity: 1;
        transition: opacity 0.6s;
        margin-bottom: 15px;
        z-index: 999;
    }

    .alert.success {background-color: #4CAF50;}
    .alert.info {background-color: #2196F3;}
    .alert.warning {background-color: #ff9800;}

    .closebtn {
        margin-left: 15px;
        color: white;
        font-weight: bold;
        float: right;
        font-size: 22px;
        line-height: 20px;
        cursor: pointer;
        transition: 0.3s;
        z-index: 9999;
    }

    .closebtn:hover {
        color: black;
    }
</style>
<script>
    $(function () {
        $("form#login").submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: "ajax/login.php",
                type: "POST",
                data: formData,
                success: function(data) {
                    if (data == "Success") {
                        $(location).attr("href", "http://www.compcs.codes/home.php")
                    } else {
                        console.log("Error: " + data);
                    }
                },
                error: function(data) {

                }
            });
        });

        $("form#register").submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: "ajax/register.php",
                type: "POST",
                data: formData,
                success: function(data) {
                    if (data == "Success") {
                        console.log("Successfully registered!");
                    } else {
                        console.log("Error: " + data);
                    }
                },
                error: function(data) {

                }
            });
        });
    });
</script>
<div class="background">
<!-- Form-->
<section data-stellar-background-ratio="0.5">
    <div class="container">
        <div class="form">
            <div class="form-toggle"></div>
            <div class="form-panel one">
                <h6 id="loginSuccess" class="text-success"></h6>
                <h6 id="loginError" class="text-danger"></h6>
                <div class="form-header">
                    <h1>Account Login</h1>
                </div>
                <div class="form-content">
                    <form id="login" method="post">
                        <div class="form-group"><label for="username">Username</label><input type="text" id="username" name="loginUsername" required="required" /></div>
                        <div class="form-group"><label for="password">Password</label><input type="password" id="password" name="loginPassword" required="required" /></div>
                        <div class="form-group"><a class="form-recovery" href="#">Forgot Password?</a></div>
                        <div class="form-group"><button form="login" type="submit">Log In</button></div>
                    </form>
                </div>
            </div>
            <div class="form-panel two">
                <h6 id="registerSuccess" class="text-success">Random</h6>
                <h6 id="registerError" class="text-danger">Random</h6>
                <div class="form-header">
                    <h1>Register Account</h1>
                </div>
                <div class="form-content">
                    <form id="register" method="post">
                        <div class="form-group"><label for="username">Username</label><input type="text" id="username" name="signUpUsername" required="required" /></div>
                        <div class="form-group"><label for="password">Password</label><input type="password" id="password" name="signUpPassword" required="required" /></div>
                        <div class="form-group"><label for="cpassword">Confirm Password</label><input type="password" id="cpassword" name="signUpCPassword" required="required" /></div>
                        <div class="form-group"><label for="email">Email Address</label><input type="email" id="email" name="signUpEmail" required="required" /></div>
                        <div class="form-group"><label class="form-remember"><input type="checkbox" checked="checked" required="required"/>Agree to Terms of Service</label></div>
                        <div class="form-group"><button form="register" type="submit">Register</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
</div>
</div>
<script src="js/login-register.js"></script>
<?php
require '../templates/footer.php'
?>
