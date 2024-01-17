<?php

require_once "config.php";

session_start();

// check if user_type is admin, if not redirect to 404 page
if ($_SESSION["user_type"] != "admin") {
    header("location: 404.php");
    exit;
}

?>
<html>

</html>