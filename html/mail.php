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
$mail->AddAddress("recipient-email@domain", "recipient-name");
$mail->SetFrom("from-email@gmail.com", "from-name");
$mail->AddReplyTo("reply-to-email@domain", "reply-to-name");
$mail->AddCC("cc-recipient-email@domain", "cc-recipient-name");
$mail->Subject = "Test is Test Email sent via Gmail SMTP Server using PHP Mailer";
$content = "<b>This is a Test Email sent via Gmail SMTP Server using PHP mailer class.</b>";

$mail->MsgHTML($content);
if(!$mail->Send()) {
    echo "Error while sending Email.";
    var_dump($mail);
} else {
    echo "Email sent successfully";
}