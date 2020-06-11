<?php
if (isset($_POST['input'])) {
    $dir = "users/test";
    $name = $_POST['name'];
    `mkdir $dir/$name`;

    $dir .= "/" . $name;

    $dirAmount = `ls $dir | wc -l`;
    $amount = (int) ($dirAmount);
    $fileNum = $amount + 1;
    $fileName = $fileNum . '.in';

    $input = $_POST['input'];

    `touch $dir/$fileName`;
    `echo "$input" > $dir/$fileName`;
}