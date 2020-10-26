<?php
require '../../templates/helper.php';
require '../../vendor/autoload.php';

if (hasValue($_POST['signUpUsername']) && hasValue($_POST['signUpPassword']) && hasValue($_POST['signUpEmail']) && hasValue($_POST['signUpSchool']) && hasValue($_POST['tos'])) {
    $mail = $_POST['signUpEmail'];
    $username = $_POST['signUpUsername'];
    $password = $_POST['signUpPassword'];
    $school = $_POST['signUpSchool'];
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
            $sth = $db->prepare("SELECT * FROM schools");
            $sth->execute();
            $schools = $sth->fetchAll();

            $works = false;
            foreach ($schools as $key => $value) {
                if ($value['name'] == $school) {
                    $works = true;
                    break;
                }
            }

            if ($works) {
//            $msg = `sudo $scriptsDirectory/createUser.sh $username $password`;

//            if (!hasValue($msg)) {
                $host = $_SERVER["HTTP_HOST"];
                $path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
                $verLink = 'https://' . $host . '/verify?email=' . $mail . '&hash=' . $hash;

                $handle = fopen('../../private/keys.csv', 'r');
                $data = fgetcsv($handle, 5, ',');

                $content = file_get_contents("../emails/register.html");
                $search = "{}";

                $pos = strpos($content, $search);
                if ($pos !== false) {
                    $content = substr_replace($content, $username, $pos, strlen($search));
                } else {
                    die("Server error.");
                }

                $pos = strpos($content, $search, $pos + 1);
                if ($pos !== false) {
                    $content = substr_replace($content, $verLink, $pos, strlen($search));
                } else {
                    die("Server error.");
                }

                $email = new PHPMailer\PHPMailer\PHPMailer();
                $email->IsSMTP();
                $email->Mailer = "smtp";

                $handle = fopen('../private/keys.csv', 'r');
                $data = fgetcsv($handle, 5, ',');

                //$mail->SMTPDebug  = 1;
                $email->SMTPAuth   = TRUE;
                $email->SMTPSecure = "tls";
                $email->Port       = 587;
                $email->Host       = "smtp.gmail.com";
                $email->Username   = "compcscodes@gmail.com";
                $email->Password   = $data[3];

                $email->IsHTML(true);
                $email->AddAddress($mail, $username);
                $email->SetFrom("noreply@compcs.codes", "CompCS");
                $email->Subject = "CompCS Verification";

                $mail->MsgHTML($content);

                if ($mail->Send()) {
                    $sql = "
                INSERT INTO `users` (`username`, `password`, `email`, `school`, `hash`)
                SELECT * FROM (SELECT ? AS `username`, ? AS `password`, ? AS `email`, ? as `school`, ? AS `hash`) AS temp 
                WHERE NOT EXISTS (SELECT * FROM `users` WHERE `username`=? OR `email`=?) LIMIT 1;";
                    $sth = $db->prepare($sql);
                    $sth->execute([$username, $hashedPw, $mail, $school, $hash, $username, $mail]);

                    $sth = $db->prepare("COMMIT");
                    $sth->execute();

//                        `mkdir ../users/$username`;

                    echo "Success";
                } else {
                    die("Mail was unable to send.");
                }
            } else {
                echo "Your school is not registered as part of CCC.";
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
