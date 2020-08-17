<?php
require '../templates/helper.php';
require '../templates/header.php';
?>
<style>
    .background {
        background-image: url("images/bg.png");
        background-repeat: no-repeat, repeat;
        /*background-size: cover;*/
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
        $("#login").submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: "ajax/login.php",
                type: "POST",
                data: formData,
                success: function(data) {
                    if (data == "Success") {
                        $("#loginSuccess").css("display", "inline");
                        $("#loginSuccess").html("Successfully logged you in. Redirecting you in 3 seconds... If you are not redirected you can click <a href='http://www.compcs.codes/home.php'>this</a>.");
                        $("#loginError").css("display", "none");
                        $("#loginError").html("");
                        setTimeout(function () {
                            $(location).attr("href", "https://www.compcs.codes/home.php");
                        }, 3000);
                    } else {
                        $("#loginSuccess").css("display", "none");
                        $("#loginSuccess").html("");
                        $("#loginError").css("display", "inline");
                        $("#loginError").html(data);
                    }
                },
                error: function(data) {
                    $("#loginSuccess").css("display", "none");
                    $("#loginSuccess").html("");
                    $("#loginError").css("display", "inline");
                    $("#loginError").html(data);
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
            <div class="form-panel one">
                <div class="form-header">
                    <h1>Account Login</h1>
                </div>
                <div class="form-content">
                    <form id="login" method="post">
                        <div class="form-group"><label for="loginUsername">Username</label><input type="text" id="loginUsername" name="loginUsername" required="required" /></div>
                        <div class="form-group"><label for="loginPassword">Password</label><input type="password" id="loginPassword" name="loginPassword" required="required" /></div>
                        <div class="form-group"><a class="form-recovery" href="#">Forgot Password?</a></div>
                        <div class="form-group"><button form="login" type="submit">Log In</button></div>
                    </form>
                </div>
                <h6 id="loginSuccess" class="text-success"></h6>
                <h6 id="loginError" class="text-danger"></h6>
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
