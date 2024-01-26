<?php
// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

http_response_code(404);
?>

<html>

<head>
<script>
            alert("404 Not Found - The page that you have requested could not be found.");
            window.location.href = "/index.php";
        </script>
</head>

</html>