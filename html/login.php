<?php
require '../templates/helper.php';
require '../templates/header.php';
?>
<script>
    function clear() {
        $("#loginUsername").val("");
        $("#loginPassword").val("");
    }

    $(function () {
        $("#login").submit(function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                url: "https://www.compcs.codes/ajax/login.php",
                type: "POST",
                data: formData,
                success: function(data) {
                    if (data == "Success") {
                        $("#loginSuccess").css("display", "inline");
                        $("#loginSuccess").html("Successfully logged you in. Redirecting you... If you are not redirected you can click <a href='http://www.compcs.codes/home'>this</a>.");
                        $("#loginError").css("display", "none");
                        $("#loginError").html("");
                        $(location).attr("href", "https://www.compcs.codes/home");
                        // setTimeout(function () {
                        //     $(location).attr("href", "https://www.compcs.codes/home");
                        // }, 3000);
                    } else {
                        $("#loginSuccess").css("display", "none");
                        $("#loginSuccess").html("");
                        $("#loginError").css("display", "inline");
                        $("#loginError").html(data);
                        setTimeout(function () {
                            $("#loginError").html("");
                        }, 3000);
                    }

                    clear();
                },
                error: function(data) {
                    $("#loginSuccess").css("display", "none");
                    $("#loginSuccess").html("");
                    $("#loginError").css("display", "inline");
                    $("#loginError").html(data);
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
                    <h1>Account Login</h1>
                </div>
                <div class="form-content">
                    <form id="login" method="post">
                        <div class="form-group"><label for="loginUsername">Username</label><input type="text" id="loginUsername" name="loginUsername" required="required" /></div>
                        <div class="form-group"><label for="loginPassword">Password</label><input type="password" id="loginPassword" name="loginPassword" required="required" /></div>
                        <div class="form-group"><a class="form-recovery" href="forgot">Forgot Password?</a></div>
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
<?php
require '../templates/footer.php'
?>
