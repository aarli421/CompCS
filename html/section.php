<?php
require '../templates/helper.php';
require '../templates/header.php';

if (!isset($_SESSION['user'])) {
    redirect("login");
    exit();
}

if (!isset($_GET['id'])) {
    redirect("home");
    exit();
}

$sth = $db->prepare("SELECT * FROM `sections` WHERE `section_id`=?");
$sth->execute([$_GET['id']]);
$section = $sth->fetchAll();

if (empty($section)) {
    redirect("404");
    exit();
}

$sth = $db->prepare("SELECT `points`, `admin` FROM `users` WHERE `username`=?");
$sth->execute([$_SESSION['user']]);
$user = $sth->fetchAll();
$points = $user[0]['points'];
?>
<link rel="stylesheet" href="css/progress.css">
<link rel="stylesheet" href="css/home.css">
<link rel="stylesheet" href="css/section.css">
<section>
    <div class="container">
        <h1><?php echo $section[0]['name']; ?></h1>
    </div>
    <div class="container"><?php echo $section[0]['description']; ?></div>
    <div class="container">
        <h1>Practice Problems</h1>
    </div>
    <div class="container">
        <ol class="questions">
            <?php
            $sth = $db->prepare("SELECT * FROM `questions` WHERE `section_id`=? ORDER BY `unlock_value`");
            $sth->execute([$_GET['id']]);
            $passArr = $sth->fetchAll();

            $j = 0;
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
                            <div id="progress-wrapper<?php if (!$locked) echo $j; ?>" class="progress-wrap progress" data-progress-percent="<?php echo round(($max[0][0] / $value['testcases']) * 100,2); ?>">
                                <div id="progress-bar<?php if (!$locked) echo $j; ?>" class="progress-bar progress"></div>
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
                if (!$locked) {
                    $j++;
                }
            }?>
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
