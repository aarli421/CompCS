<?php
require '../templates/helper.php';

if (!isset($_SESSION['user'])) {
    redirect("login");
    exit();
}

if (hasValue($_SESSION['contest'])) {
    $sth = $db->prepare("SELECT `start`, `end` FROM tries WHERE `user_id`=? AND `contest_id`=?");
    $sth->execute([$user_id, $_SESSION['contest']]);
    $try = $sth->fetchAll();

    echo "HELAIDOISHDOHA";

    $diff = strtotime($try[0]['end']) - strtotime(getCurrDate());
    if ($diff < 0) $diff = 0;
    header("refresh: {$diff};url= https://www.compcs.codes/contest");
    exit();
}

require '../templates/header.php';

function time_to_interval($time) {
    $parts = explode(':',$time);
    return new DateInterval('PT'.$parts[0] .'H'. $parts[1] .'M'. $parts[2] .'S');
}

$redirect = false;
$success = "";
$error = "";

if (hasValue($_GET['code']) && !hasValue($_SESSION['contest'])) {
    $sth = $db->prepare("SELECT `contest_id`, `start`, `end`, `length` FROM `contests` WHERE `hash`=?");
    $sth->execute([$_GET['code']]);
    $contest = $sth->fetchAll();

    if (empty($contest)) {
        $error = "The code you entered was not found.";
    } else {
        $sth = $db->prepare("SELECT EXISTS(SELECT * FROM `tries` WHERE `user_id`=? AND `contest_id`=?) LIMIT 1");
        $sth->execute([$user_id, $contest[0]['contest_id']]);
        $passArr = $sth->fetchAll();

        if ($passArr[0][0] == 0) {
            try {
                $start = new DateTime($contest[0]['start']);
                $end = new DateTime($contest[0]['end']);
                $curr_date = getCurrDate();
                $curr = new DateTime($curr_date);
                $curr_copy = new DateTime($curr_date);

                if ($curr >= $start && $curr < $end) {
                    $_SESSION['contest'] = $contest[0]['contest_id'];

                    $curr->add(time_to_interval($contest[0]['length']));

                    if ($curr > $end) $curr = $end;

                    $sth = $db->prepare("INSERT INTO `tries` (`user_id`, `contest_id`, `start`, `end`) VALUES (?, ?, ?, ?)");
                    $sth->execute([$user_id, $contest[0]['contest_id'], $curr_copy->format('Y-m-d H:i:s'), $curr->format('Y-m-d H:i:s')]);

                    $success = "If you are not redirected, please refresh the page.";
                } else {
                    $error = "The contest has not begun or has already ended.";
                }
            } catch (Exception $e) {
                $error = "A server error occurred.";
            }
        } else {
            $error = "You have already taken this contest.";
        }
    }
}

if (!hasValue($_SESSION['contest'])) {
?>
<div class="background">
    <section data-stellar-background-ratio="0.5">
        <div class="container">
            <div class="form">
                <div class="form-panel one">
                    <div class="form-header">
                        <h1>Enter Contest</h1>
                    </div>
                    <div class="form-content">
                        <form id="contest" action="contest" method="get">
                            <div class="form-group"><label for="code">Contest Code</label><input type="text" id="code" name="code" required="required" /></div>
                            <div class="form-group"><button form="contest" type="submit">Submit Code and Start</button></div>
                        </form>
                    </div>
                    <h6 id="contestSuccess" class="text-success"></h6>
                    <h6 id="contestError" class="text-danger"></h6>
                    <script>
                        $("#contestSuccess").html("<?php echo $success; ?>");
                        $("#contestError").html("<?php echo $error; ?>");
                        <?php
                        if ($redirect) {
                        ?>
                            $(location).attr("href", "https://www.compcs.codes/contest");
                        <?php
                        }
                        ?>
                    </script>
                </div>
            </div>
        </div>
    </section>
</div>
<?php
} else {
    $end = new DateTime($try[0]['end']);
    $curr = new DateTime(getCurrDate());

    if ($curr >= $end) {
        echo "Finished";
        unset($_SESSION['contest']);
    } else {
        $sth = $db->prepare("SELECT `start`, `end` FROM tries WHERE `user_id`=? AND `contest_id`=?");
        $sth->execute([$user_id, $_SESSION['contest']]);
        $try = $sth->fetchAll();

        $sth = $db->prepare("SELECT `points` FROM `users` WHERE `username`=?");
        $sth->execute([$_SESSION['user']]);
        $passArr = $sth->fetchAll();
        $points = $passArr[0]['points'];

        $sth = $db->prepare("SELECT `name` FROM contests WHERE `contest_id`=?");
        $sth->execute([$_SESSION['contest']]);
        $contest = $sth->fetchAll();
?>
    <link rel="stylesheet" href="css/progress.css">
    <link rel="stylesheet" href="css/home.css">
    <!-- Greeting Message -->
    <section data-stellar-background-ratio="0.5" style="padding-bottom: 25px;">
        <div class="container">
            <h1>Welcome, <?php echo $_SESSION['user']; ?>!</h1>
            <h3 class="problemtitle" style="margin-top: 0px">Points: <?php echo $points; ?></h3>
        </div>
    </section>
    <section data-stellar-background-ratio="0.5" style="padding-top: 0px;">
        <div class="container">
            <ol class="home-divisions">
                <li>
                    <div class="division-title"><hr class="line div-line"></div>
                    <div>
                        <br>
                        <br>
                        <h2 class="division"><?php echo $contest[0]['name']; ?></h2>
                        <h4 id="countdown">Updating...</h4>
                        <script>
                            var countDownDate = new Date(<?php echo strtotime($try[0]['end']); ?> * 1000).getTime();
                        </script>
                        <script src="js/countdown.js"></script>
                    </div>
                </li>
                <ol class="questions">
                    <?php
                    $sth = $db->prepare("SELECT * FROM `questions` WHERE `contest_id`=? ORDER BY `unlock_value`, `testcase_value`");
                    $sth->execute([$_SESSION['contest']]);
                    $passArr = $sth->fetchAll();

                    $j = 0;
                    foreach ($passArr as $value) {
                        $locked = false;
                        if ($points < $value['unlock_value']) {
                            $locked = true;
                        }

                        $sth = $db->prepare("SELECT MAX(correct_cases) FROM `grades` WHERE `user_id`=? AND `question_id`=?");
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
                    }?>
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
    }
}
require '../templates/footer.php';
