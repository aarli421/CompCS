<?php
require '../../templates/helper.php';
require '../../vendor/autoload.php';

if (hasValue($_POST['signUpUsername']) && hasValue($_POST['signUpPassword']) && hasValue($_POST['signUpEmail']) && hasValue($_POST['tos'])) {
    $mail = $_POST['signUpEmail'];
    $username = $_POST['signUpUsername'];
    $password = $_POST['signUpPassword'];
    $hashedPw = hash('sha256', $_POST['signUpPassword']);
    $hash = md5(rand(0, 10000));

    $usernameRegPHP = "/" . $usernameReg . "/iD";
    $passwordRegPHP = "/" . $passwordReg . "/iD";
    $emailRegPHP = "/" . $emailReg . "/iD";

    $sth = $db->prepare("START TRANSACTION");
    $sth->execute();

    $sth = $db->prepare("SELECT EXISTS(SELECT * FROM `users` WHERE `username`=? OR `email`=?) LIMIT 1");
    $sth->execute([$username, $mail]);
    $passArr = $sth->fetchAll();

    if ($passArr[0][0] == 0) {
//        echo "Passed ";
        if ((preg_match($emailRegPHP, $mail) === 1) && (preg_match($usernameRegPHP, $username) === 1) && (preg_match($passwordRegPHP, $password) === 1)) {
            $msg = `sudo $scriptsDirectory/createUser.sh $username $password`;

            if (!hasValue($msg)) {
                $host = $_SERVER["HTTP_HOST"];
                $path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
                $verLink = 'https://' . $host . '/verify?email=' . $mail . '&hash=' . $hash;

                $handle = fopen('../../private/keys.csv', 'r');
                $data = fgetcsv($handle, 5, ',');

                $content = file_get_contents("emails/register.html");
                $search = "{}";

                $pos = strpos($content, $search);
                if ($pos !== false) {
                    $content = substr_replace($content, $username, $pos, strlen($search));
                } else {
                    echo $content;
                    die("Server error.");
                }

                $pos = strrpos($content, $search);
                if ($pos !== false) {
                    $content = substr_replace($content, $verLink, $pos, strlen($search));
                } else {
                    echo "second";
                    die("Server error.");
                }

                $email = new \SendGrid\Mail\Mail();
                $email->setFrom("noreply@compcs.codes", "CompCS");
                $email->setSubject("Verify CompCS Account");
                $email->addTo($mail, "CompCS Codes User");
                $email->addContent(
                    "text/html", $content
                );
                $sendgrid = new \SendGrid($data[1]);
                try {
                    $response = $sendgrid->send($email);
                    if ($response->statusCode() == 202) {
                        $sql = "
                    INSERT INTO `users` (`username`, `password`, `email`, `hash`)
                    SELECT * FROM (SELECT ? AS `username`, ? AS `password`, ? AS `email`, ? AS `hash`) AS temp 
                    WHERE NOT EXISTS (SELECT * FROM `users` WHERE `username`=? OR `email`=?) LIMIT 1;";
                        $sth = $db->prepare($sql);
                        $sth->execute([$username, $hashedPw, $mail, $hash, $username, $mail]);

                        $sth = $db->prepare("COMMIT");
                        $sth->execute();

//                        `mkdir ../users/$username`;

                        echo "Success";
                    } else {
                        die("Mail was unable to send.");
                    }
                } catch (Exception $e) {
                    die("Server error.");
//                    die("Caught exception: " . $e->getMessage() . "\n");
                }
            } else {
                echo "The username already exists";
            }
        } else {
            echo "Invalid email.";
        }
    } else {
        echo "The username / email already exists.";
    }
} else {
    echo "Form not filled completely.";
}
?>
