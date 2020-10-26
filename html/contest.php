<?php
require '../templates/helper.php';

if (!isset($_SESSION['user'])) {
    redirect("login");
    exit();
}

if (hasValue($_POST['finish'])) {
    $_SESSION['finish'] = true;
    redirect("contest");
    exit();
}

function time_to_interval($time) {
    $parts = explode(':',$time);
    return new DateInterval('PT'.$parts[0] .'H'. $parts[1] .'M'. $parts[2] .'S');
}

$redirect = false;
$success = "";
$error = "";

if (hasValue($_GET['code']) && !hasValue($_SESSION['contest'])) {
    $sth = $db->prepare("SELECT `contest_id`, `unlock_value`, `start`, `end`, `length` FROM `contests` WHERE `hash`=?");
    $sth->execute([$_GET['code']]);
    $contest = $sth->fetchAll();

    if (empty($contest)) {
        $error = "The code you entered was not found.";
    } else {
        $sth = $db->prepare("SELECT EXISTS(SELECT * FROM `tries` WHERE `user_id`=? AND `contest_id`=?) LIMIT 1");
        $sth->execute([$user_id, $contest[0]['contest_id']]);
        $passArr = $sth->fetchAll();

        $sth = $db->prepare("SELECT `points`, `admin` FROM users WHERE `user_id`=?");
        $sth->execute([$user_id]);
        $user = $sth->fetchAll();

        if ($user[0]['points'] >= $contest[0]['unlock_value']) {
            if ($passArr[0][0] == 0) {
                try {
                    $start = new DateTime($contest[0]['start']);
                    $end = new DateTime($contest[0]['end']);
                    $curr_date = getCurrDate();
                    $curr = new DateTime($curr_date);
                    $curr_copy = new DateTime($curr_date);

                    if (($curr >= $start && $curr < $end) || $user[0]['admin'] > 2) {
                        $_SESSION['contest'] = $contest[0]['contest_id'];

                        $curr->add(time_to_interval($contest[0]['length']));

                        if ($user[0]['admin'] == 0 && $curr > $end) $curr = $end;

                        $sth = $db->prepare("INSERT INTO `tries` (`user_id`, `contest_id`, `start`, `end`) VALUES (?, ?, ?, ?)");
                        $sth->execute([$user_id, $contest[0]['contest_id'], $curr_copy->format('Y-m-d H:i:s'), $curr->format('Y-m-d H:i:s')]);

                        $success = "If you are not redirected, please refresh the page.";

                        redirect("contest");
                        exit();
                    } else {
                        $error = "The contest has not begun or has already ended.";
                    }
                } catch (Exception $e) {
                    $error = "A server error occurred.";
                }
            } else {
                $_SESSION['contest'] = $contest[0]['contest_id'];
                $_SESSION['finish'] = true;
                redirect("contest");
                exit();
            }
        } else {
            $error = "You do not have enough points to access this contest.";
        }
    }
}

require '../templates/header.php';

