<?php
$root = $_SERVER['DOCUMENT_ROOT'];

require $root . "/config.php";

session_start();

// if user is not logged in redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /login.php");
    exit;
}

// if user is not admin show 404.php
if ($_SESSION["user_type"] != "admin") {
    header("location: /404.php");
    exit;
}

$user_id = $_GET["user_id"];

// delete user from users table
$sql = "DELETE FROM users WHERE user_id = '$user_id'";
mysqli_query($conn, $sql);
?>
<html>
    <head>
        <script>
            alert("User has been deleted.");
            window.location.href = "/admin/users.php";
        </script>
    </head>
</html>