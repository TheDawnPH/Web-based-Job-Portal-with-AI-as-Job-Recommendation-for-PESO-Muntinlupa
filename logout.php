<?php
// typical logout session
session_start();
$_SESSION = array();
session_destroy();
header("location: index.php");
exit;

// destroy cookies
setcookie("fname", "", time() - 3600);
setcookie("lname", "", time() - 3600);
setcookie("suffix", "", time() - 3600);
setcookie("email", "", time() - 3600);
?>