if (!hasValue($_SESSION['contest'])) {
    $curr_date = getCurrDate();

    $sth = $db->prepare("SELECT `name`, `hash` FROM `contests` WHERE `start`<=? AND `end`>=?");
    $sth->execute([$curr_date, $curr_date]);
    $contests = $sth->fetchAll();
?>
<div class="background">
    <section data-stellar-background-ratio="0.5">
        <div class="container">
            <div class="form">
                <div class="form-panel one">
                    <div class="form-header">
                        <h1>Enter Contest</h1>
                        <?php
                        foreach ($contests as $id => $contest) {
                        ?>
                            <h6 class="text-desc"><?php echo $contest['name']; ?>: <?php echo $contest['hash']; ?></h6>
                        <?php
                        }
                        ?>
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
    $sth = $db->prepare("SELECT `start`, `end` FROM tries WHERE `user_id`=? AND `contest_id`=?");
    $sth->execute([$user_id, $_SESSION['contest']]);
    $try = $sth->fetchAll();

    $end = new DateTime($try[0]['end']);
    $curr = new DateTime(getCurrDate());
    ?>
    <link rel="stylesheet" href="css/progress.css">
    <link rel="stylesheet" href="css/home.css">
    <?php
    if ($curr >= $end || hasValue($_SESSION['finish'])) {
        $sth = $db->prepare("SELECT `name` FROM contests WHERE `contest_id`=?");
        $sth->execute([$_SESSION['contest']]);
        $contest = $sth->fetchAll();
        ?>
    <style>
        .form {
            height: auto;
            width: auto;
        }

        .form-panel {
            padding: 20px calc(5% + 60px) 60px 60px;
        }

        .question-line {
            width: 205%;
        }
    </style>
    <div class="background">
        <section data-stellar-background-ratio="0.5">
            <div class="container">
                <div class="form">
                    <div class="form-panel one" style="color:black">
                        <div class="categories">
                            <h1><?php echo $contest[0]['name']; ?> - Your Results</h1>
                            <h2 id="total">Total Score: </h2>
                        </div>
                        <ol class="questions">
                            <?php
                            $sth = $db->prepare("SELECT * FROM `questions` WHERE `contest_id`=? ORDER BY `unlock_value`, `testcase_value`");
                            $sth->execute([$_SESSION['contest']]);
                            $passArr = $sth->fetchAll();

                            $j = 0;
                            $total = 0;
                            foreach ($passArr as $value) {
                                $sth = $db->prepare("SELECT MAX(correct_cases) FROM `grades` WHERE `user_id`=? AND `question_id`=? AND `contest_id`=?");
                                $sth->execute([$user_id, $value['question_id'], $_SESSION['contest']]);
                                $max = $sth->fetchAll();
                                if (empty($max)) $max[0][0] = 0;

                                $question_points = $max[0][0] * $value['testcase_value'];
                                $total += $question_points;
                                ?>
                                <li class="question">
                                    <div class="categories">
                                        <div><hr class="line question-line"></div>
                                        <div class="categories-div">
                                            <div class="category">
                                                <h4>Problem</h4>
                                            </div>
                                            <div class="category">
                                                <h4>Your Points</h4>
                                            </div>
                                        </div>
                                        <div class="categories-div">
                                            <div class="category">
                                                <h5><?php echo $value['name']; ?></h5>
                                            </div>
                                            <div class="category">
                                                <h5><?php echo $question_points; ?></h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="progress-bar-div">
                                        <div id="progress-wrapper<?php echo $j; ?>" class="progress-wrap progress" data-progress-percent="<?php echo round(($max[0][0] / $value['testcases']) * 100,2); ?>">
                                            <div id="progress-bar<?php echo $j; ?>" class="progress-bar progress"></div>
                                        </div>
                                    </div>
                                </li>
                                <?php
                                $j++;
                            }?>
                        </ol>
                        <script>
                            $("#total").append("<?php echo $total; ?>");
                        </script>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <?php
        $sth = $db->prepare("SELECT EXISTS(SELECT * FROM `results` WHERE `user_id`=? AND `contest_id`=?) LIMIT 1");
        $sth->execute([$user_id, $_SESSION['contest']]);
        $exists = $sth->fetchAll();

        if ($exists[0][0] == 0) {
            $sth = $db->prepare("INSERT INTO `results` (`user_id`, `contest_id`, `score`, `timestamp`) VALUES (?, ?, ?, ?)");
            $sth->execute([$user_id, $_SESSION['contest'], $total, getCurrDate()]);
        }

        unset($_SESSION['contest']);
        unset($_SESSION['finish']);
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
                    $sth = $db->prepare("SELECT * FROM `questions` WHERE `contest_id`=? ORDER BY `testcase_value`");
                    $sth->execute([$_SESSION['contest']]);
                    $passArr = $sth->fetchAll();

                    $j = 0;
                    foreach ($passArr as $value) {
                        $locked = false;
                        if ($points < $value['unlock_value']) {
                            $locked = true;
                        }

                        $sth = $db->prepare("SELECT MAX(correct_cases) FROM `grades` WHERE `user_id`=? AND `question_id`=? AND `contest_id`=?");
                        $sth->execute([$user_id, $value['question_id'], $_SESSION['contest']]);
                        $max = $sth->fetchAll();
                        if (empty($max)) $max[0][0] = 0;
                        ?>
                        <li class="question">
                            <div class="categories">
                                <div><hr class="line question-line"></div>
                                <a href="https://www.compcs.codes/question?questionName=<?php echo $value['name']; ?>">
                                    <div class="categories-div">
                                        <div class="category">
                                            <h4>Problem</h4>
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
                        </li>
                        <?php
                        $j++;
                    }?>
                </ol>
            </ol>
        </div>
    </section>
<?php
    }
    ?>
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
require '../templates/footer.php';
