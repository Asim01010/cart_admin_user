<?php
// config.php - include on every page
session_start();




// update these DB settings to match your environment
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'shop_demo';

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);
if (!$conn) {
    die("DB connect error: " . mysqli_connect_error());
}

// helper: sanitize input (simple)
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
};