<?php
require '../templates/helper.php';
require '../vendor/autoload.php';
require '../templates/header.php';

if (hasValue($_POST['forgotEmail'])) {
    $sth = $db->prepare("SELECT `user_id` FROM `users` WHERE `email`=? AND `active`=1");
    $sth->execute([$_POST['forgotEmail']]);
    $user = $sth->fetchAll();

    if (empty($user)) {
        redirect("forgot");
        exit();
    }

    $hash = md5(rand(0, 10000));

    $sth = $db->prepare("UPDATE `users` SET `hash`=?, `change_password`=1 WHERE `email`=? AND `active`=1");
    $sth->execute([$hash, $_POST['forgotEmail']]);
}

if (hasValue($_POST['newPassword']) && hasValue($_POST['newCPassword']) && hasValue($_POST['userID']) && hasValue($_POST['hash'])) {
    $sth = $db->prepare("SELECT `user_id` FROM `users` WHERE `user_id`=? AND `hash`=? AND `change_password`=1 AND `active`=1");
    $sth->execute([$_POST['userID'], $_POST['hash']]);
    $passArr = $sth->fetchAll();

    if (empty($passArr)) {
        redirect("forgot");
        exit();
    }

    $passwordRegPHP = "/" . $passwordReg . "/iD";

    if (preg_match($passwordRegPHP, $_POST['newPassword']) === 1) {
        $password = hash('sha256', $_POST['newPassword']);

        $sth = $db->prepare("UPDATE `users` SET `password`=?, `change_password`=0 WHERE `user_id`=? AND `active`=1");
        $sth->execute([$password, $_POST['userID']]);
        redirect("login");
        exit();
    }
}
?>
<div class="background">
    <section data-stellar-background-ratio="0.5">
        <div class="container">
            <div class="form">
                <div class="form-panel one">
<?php
if (hasValue($_GET['hash']) && hasValue($_GET['email'])) {
    $sth = $db->prepare("SELECT `user_id` FROM `users` WHERE `email`=? AND `hash`=? AND `change_password`=1 AND `active`=1");
    $sth->execute([$_GET['email'], $_GET['hash']]);
    $user = $sth->fetchAll();

    if (empty($user)) {
        redirect("forgot");
    }
?>
    <script>
        $('#newPassword, #newCPassword').on('keyup', function () {
            if ($("#newPassword").val() == $("#newCPassword").val()) {
                $("#newCPassword")[0].setCustomValidity('');
            } else {
                $("#newCPassword")[0].setCustomValidity('The passwords do not match.');
            }
        });
    </script>

    <div class="form-header">
        <h1>Change Password</h1>
    </div>
    <div class="form-content">
        <form id="forgot" method="post" action="forgot">
            <div class="form-group"><label for="newPassword">New Password</label><input type="password" id="newPassword" name="newPassword" required="required" pattern="<?php echo $passwordReg; ?>" /></div>
            <div class="form-group"><label for="newCPassword">Confirm Password</label><input type="password" id="newCPassword" name="newCPassword" required="required" pattern="<?php echo $passwordReg; ?>" /></div>
            <input type="hidden" name="userID" value="<?php echo $user[0]['user_id']; ?>">
            <input type="hidden" name="hash" value="<?php echo $_GET['hash']; ?>">
            <div class="form-group"><button form="login" type="submit">Request Change</button></div>
        </form>
    </div>
    <h6 id="forgotSuccess" class="text-success"></h6>
    <h6 id="forgotError" class="text-danger"></h6>
<?php
} else {
?>
    <div class="form-header">
        <h1>Forgot Password</h1>
    </div>
    <div class="form-content">
        <form id="forgot" method="post" action="forgot">
            <div class="form-group"><label for="forgotEmail">Email</label><input type="email" id="forgotEmail" name="forgotEmail" required="required" /></div>
            <div class="form-group"><button form="login" type="submit">Request Change</button></div>
        </form>
    </div>
    <h6 id="forgotSuccess" class="text-success"></h6>
    <h6 id="forgotError" class="text-danger"></h6>
<?php
}
?>
                </div>
            </div>
        </div>
    </section>
</div>
<?php
require '../templates/footer.php';
