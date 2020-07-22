<?php
function getRootDir() {
    $dir = dirname($_SERVER["PHP_SELF"]);
    return $dir;
}

function setupDb() {
    $dsn = 'mysql:host=localhost;dbname=compcs';
    $user = 'aaron';

    $handle = fopen($_SERVER['DOCUMENT_ROOT'] . '/../private/keys.csv', 'r');
    $data = fgetcsv($handle, 5, ',');
    $password = $data[0];

    try {
        $db = new PDO($dsn, $user, $password);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        return $db;
    } catch (PDOException $e) {
        echo $e;
        return false;
    }
}

function redirect($file) {
    $loc = $file . ".php";
    $host = $_SERVER["HTTP_HOST"];
    $path = rtrim(dirname($_SERVER["PHP_SELF"]), "/\\");
    header("Location: http://$host$path/$loc");
}

function hasValue($value) {
    return isset($value) && !empty($value);
}
?>