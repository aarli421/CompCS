<?php
require '../templates/header.php';
require '../vendor/autoload.php';

$handle = fopen('../private/keys.csv', 'r');
$data = fgetcsv($handle, 5, ',');

$email = new \SendGrid\Mail\Mail();
$email->setFrom("noreply@compcs.codes", "Example User");
$email->setSubject("Sending with SendGrid is Fun");
$email->addTo("aaron.linear@gmail.com", "Example User");
$email->addContent("text/plain", "and easy to do anywhere, even with PHP");
$email->addContent(
    "text/html", "<strong>and easy to do anywhere, even with PHP</strong> <a href='https://www.google.com'>Verify your Email</a>"
);
$sendgrid = new \SendGrid($data[1]);
try {
    $response = $sendgrid->send($email);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: ' . $e->getMessage() . "\n";
}
?>
<?php
require("../templates/footer.php");
?>
