<?php
require '../templates/helper.php';

$success = "";
$error = "";

if (hasValue($_GET['code']) && !hasValue($_SESSION['result'])) {
    $sth = $db->prepare("SELECT `contest_id`, `unlock_value`, `start`, `end`, `length` FROM `contests` WHERE `hash`=?");
    $sth->execute([$_GET['code']]);
    $contest = $sth->fetchAll();

    if (empty($contest)) {
        $error = "The code you entered was not found.";
    } else {
        $curr = new DateTime(getCurrDate());
        $end = new DateTime($contest[0]['end']);

        if ($curr > $end) {
            $_SESSION['result'] = $contest[0]['contest_id'];
            redirect("result");
            exit();
        } else {
            $error = "Please wait until the end of the contest to view the results.";
        }
    }
}

require '../templates/header.php';

if (!hasValue($_SESSION['result'])) {
?>
<div class="background">
    <section data-stellar-background-ratio="0.5">
        <div class="container">
            <div class="form">
                <div class="form-panel one">
                    <div class="form-header">
                        <h1>See Results</h1>
                    </div>
                    <div class="form-content">
                        <form id="contest" action="result" method="get">
                            <div class="form-group"><label for="code">Contest Code</label><input type="text" id="code" name="code" required="required" /></div>
                            <div class="form-group"><button form="contest" type="submit">Submit Code and See Results</button></div>
                        </form>
                    </div>
                    <h6 id="contestSuccess" class="text-success"></h6>
                    <h6 id="contestError" class="text-danger"></h6>
                    <script>
                        $("#contestSuccess").html("<?php echo $success; ?>");
                        $("#contestError").html("<?php echo $error; ?>");
                    </script>
                </div>
            </div>
        </div>
    </section>
</div>
<?php
} else {
    $sth = $db->prepare("SELECT `name` FROM contests WHERE `contest_id`=?");
    $sth->execute([$_SESSION['result']]);
    $contest = $sth->fetchAll();

    $sth = $db->prepare("SELECT `username`, `score` FROM `results` INNER JOIN `users` ON `users`.`user_id`=`results`.`user_id` WHERE `contest_id`=? ORDER BY `score` DESC, `timestamp` ASC LIMIT 5");
    $sth->execute([$_SESSION['result']]);
    $users = $sth->fetchAll();
?>
<link rel="stylesheet" href="css/leaderboard.css">
<section>
    <center>
        <h1><?php echo $contest[0]['name']; ?> - Results</h1>
    </center>
    <div class="table">
        <div class="table-cell">
            <ul class="leader">
                <?php
                $i = 1;
                foreach ($users as $index => $user) {
                    $add = "";
                    if ($i == 1 || $i == 2 || $i == 3) $add = "Top";
                    ?>
                    <li>
                        <span class="list<?php echo $add; ?>"><?php echo $i; ?></span>
                        <h2 class="<?php echo strtolower($add); ?>"><?php echo $user['username']; ?><span class="number<?php echo $add; ?>"><?php echo $user['score'] ?></span></h2>
                    </li>
                    <?php
                    $i++;
                }
                ?>
            </ul>
        </div>
    </div>
</section>
<?php
    unset($_SESSION['result']);
}
require '../templates/footer.php';
?>