<?php
require '../templates/helper.php';

$sth = $db->prepare("SELECT * FROM `posts`");
$sth->execute();
$posts = $sth->fetchAll();

require '../templates/header.php';
?>
<link rel="stylesheet" href="css/news.css">
<section data-stellar-background-ratio="0.5">
    <div class="container">
        <div class="row">
            <div class="col-md-12 col-sm-12">
                <div class="section-title wow fadeInUp" data-wow-delay="0.1s">
                    <h2>News</h2>
                </div>
                <?php
                $index = 1;
                $size = sizeof($posts);
                foreach ($posts as $index => $post) {
                    ?>
                    <div>
                        <p class="title"><b><?php echo $post['title']; ?></b></p>
                        <p><?php echo $post['timestamp']; ?></p>
                        <p class="content"><?php echo $post['content']; ?></p>
                    </div>
                    <?php if ($index != $size) { ?>
                        <hr>
                    <?php } ?>
                <?php
                    $index++;
                }
                ?>
            </div>
        </div>
    </div>
</section>
<?php
require '../templates/footer.php';
?>
