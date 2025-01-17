<?php
session_start();

$view = false;
$route = null;


if (isset($_SESSION['login'])) {
    $view = true;
}


include_once('src/pages/HomePage.php');