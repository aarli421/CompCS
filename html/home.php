<?php
require '../templates/helper.php';

if (hasValue($_POST['logout'])) {
    unset($_SESSION['user']);
}

if (!isset($_SESSION['user'])) {
    redirect("login");
    exit();
}

$sth = $db->prepare("SELECT `points` FROM `users` WHERE `username`=?");
$sth->execute([$_SESSION['user']]);
$passArr = $sth->fetchAll();
$points = $passArr[0]['points'];

require '../templates/header.php';
?>
<link rel="stylesheet" href="css/progress.css">
<!-- Greeting Message -->
<section data-stellar-background-ratio="0.5" style="padding-bottom: 25px;">
    <div class="container">
        <h1>Welcome, <?php echo $_SESSION['user']; ?>!</h1>
        <h3 class="problemtitle" style="margin-top: 0px">Points: <?php echo $points; ?></h3>
    </div>
</section>
<section data-stellar-background-ratio="0.5" style="padding-top: 0px;">
    <div class="container">
        <ol>
            <div style="padding-top: 5%"><hr class="line div-line"></div>
            <li>
                <div>
                    <br>
                    <br>
                    <h2 class="division">Points: 1000 - 2000</h2>
                </div>
            </li>
            <ol class="questions">
                <li class="question">
                    <div class="categories">
                        <div><hr class="line question-line"></div>
                        <a href="">
                            <div class="categories-div">
                                <div class="category">
                                    <h4>Problem: Where am I</h4>
                                </div>
                                <div class="category">
                                    <h4>Unlock Value: 10</h4>
                                </div>
                                <div class="category">
                                    <h4>Points/Testcase: 10</h4>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="progress-bar-div">
                        <div class="progress-wrap progress" data-progress-percent="90">
                            <div class="progress-bar progress"></div>
                        </div>
                    </div>
                </li>
                <li class="question">
                    <div class="categories">
                        <div><hr class="line question-line"></div>
                        <a href="">
                            <div class="categories-div">
                                <div class="category">
                                    <h4>Swapity-Swapity Swap</h4>
                                </div>
                                <div class="category">
                                    <h4>Unlock Value: 10</h4>
                                </div>
                                <div class="category">
                                    <h4>Points/Testcase: 10</h4>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="progress-bar-div">
                        <div class="progress-wrap progress" data-progress-percent="90">
                            <div class="progress-bar progress"></div>
                        </div>
                    </div>
                </li>
            </ol>
        </ol>
    </div>
</section>
<!---->
<!--<section data-stellar-background-ratio="0.5" style="padding-top: 10px;">-->
<!--<div class="container">-->
<!--<table class="question">-->
<!--<tr class="categories">-->
<!--    <th class="problemnum">Problem #</th>-->
<!--    <th>Problem name</th>-->
<!--    <th>Points / Testcase</th>-->
<!--    <th class="link">Link</th>-->
<!--</tr>-->
<?php
//$sth = $db->prepare("SELECT * FROM `questions` ORDER BY `unlock_value`");
//$sth->execute();
//$passArr = $sth->fetchAll();
//
//$i = 0;
//foreach ($passArr as $value) {
//    if ($value['unlock_value'] <= $points) {
//        $i++;
//        ?>
<!--        <tr class="categories">-->
<!--            <td>--><?php //echo $i; ?><!--</td>-->
<!--            <td>--><?php //echo $value['name']; ?><!--</td>-->
<!--            <td>--><?php //echo $value['testcase_value']; ?><!--</td>-->
<!--            <td><form method="get" action="question"><input name="questionName" value="--><?php //echo $value['name']; ?><!--" hidden=""><button type="submit" class="section-btn" style="margin:20px;">Go to question</button></form></td>-->
<!--        </tr>-->
<?php //  }
//} ?>
<!--</table>-->
<!--</div>-->
<!--</section>-->
<script src="js/progress.js"></script>
<?php
require '../templates/footer.php';
?>