<?php
// typical logout session
// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header("location: index.php");
exit;

// destroy cookies
setcookie("fname", "", time() - 3600, "/", "", isset($_SERVER["HTTPS"]), true);
setcookie("lname", "", time() - 3600, "/", "", isset($_SERVER["HTTPS"]), true);
setcookie("email", "", time() - 3600, "/", "", isset($_SERVER["HTTPS"]), true);
setcookie("jinindustry_id", "", time() - 3600, "/", "", isset($_SERVER["HTTPS"]), true);
?>