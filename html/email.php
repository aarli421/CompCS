<?php
require '/usr/share/php/libphp-phpmailer/class.phpmailer.php';
require '/usr/share/php/libphp-phpmailer/class.smtp.php';
$mail = new PHPMailer;
$mail->setFrom('noreply@compcs.codes');
$mail->addAddress('aaron.linear@gmail.com');
$mail->Subject = 'Message sent by PHPMailer';
$mail->Body = 'Hello! use PHPMailer to send email using PHP';
$mail->IsSMTP();
$mail->SMTPSecure = 'ssl';
$mail->Host = 'ssl://smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Port = 465;

//Set your existing gmail address as user name
$mail->Username = <a href="mailto:'compcscodes@gmail.com">'compcscodes@gmail.com</a>';

//Set the password of your gmail address here
$mail->Password = 'password';
if(!$mail->send()) {
    echo 'Email is not sent.';
    echo 'Email error: ' . $mail->ErrorInfo;
} else {
    echo 'Email has been sent.';
}
?>