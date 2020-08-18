<?php
require '../templates/helper.php';
require '../templates/header.php';

$usernameReg = "^[A-Za-z0-9]*$";
$passwordReg = "^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?]{8,}$";
$emailReg = "^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$";

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
        $("#register").submit(function(e) {
            e.preventDefault();
            console.log("Passed here 1");

            var formData = $(this).serialize();

            console.log("Passed here 2");

            $.ajax({
                url: "ajax/register.php",
                type: "POST",
                data: formData,
                success: function(data) {
                    if (data == "Success") {
                        $("#registerSuccess").css("display", "inline");
                        $("#registerSuccess").html("You are successfully registered! Please verify your account through the email that was just sent.");
                        $("#registerError").css("display", "none");
                        $("#registerError").html("");

                        let btn = $("#registerBtn");
                        btn.attr("disabled", true);
                        setTimeout(function() {
                            btn.removeAttr("disabled");
                        }, 5000);
                    } else {
                        $("#registerSuccess").css("display", "none");
                        $("#registerSuccess").html("");
                        $("#registerError").css("display", "inline");
                        $("#registerError").html(data);
                    }
                },
                error: function(data) {
                    $("#registerSuccess").css("display", "none");
                    $("#registerSuccess").html("");
                    $("#registerError").css("display", "inline");
                    $("#registerError").html(data);
                }
            });
        });

        var passwordRepeatTimer;
        var doneTypingInterval = 500;

        $("#registerCPassword").keyup(function() {
            clearTimeout(passwordRepeatTimer);
            if ($('#registerCPassword').val()) {
                passwordRepeatTimer = setTimeout(passwordRepeatDoneTyping, doneTypingInterval);
            }
        });

        function passwordRepeatDoneTyping() {
            if ($("#registerPassword").val() == $("#registerCPassword").val()) {
                $("#registerCPassword")[0].setCustomValidity('');
            } else {
                $("#registerCPassword")[0].setCustomValidity('The passwords do not match.');
            }
        }
    });
</script>
<div class="background">
    <!-- Form-->
    <section data-stellar-background-ratio="0.5">
        <div class="container">
            <div class="form">
                <div class="form-panel one">
                    <div class="form-header">
                        <h1>Register Account</h1>
                    </div>
                    <div class="form-content">
                        <form id="register" method="post">
                            <div class="form-group"><label for="registerUsername">Username (Only letters and numbers)</label><input type="text" id="registerUsername" name="signUpUsername" required="required" pattern="<?php echo $usernameReg; ?>" /></div>
                            <div class="form-group"><label for="registerEmail">Email Address</label><input type="email" id="registerEmail" name="signUpEmail" required="required" pattern="<?php echo $emailReg; ?>" /></div>
                            <div class="form-group"><label for="registerPassword">Password</label><input type="password" id="registerPassword" name="signUpPassword" required="required" pattern="<?php echo $passwordReg; ?>" /></div>
                            <div class="form-group"><label for="registerCPassword">Confirm Password</label><input type="password" id="registerCPassword" name="signUpCPassword" required="required" /></div>
                            <div class="form-group"><label class="form-remember"><input type="checkbox" name="tos" checked="checked" required="required"/>Agree to Terms of Service</label></div>
                            <div class="form-group"><button id="registerBtn" form="register" type="submit">Register</button></div>
                        </form>
                    </div>
                    <h6 id="registerSuccess" class="text-success"></h6>
                    <h6 id="registerError" class="text-danger"></h6>
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
