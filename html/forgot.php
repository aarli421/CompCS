<?php
require '../templates/helper.php';
require '../vendor/autoload.php';
require '../templates/header.php';

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

if (hasValue($_POST['forgotEmail'])) {
    $sth = $db->prepare("SELECT `change_password` FROM `users` WHERE `email`=? AND `active`=1");
    $sth->execute([$_POST['forgotEmail']]);
    $user = $sth->fetchAll();

    if (empty($user)) {
        redirect("forgot");
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
        <form id="change" action="forgot" method="post">
            <div class="form-group"><label for="newPassword">New Password</label><input type="password" id="newPassword" name="newPassword" required="required" pattern="<?php echo $passwordReg; ?>" /></div>
            <div class="form-group"><label for="newCPassword">Confirm Password</label><input type="password" id="newCPassword" name="newCPassword" required="required" pattern="<?php echo $passwordReg; ?>" /></div>
            <input type="hidden" name="userID" value="<?php echo $user[0]['user_id']; ?>">
            <input type="hidden" name="hash" value="<?php echo $_GET['hash']; ?>">
            <div class="form-group"><button form="change" type="submit">Change Password</button></div>
        </form>
    </div>
    <h6 id="changeSuccess" class="text-success"></h6>
    <h6 id="changeError" class="text-danger"></h6>
<?php
} else {
?>
    <div class="form-header">
        <h1>Forgot Password</h1>
    </div>
    <div class="form-content">
        <form id="forgot" action="forgot" method="post">
            <div class="form-group"><label for="forgotEmail">Email</label><input type="email" id="forgotEmail" name="forgotEmail" required="required" /></div>
            <div class="form-group"><button form="forgot" type="submit">Request Change</button></div>
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
if (hasValue($_POST['forgotEmail'])) {
    if ($user[0]['change_password'] == 1) {
        ?>
        <script>
            $("#forgotSuccess").html("");
            $("#forgotError").html("You have already request to change your password. If this is a mistake please contact us at compcscodes@gmail.com.");
        </script>
        <?php
    } else {

        $hash = md5(rand(0, 10000));

        $host = $_SERVER["HTTP_HOST"];
        $path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
        $forgotLink = 'https://' . $host . '/forgot?email=' . $_POST['forgotEmail'] . '&hash=' . $hash;

        $handle = fopen('../private/keys.csv', 'r');
        $data = fgetcsv($handle, 5, ',');

        $content = file_get_contents("emails/forgot.html");
        $search = "{}";

        $pos = strpos($content, $search);
        if ($pos !== false) {
            $content = substr_replace($content, $forgotLink, $pos, strlen($search));
        } else {
            die("Server error.");
        }

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("noreply@compcs.codes", "CompCS");
        $email->setSubject("Password Change CompCS Account");
        $email->addTo($_POST['forgotEmail'], "CompCS Codes User");
        $email->addContent(
            "text/html", $content
        );
        $sendgrid = new \SendGrid($data[1]);
        try {
            $response = $sendgrid->send($email);

            $sth = $db->prepare("UPDATE `users` SET `hash`=?, `change_password`=1 WHERE `email`=? AND `active`=1");
            $sth->execute([$hash, $_POST['forgotEmail']]);
            ?>
            <script>
                $("#forgotSuccess").html("Successfully sent email. Please check your email for further inquiry.");
                $("#forgotError").html("");
            </script>
            <?php
        } catch (Exception $e) {
            echo 'Caught exception: ' . $e->getMessage() . "\n";
            ?>
            <script>
                $("#forgotSuccess").html("");
                $("#forgotError").html("Server error!");
            </script>
            <?php
        }
    }
}

require '../templates/footer.php';
