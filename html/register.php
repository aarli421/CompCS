<?php
require('../templates/header.php');
require '../vendor/autoload.php';

$db = setupDb();
if (!$db) {
    echo "Database could not load";
}

if (hasValue($_POST['signUpUsername']) && hasValue($_POST['signUpPassword']) && hasValue($_POST['signUpEmail'])) {
    $email = $_POST['signUpEmail'];
    $username = $_POST['signUpUsername'];
    $hashedPw = hash('sha256', $_POST['signUpPassword']);
    $hash = md5(rand(0, 10000));

    // A note on the regex pattern used below (from the PHP source). It looks like there is some copyright on it of Michael Rushton. As stated: "Feel free to use and redistribute this code. But please keep this copyright notice."
    $pattern = '/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-+[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-+[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/iD';

    $sql = "
    START TRANSACTION;
    INSERT INTO `users` (`username`, `password`, `email`, `hash`)
    SELECT * FROM (SELECT ?, ?, ?, ?) AS temp 
    WHERE NOT EXISTS (SELECT * FROM `users` WHERE `username`=? OR `email`=?) LIMIT 1;
    COMMIT;";
    $sth = $db->prepare($sql);
    if (!$sth) {
        print_r($db->errorInfo());
        die();
    }
    $sth->execute([$username, $hashedPw, $email, $hash, $username, $email]);
    echo "Please verify your email";

    if (preg_match($pattern, $email) === 1) {
        $host = $_SERVER["HTTP_HOST"];
        $path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
        $verLink = 'http://' . $host . $path . '/verify.php?email=' . $email . '&hash=' . $hash;

        $handle = fopen('../private/keys.csv', 'r');
        $data = fgetcsv($handle, 5, ',');

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("noreply@compcs.codes", "CompCS");
        $email->setSubject("Verify your CompCS Account");
        $email->addTo("aaron.linear@gmail.com", "CompCS Codes User");
        $email->addContent(
            "text/html", "You have recently created an account with an username of $username<br>
                                  If you did not create an account, <strong>IGNORE THIS EMAIL</strong><br>
                                  <a href=$verLink>Verify your Email</a>"
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
    } else {
        echo "Invalid email!";
    }
}
?>
<form method="post" action="register.php">
    <input name="signUpEmail" type="email"> <br>
    <input name="signUpUsername" type="text"> <br>
    <input name="signUpPassword" type="password"> <br>
    <button type="submit">Sign Up</button>
</form>
<?php
require('../templates/footer.php');
?>
