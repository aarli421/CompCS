<?php
if (isset($_SESSION['user'])) {
    echo "Logged in";
} else {
    echo "Not logged in";
}