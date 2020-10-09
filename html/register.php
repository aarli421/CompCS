<?php
require '../templates/helper.php';
require '../templates/header.php';
?>
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
    function clear() {
        $("#registerUsername").val("");
        $("#registerEmail").val("");
        $("#registerPassword").val("");
        $("#registerCPassword").val("");
    }

    $(function () {
        $('#registerPassword, #registerCPassword').on('keyup', function () {
            if ($("#registerPassword").val() == $("#registerCPassword").val()) {
                $("#registerCPassword")[0].setCustomValidity('');
            } else {
                $("#registerCPassword")[0].setCustomValidity('The passwords do not match.');
            }
        });

        $("#register").submit(function(e) {
            e.preventDefault();
            // console.log("Passed here 1");

            var formData = $(this).serialize();

            // console.log("Passed here 2");

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

                        clear();

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
                    clear();
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
                        <h1>Register Account</h1>
                    </div>
                    <div class="form-content">
                        <form id="register" method="post">
                            <div class="form-group"><label for="registerUsername">Username (Only letters and numbers)</label><input type="text" id="registerUsername" name="signUpUsername" required="required" pattern="<?php echo $usernameReg; ?>" /></div>
                            <div class="form-group"><label for="registerEmail">Email Address</label><input type="email" id="registerEmail" name="signUpEmail" required="required" pattern="<?php echo $emailReg; ?>" /></div>
                            <div class="form-group"><label for="registerPassword">Password (>8 letters, numbers, and symbols)</label><input type="password" id="registerPassword" name="signUpPassword" required="required" pattern="<?php echo $passwordReg; ?>" /></div>
                            <div class="form-group"><label for="registerCPassword">Confirm Password</label><input type="password" id="registerCPassword" name="signUpCPassword" required="required" /></div>
                            <div class="form-group school"><label>School</label></div>
                            <div class="form-group">
                                <ul class="list-unstyled">
                                    <li class="init item">Select School</li>
                                    <li class="item" data-value="value 1" style="padding-top: 10%">American</li>
                                    <li class="item" data-value="value 2">Irvington</li>
                                </ul>
                            </div>
                            <div class="form-group agree after"><label class="form-remember"><input type="checkbox" name="tos" checked="checked" required="required"/>Agree to <a href="tos" target="_blank">Terms of Service</a></label></div>
                            <input id="registerSchool" name="registerSchool" type="hidden" value="">
                            <div class="form-group after"><button id="registerBtn" form="register" type="submit">Register</button></div>
                        </form>
                    </div>
                    <h6 id="registerSuccess" class="text-success"></h6>
                    <h6 id="registerError" class="text-danger"></h6>
                </div>
            </div>
        </div>
    </section>
</div>
<script>
    $(".list-unstyled").on("click", ".init", function() {
        $(this).closest(".list-unstyled").children('.item:not(.init)').toggle();
    });

    var allOptions = $(".list-unstyled").children('.item:not(.init)');
    $(".list-unstyled").on("click", ".item:not(.init)", function() {
        allOptions.removeClass('selected');
        $(this).addClass('selected');
        $(".list-unstyled").children('.init').html($(this).html());
        console.log($(this).html());
        allOptions.toggle();
    });
</script>
<script src="js/login-register.js"></script>
<?php
require '../templates/footer.php'
?>
