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
            <?php
            $divisions = array(0 => array('lower' => 0, 'upper' => 9999));
            $numDivisions = 1;

            for ($i = 0; $i < $numDivisions; $i++) {
                $lower = $divisions[$i]['lower'];
                $upper = $divisions[$i]['upper'];
                ?>
            <li>
                <div class="division-title"><hr class="line div-line"></div>
                <div>
                    <br>
                    <br>
                    <h2 class="division">Points: <?php echo $lower; ?> - <?php echo $upper; ?></h2>
                </div>
            </li>
            <ol class="questions">
                <?php
                $sth = $db->prepare("SELECT * FROM `questions` WHERE `unlock_value`>=? AND `unlock_value`<? ORDER BY `unlock_value`");
                $sth->execute([$lower, $upper]);
                $passArr = $sth->fetchAll();



                $i = 0;
                foreach ($passArr as $value) {
                    if ($points <= $value['unlock_value']) {
                        $i++;
                    }
                    $sth = $db->prepare("SELECT MAX(correct_cases) FROM grades WHERE user_id=? AND question_id=?");
                    $sth->execute([$user_id, $value['question_id']]);
                    $max = $sth->fetchAll();
                    if (empty($max)) $max[0][0] = 0;
                ?>
                    <li class="question">
                        <div class="categories">
                            <div><hr class="line question-line"></div>
                            <a href="">
                                <div class="categories-div">
                                    <div class="category">
                                        <h4>Problem</h4>
                                    </div>
                                    <div class="category">
                                        <h4>Unlock Value</h4>
                                    </div>
                                    <div class="category">
                                        <h4>Total Points</h4>
                                    </div>
                                </div>
                                <div class="categories-div">
                                    <div class="category">
                                        <h5><?php echo $value['name']; ?></h5>
                                    </div>
                                    <div class="category">
                                        <h5><?php echo $value['unlock_value']; ?></h5>
                                    </div>
                                    <div class="category">
                                        <h5><?php echo ($value['testcase_value'] * $value['testcases']);?></h5>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="progress-bar-div">
                            <div id="progress-bar<?php echo $i; ?>" class="progress-wrap progress" data-progress-percent="<?php echo round(($max[0][0] / $value['testcases']) * 100,2); ?>">
                                <div class="progress-bar progress"></div>
                            </div>
                        </div>
                    </li>
                <?php
                }
            }
            ?>
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
<script>
    // on page load...
    moveProgressBar();
    // on browser resize...
    $(window).resize(function() {
        moveProgressBar();
    });

    // SIGNATURE PROGRESS
    function moveProgressBar() {
        console.log("moveProgressBar");
        let i;
        for (i = 0; i < <?php echo $i; ?>; i++) {
            var getPercent = ($('#progress-bar' + i).data('progress-percent') / 100);
            var getProgressWrapWidth = $('#progress-bar' + i).width();
            var progressTotal = getPercent * getProgressWrapWidth;
            var animationLength = 1000;

            // on page load, animate percentage bar to data percentage length
            // .stop() used to prevent animation queueing
            $('.progress-bar').stop().animate({
                left: progressTotal
            }, animationLength);
        }
    }
</script>
<?php
require '../templates/footer.php';
?>