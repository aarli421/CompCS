<?php
require '../templates/helper.php';
require '../vendor/autoload.php';
require '../templates/header.php';

if (hasValue($_POST['forgotEmail'])) {

    $hash = md5(rand(0, 10000));
}
?>
<div class="background">
    <section data-stellar-background-ratio="0.5">
        <div class="container">
            <div class="form">
                <div class="form-panel one">
<?php
if (hasValue($_GET['hash']) && hasValue($_GET['email'])) {
?>

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
