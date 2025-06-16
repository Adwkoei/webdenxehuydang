<?php
require('system/dbconfig.php');
if ($level != "0") {
    setcookie('auth_token', '', time() - 3600, '/', '', isset($_SERVER['HTTPS']), true);
}
header("Location: ".((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://").$_SERVER['HTTP_HOST']);
exit();
?>
