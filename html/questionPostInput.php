<?php
if (isset($_POST['input'])) {
    $dir = "users/test";
    $dirAmount = `ls $dir | wc -l`;
    $amount = ((int) ($dirAmount - 1)) / 2;
    $fileNum = $amount + 1;
    $fileName = $fileNum . '.in';

    $input = $_POST['input'];

    `touch $dir/$fileName`;
    `echo "$input" > $dir/$fileName`;
}