<?php
require '../templates/helper.php';

if (hasValue($_POST['logout'])) {
    session_unset();
    session_destroy();
    redirect("login");
    exit();
}

if (!isset($_SESSION['user'])) {
    redirect("login");
    exit();
}

$sth = $db->prepare("SELECT `points`, `admin` FROM `users` WHERE `username`=?");
$sth->execute([$_SESSION['user']]);
$user = $sth->fetchAll();
$points = $user[0]['points'];

require '../templates/header.php';
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
            <?php
            $sth = $db->prepare("SELECT * FROM `divisions`");
            $sth->execute();
            $divisions = $sth->fetchAll();

            $numDivisions = sizeof($divisions);
            $j = 0;

            for ($i = 0; $i < $numDivisions; $i++) {
                $lower = $divisions[$i]['lower'];
                $upper = $divisions[$i]['upper'];
                $bonus = $divisions[$i]['bonus'];

                $name = $divisions[$i]['name'];
                $name_id = strtolower(preg_replace('/\s*/', '', $name));

                if ($bonus != 0) $upper = $points;
                ?>
            <li id="<?php echo $name_id; ?>">
                <div class="division-title"><hr class="line div-line"></div>
                <div>
                    <br>
                    <br>
                    <h2 class="division"><?php echo $name; ?></h2>
                    <h4>Points: <?php echo $lower; ?> - <?php echo $upper; ?></h4>
                </div>
            </li>
            <ol class="questions">
                <?php
//                if ($bonus == 2) {
//                    $curr_date = getCurrDate();
//
//                    $sth = $db->prepare("UPDATE `questions` INNER JOIN `contests` ON `questions`.`contest_id`=`contests`.`contest_id` SET `testcase_value`=0 WHERE `end`<?");
//                    $sth->execute([$curr_date]);
//
//                    $sth = $db->prepare("SELECT `questions`.`name`, `contests`.`unlock_value`, `question_id`, `testcase_value`, `testcases` FROM `questions` INNER JOIN `contests` ON `questions`.`contest_id`=`contests`.`contest_id` WHERE `contests`.`unlock_value`<=? AND `end`<? AND `admin`<=?");
//                    $sth->execute([$upper, $curr_date, $user[0]['admin']]);
//                    $passArr = $sth->fetchAll();
//                } else {
//                    $sth = $db->prepare("SELECT * FROM `questions` WHERE `unlock_value`>=? AND `unlock_value`<=? AND `admin`<=? AND `bonus`=? AND `contest_id`=0 ORDER BY `unlock_value`");
//                    $sth->execute([$lower, $upper, $user[0]['admin'], $bonus]);
//                    $passArr = $sth->fetchAll();
//                }

                $sth = $db->prepare("SELECT * FROM `sections` WHERE `division_id`=? AND `admin`<=? ORDER BY `unlock_value`");
                $sth->execute([$divisions[$i]['division_id'], $user[0]['admin']]);
                $sections = $sth->fetchAll();

                foreach ($sections as $value) {
                ?>
                    <li class="question">
                        <div class="categories">
                            <div><hr class="line question-line"></div>
                            <a href="https://www.compcs.codes/section?id=<?php echo $value['section_id']; ?>">
                                <span class="topic"><?php echo $value['name']; ?></span>
                            </a>
                        </div>
                    </li>
                <?php
                }
                ?>
            </ol>
            <?php
            }
            ?>
        </ol>
    </div>
</section>
<script>
    function myFunction() {
        document.getElementById("myDropdown").classList.toggle("show");
    }

    window.onclick = function(event) {
        if (!event.target.matches('.dropbtn')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            var i;
            for (i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    }
</script>
<script>
    // on page load...
    moveProgressBar();
    // on browser resize...
    $(window).resize(function() {
        moveProgressBar();
    });

    // SIGNATURE PROGRESS
    function moveProgressBar() {
        // console.log("moveProgressBar");
        let i;
        console.log($("#progress-wrapper20").data('progress-percent'));
        for (i = 0; i < <?php echo $j; ?>; i++) {
            var getPercent = ($('#progress-wrapper' + i.toString()).data('progress-percent') / 100);
            var getProgressWrapWidth = $('#progress-wrapper' + i.toString()).width();

            // console.log(i);
            // console.log(getPercent);

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