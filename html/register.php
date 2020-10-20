<?php
require '../templates/helper.php';
require '../templates/header.php';

$sth = $db->prepare("SELECT * FROM schools");
$sth->execute();
$schools = $sth->fetchAll();
?>
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
                            <div class="form-group"><label for="registerEmail">Email Address</label><input type="email" id="registerEmail" name="signUpEmail" required="required" pattern="<?php echo $emailReg; ?>" title="Please make sure your symbol is from @$!%*#?." /></div>
                            <div class="form-group"><label for="registerPassword">Password (>8 letters, numbers, and symbols)</label><input type="password" id="registerPassword" name="signUpPassword" required="required" pattern="<?php echo $passwordReg; ?>" /></div>
                            <div class="form-group"><label for="registerCPassword">Confirm Password</label><input type="password" id="registerCPassword" name="signUpCPassword" required="required" /></div>
                            <div class="form-group school"><label>School</label></div>
                            <div class="form-group">
                                <ul class="list-unstyled">
                                    <li class="init item">Select School</li>
                                    <?php
                                    $len = count($schools);
                                    for ($i = 0; $i < $len; $i++) {
                                    ?>
                                    <li class="item" data-value="value <?php echo $i + 1; ?>" style="<?php if ($i == 0) { ?>padding-top: 10% <?php } ?>"><?php echo $schools[$i]['name']; ?></li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            </div>
                            <div class="form-group agree after"><label class="form-remember"><input type="checkbox" name="tos" checked="checked" required="required"/>Agree to <a href="tos" target="_blank">Terms of Service</a></label></div>
                            <input id="registerSchool" name="signUpSchool" type="hidden" value="" required>
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
    function clear() {
        $("#registerUsername").val("");
        $("#registerEmail").val("");
        $("#registerPassword").val("");
        $("#registerCPassword").val("");
        $("#registerSchool").val("");
        $(".list-unstyled").children('.item:not(.init)').removeClass('selected');
        $(".list-unstyled").children('.init').html("Select School");
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

    $(".list-unstyled").on("click", ".init", function() {
        $(this).closest(".list-unstyled").children('.item:not(.init)').toggle();
    });

    var allOptions = $(".list-unstyled").children('.item:not(.init)');
    $(".list-unstyled").on("click", ".item:not(.init)", function() {
        allOptions.removeClass('selected');
        $(this).addClass('selected');
        $(".list-unstyled").children('.init').html($(this).html());
        $("#registerSchool").val($(this).html());
        // console.log($(this).html());
        allOptions.toggle();
    });
</script>
<?php
require '../templates/footer.php'
?>
