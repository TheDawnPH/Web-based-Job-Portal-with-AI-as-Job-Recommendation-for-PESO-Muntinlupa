<?php
session_start();

require_once "config.php";

$code = $_GET["code"];

if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($code)) {
    $sql = "UPDATE users SET verification_status = 1 WHERE verification_code = ?";

    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $param_code);

        $param_code = $code;

        if (mysqli_stmt_execute($stmt)) {
            $message = "<p>Your Email has been verified, please setup your account <a href='edit_profile.php'>here</a>.</p>";


            // get user type from user id
            $sql = "SELECT * FROM users WHERE verification_code = '$code'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);

            // set session variables
            $_SESSION["loggedin"] = true;
            $_SESSION["user_id"] = $row["user_id"];
            $_SESSION["user_type"] = $row["user_type"];
            $_SESSION["company_verified"] = $row["company_verified"];
        } else {
            $message =  "Email verification failed. Please re-register again.";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
} else {
    $message = "Invalid verification code.";
}
?>

<html>

<head>
    <title>PESO Job Portal - Verify Email</title>
    <link rel="stylesheet" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/img/peso_muntinlupa.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</head>

<body>
    <div class="container">
        <h1>Verify Email</h1>
        <hr>
        <?php echo $message; ?>
    </div>
</body>

</html>