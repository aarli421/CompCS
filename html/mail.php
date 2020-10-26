<?php
require '../vendor/autoload.php';

$mail = new PHPMailer\PHPMailer\PHPMailer();
$mail->IsSMTP();
$mail->Mailer = "smtp";

$handle = fopen('../private/keys.csv', 'r');
$data = fgetcsv($handle, 5, ',');

$mail->SMTPDebug  = 1;
$mail->SMTPAuth   = TRUE;
$mail->SMTPSecure = "tls";
$mail->Port       = 587;
$mail->Host       = "smtp.gmail.com";
$mail->Username   = "compcscodes@gmail.com";
$mail->Password   = $data[3];

$mail->IsHTML(true);
$mail->AddAddress("aaron.linear@gmail.com", "Aaron Li");
$mail->SetFrom("noreply@compcs.codes", "CompCS");
$mail->Subject = "Test is Test Email sent via Gmail SMTP Server using PHP Mailer";
$content = file_get_contents("emails/register.html");;

$mail->MsgHTML($content);
if(!$mail->Send()) {
    echo "Error while sending Email.";
    var_dump($mail);
} else {
    echo "Email sent successfully";
}