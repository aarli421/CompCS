<?php
require '../templates/helper.php';
require '../templates/header.php';

$sth = $db->prepare("SELECT * FROM users");
$sth->execute();
$users = $sth->fetchAll();

$sth = $db->prepare("SELECT * FROM questions");
$sth->execute();
$questions = $sth->fetchAll();

foreach ($users as $user_index => $user) {
    foreach ($questions as $question_index => $question) {
        for ($contest = 0; $contest <= 3; $contest++) {
            $sth = $db->prepare("SELECT MAX(correct_cases), `grade_id` FROM grades WHERE user_id=? AND question_id=? AND `contest_id`=? GROUP BY `correct_cases`, `grade_id` ORDER BY `timestamp` DESC LIMIT 1");
            $sth->execute([$user['user_id'], $question['question_id'], $contest]);
            $max = $sth->fetchAll();

            if (!empty($max)) {
                $sth = $db->prepare("DELETE FROM grades WHERE `user_id`=? AND `question_id`=? AND NOT `grade_id`=?");
                $sth->execute([$user['user_id'], $question['question_id'], $max[0]['grade_id']]);
            }
        }
    }
}

require '../templates/footer.php';
