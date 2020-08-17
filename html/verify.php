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
<!-- Verification -->
<div class="background">
    <section data-stellar-background-ratio="0.5">
        <div class="container">
            <div class="form">
                <div class="form-toggle"></div>
                <div class="form-panel one">
                    <div class="form-header">
                        <h1 id="title">Verification Page</h1>
                    </div>
                    <p id="message"></p>
                </div>

            </div>
        </div>
    </section>
</div>
<script>
    let redirect = "https://www.compcs.codes/index";
<?php
if (hasValue($_GET['email']) && hasValue($_GET['hash'])) {
    $sth = $db->prepare("SELECT `username`, `email`, `hash`, `active` FROM `users` WHERE `email`=? AND `hash`=?");
    $sth->execute([$_GET['email'], $_GET['hash']]);
    $passArr = $sth->fetchAll();

    if (empty($passArr)) {
//        echo "Account not found!";
        ?>
        $("#title").html("Your account was not found!");
        redirect = "https://www.compcs.codes/register";
    <?php
    } else {
        if ($passArr[0]['active'] == 1) {
//            echo "Your account has already been made";
            ?>
            $("#title").html("Your account was already verified!");
            redirect = "https://www.compcs.codes/login";
            <?php
        } else {
            $username = $passArr[0]['username'];
            $msg = `mkdir users/$username`;

            if (hasValue($msg)) {
                ?>
                $("#title").html("The verification process encountered an error!");
                redirect = "https://www.compcs.codes/contact";
                <?php
            } else {
                $sth = $db->prepare("UPDATE users SET active=1 WHERE email=? AND hash=? AND active=0");
                $sth->execute([$_GET['email'], $_GET['hash']]);
                ?>
                $("#title").html("Your account was successfully verified!");
                redirect = "https://www.compcs.codes/login";
                <?php
            }
//            echo "Account Verified!";
        }
    }
}
?>
    $("#message").html("Redirecting you in 5 seconds... If you are not redirected you can click <a href='" + redirect + "'>this</a>.");
    setTimeout(function () {
        $(location).attr("href", redirect);
    }, 5000);
</script>
<?php
require '../templates/footer.php';