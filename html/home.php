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
<section data-stellar-background-ratio="0.5">
    <div>
        <ol>
            <div style="padding-top: 5%"><hr style="height:10px; width: 85%; border-width:0; float: left; color:gray; background-color:gray"></div>
            <li>
                <div>
                    <br>
                    <br>
                    <h2 class="division">Points: 1000 - 2000</h2>
                </div>
            </li>
            <ol>
                <li class="questions">

                    <a href="">
                        <div style="width: 80%; display: table; padding-top: 1%">
                            <div><hr style="height:2px; width: 262%; border-width:0; color:gray; background-color:gray"></div>
                            <div style="display: table-row; height: 100px;">
                                <div style="width: 33%; display: table-cell">
                                    <h4>Problem: Where am I</h4>
                                </div>
                                <div style="width: 33%; height: 65%; display: table-cell">
                                    <h4>Unlock Value: 10</h4>
                                </div>
                                <div style="width: 33%; display: table-cell">
                                    <h4>Points/Testcase: 10</h4>
                                </div>
                            </div>
                        </div>
                    </a>
                    <div style="width: 70%; display: table">
                        <div class="progress-wrap progress" data-progress-percent="90">
                            <div class="progress-bar progress"></div>
                        </div>
                    </div>
                </li>
                <li class="questions">
                    <a href="">
                        <div style="width: 80%; display: table; padding-top: 1%">
                            <div><hr style="height:2px; width: 262%; border-width:0; color:gray; background-color:gray"></div>
                            <div style="display: table-row; height: 100px;">
                                <div style="width: 33%; display: table-cell">
                                    <h4>Swapity-Swapity Swap</h4>
                                </div>
                                <div style="width: 33%; height: 65%; display: table-cell">
                                    <h4>Unlock Value: 10</h4>
                                </div>
                                <div style="width: 33%; display: table-cell">
                                    <h4>Points/Testcase: 10</h4>
                                </div>
                            </div>
                        </div>
                    </a>
                    <div style="width: 70%; display: table">
                        <div class="progress-wrap progress" data-progress-percent="90">
                            <div class="progress-bar progress"></div>
                        </div>
                    </div>
            </ol>
            </li>
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