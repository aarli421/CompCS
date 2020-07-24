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
<div class="background">
<!-- Form-->
<section data-stellar-background-ratio="0.5">
    <div class="container">
        <div class="form">
            <div class="form-toggle"></div>
            <div class="form-panel one">
                <div class="form-header">
                    <h1>Account Login</h1>
                </div>
                <div class="form-content">
                    <form id="login" method="post">
                        <div class="form-group"><label for="username">Username</label><input type="text" id="loginUsername" name="username" required="required" /></div>
                        <div class="form-group"><label for="password">Password</label><input type="password" id="loginPassword" name="password" required="required" /></div>
                        <div class="form-group"><a class="form-recovery" href="#">Forgot Password?</a></div>
                        <div class="form-group"><button type="submit">Log In</button></div>
                    </form>
                </div>
            </div>
            <div class="form-panel two">
                <div class="form-header">
                    <h1>Register Account</h1>
                </div>
                <div class="form-content">
                    <form id="register" method="post">
                        <div class="form-group"><label for="username">Username</label><input type="text" id="username" name="singUpUsername" required="required" /></div>
                        <div class="form-group"><label for="password">Password</label><input type="password" id="password" name="signUpPassword" required="required" /></div>
                        <div class="form-group"><label for="cpassword">Confirm Password</label><input type="password" id="cpassword" name="signUpCPassword" required="required" /></div>
                        <div class="form-group"><label for="email">Email Address</label><input type="email" id="email" name="signUpEmail" required="required" /></div>
                        <div class="form-group"><label class="form-remember"><input type="checkbox" checked="checked" required="required"/>Agree to Terms of Service</label></div>
                        <div class="form-group"><button type="submit">Register</button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    $(function () {
        $("form#login").submit(function(e) {
            return false;
            var formData = $(this).serialize();

            $.ajax({
                url: "ajax/login.php",
                type: "POST",
                data: formData,
                success: function(data) {
                    if (data == "Success") {
                        alert("Successfully logged in!");
                    } else {
                        alert("Error: " + data);
                    }
                },
                error: function(data) {

                }
            });
        });

        $("form#register").submit(function(e) {
            return false;
            var formData = $(this).serialize();

            $.ajax({
                url: "ajax/register.php",
                type: "POST",
                data: formData,
                success: function(data) {
                    if (data == "Success") {
                        alert("Successfully registered!");
                    } else {
                        alert("Error: " + data);
                    }
                },
                error: function(data) {

                }
            });
        });
    });
</script>
</div>
</div>
<?php
require '../templates/footer.php'
?>
