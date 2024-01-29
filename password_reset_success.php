<?php

// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require_once "config.php";

$code = $_GET["code"];

// Validate the input
if (!preg_match("/^[a-zA-Z0-9]*$/", $code)) {
    header("location: 404.php");
    exit;
}

// check if $code is same as verification_code in database, if not redirect to 404 page
$sql = "SELECT * FROM users WHERE verification_code = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $param_code);
// if code is not found in database, redirect to 404 page
$param_code = $code;
if (mysqli_stmt_execute($stmt)) {
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) == 0) {
        header("location: 404.php");
        exit();
    }
} else {
    echo "Oops! Something went wrong. Please try again later.";
}

$password = $confirm_password = "";
$password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have atleast 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);

        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    if (empty($password_err) && empty($confirm_password_err)) {
        $sql = "UPDATE users SET user_password = ? WHERE verification_code = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ss", $param_password, $param_code);

            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_code = $code;

            if (mysqli_stmt_execute($stmt)) {
                $success = "Password reset successful. You may now login.";
                // randomize verification code again
                $sql = "UPDATE users SET verification_code = ? WHERE verification_code = ?";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ss", $param_new_code, $param_code);

                    $param_new_code = bin2hex(random_bytes(16));
                    $param_code = $code;

                    if (mysqli_stmt_execute($stmt)) {
                        // do nothing
                    }
                }
            } else {
                $failed = "Something went wrong. Please try again later.";
            }
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}
?>

<html>

<head>
    <title>PESO Job Portal - Reset Password</title>
    <link rel="stylesheet" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/img/peso_muntinlupa.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</head>

<body>
    <div class="container">
        <br>
        <h1>Password Reset</h1>
        <hr>
        <?php if (!empty($success)) { ?>
            <div class="alert alert-success" role="alert">
                <?php echo $success; ?>
            </div>
        <?php } ?>
        <?php if (!empty($failed)) { ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $failed; ?>
            </div>
        <?php } ?>
        <form action="<?php echo htmlentities(htmlspecialchars($_SERVER["PHP_SELF"] . '?code=' . $code, ENT_QUOTES, 'utf-8'), ENT_QUOTES); ?>" method="post">
            <div class="mb-4">
                <label for="password" class="form-label">New Password</label>
                <input type="password" name="password" class="form-control" id="password" aria-describedby="passwordHelp">
                <span class="text-danger"><?php echo $password_err; ?></span>
            </div>
            <div class="mb-4">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" id="confirm_password" aria-describedby="confirm_passwordHelp">
                <span class="text-danger"><?php echo $confirm_password_err; ?></span>
            </div>
            <input type="submit" class="btn btn-primary" value="Reset Password">
        </form>
    </div>
</body>

</html>
