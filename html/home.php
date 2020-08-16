<?php
require '../templates/helper.php';

if (hasValue($_POST['logout'])) unset($_SESSION['user']);

if (!isset($_SESSION['user'])) redirect("login");

require '../templates/header.php';

$db = setupDb();
if (!$db) {
    echo "Database could not load";
}
?>
<!-- Greeting Message -->
<section style="height: 50px;">
    <div class="container">
        <div style="text-align: left; padding-left: 17%; padding-top: 4%">
            <h1 >Welcome, <?php echo $_SESSION['user']; ?>!</h1>
        </div>
    </div>
</section>
<!-- Form-->
<section data-stellar-background-ratio="0.5" class="questionlist">
<center>
<table class="question">
<tr class="categories">
    <th class="problemnum">Problem #</th>
    <th>Problem name</th>
    <th>Points / Testcase</th>
    <th class="link">Link</th>
</tr>
<?php
$sth = $db->prepare("SELECT `points` FROM `users` WHERE `username`=?");
$sth->execute([$_SESSION['user']]);
$passArr = $sth->fetchAll();
$points = $passArr[0]['points'];

$sth = $db->prepare("SELECT * FROM `questions`");
$sth->execute();
$passArr = $sth->fetchAll();

$i = 0;
foreach ($passArr as $value) {
    if ($value['unlock_value'] <= $points) {
        $i++;
        ?>
        <tr class="categories">
            <td class="passed"><?php echo $i; ?></td>
            <td class="passed"><?php echo $value['name']; ?></td>
            <td class="passed"><?php echo $value['testcase_value']; ?></td>
            <td class="passed"><form method="get" action="question.php"><input name="questionName" value="<?php echo $value['name']; ?>" hidden=""><button type="submit" class="section-btn" style="margin:20px;">Go to question</button></form></td>
        </tr>
<?php   }
} ?>
</table>
</center>
</section>
<?php
require '../templates/footer.php';
?>