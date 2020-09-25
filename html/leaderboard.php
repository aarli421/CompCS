<?php
require '../templates/helper.php';
require '../templates/header.php';

$sth = $db->prepare("SELECT `username`, `points` FROM users WHERE `admin`=0 AND `active`=1 ORDER BY `points`-`start` DESC LIMIT 5");
$sth->execute();
$users = $sth->fetchAll();
?>
<link rel="stylesheet" href="css/leaderboard.css">
<section>
    <center>
        <h1>Leaderboard</h1>
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
                    <h2 class="<?php echo strtolower($add); ?>"><?php echo $user['username']; ?><span class="number<?php echo $add; ?>"><?php echo $user['points'] ?></span></h2>
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
require '../templates/footer.php';
