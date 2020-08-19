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
            $sth = $db->prepare("SELECT * FROM `divisions`");
            $sth->execute();
            $divisions = $sth->fetchAll();

            $numDivisions = sizeof($divisions);
            $j = 0;

            for ($i = 0; $i < $numDivisions; $i++) {
                $lower = $divisions[$i]['lower'];
                $upper = $divisions[$i]['upper'];
                ?>
            <li>
                <div class="division-title"><hr class="line div-line"></div>
                <div>
                    <br>
                    <br>
                    <h2 class="division">Division <?php echo $i + 1; ?></h2>
                    <h4>Points: <?php echo $lower; ?> - <?php echo $upper; ?></h4>
                </div>
            </li>
            <ol class="questions">
                <?php
                $sth = $db->prepare("SELECT * FROM `questions` WHERE `unlock_value`>=? AND `unlock_value`<=? ORDER BY `unlock_value`");
                $sth->execute([$lower, $upper]);
                $passArr = $sth->fetchAll();

                foreach ($passArr as $value) {
                    $locked = false;
                    if ($points < $value['unlock_value']) {
                        $locked = true;
                    }

                    $sth = $db->prepare("SELECT MAX(correct_cases) FROM grades WHERE user_id=? AND question_id=?");
                    $sth->execute([$user_id, $value['question_id']]);
                    $max = $sth->fetchAll();
                    if (empty($max)) $max[0][0] = 0;
                ?>
                    <li class="question">
                        <?php
                        if ($locked) {
                        ?>
                        <div class="locked">
                        <?php
                        }
                        ?>
                            <div class="categories">
                                <div><hr class="line question-line"></div>
                                <a href="https://www.compcs.codes/question?questionName=<?php echo $value['name']; ?>">
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
                                <div id="progress-wrapper<?php echo $j; ?>" class="progress-wrap progress" data-progress-percent="<?php echo round(($max[0][0] / $value['testcases']) * 100,2); ?>">
                                    <div id="progress-bar<?php echo $j; ?>" class="progress-bar progress"></div>
                                </div>
                            </div>
                        <?php
                        if ($locked) {
                        ?>
                        </div>
                        <?php
                        }
                        ?>
                    </li>
                <?php
                    if ($points >= $value['unlock_value']) {
                        $j++;
                    }
                }
            }
            ?>
            </ol>
        </ol>
    </div>
</section>
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
        for (i = 0; i < <?php echo $j; ?>; i++) {
            var getPercent = ($('#progress-wrapper' + i.toString()).data('progress-percent') / 100);
            var getProgressWrapWidth = $('#progress-wrapper' + i.toString()).width();

            console.log(i);
            console.log(getPercent);

            var progressTotal = getPercent * getProgressWrapWidth;
            var animationLength = 1000;

            // on page load, animate percentage bar to data percentage length
            // .stop() used to prevent animation queueing
            $('#progress-bar' + i.toString()).stop().animate({
                left: progressTotal
            }, animationLength);
        }
    }
</script>
<?php
require '../templates/footer.php';
?>