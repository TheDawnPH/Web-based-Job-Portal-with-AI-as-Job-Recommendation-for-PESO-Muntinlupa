<?php

session_start();

require_once "config.php";

$code = $_GET["code"];

if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($code)) {
    $sql = "SELECT * users WHERE verification_code = ?";

    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $param_code);

        $param_code = $code;

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            // get user id
            $sql = "SELECT * FROM users WHERE verification_code = '$code'";
            $result = mysqli_query($conn, $sql);
            $row = mysqli_fetch_assoc($result);

            // get password from form
            $password = trim($_POST["password"]);
            $confirm_password = trim($_POST["confirm_password"]);

            // validate password using 6 characters only
            if (empty($password)) {
                $password_err = "Please enter a password.";
            } elseif (strlen($password) < 6) {
                $password_err = "Password must have atleast 6 characters.";
            }

            // validate confirm password
            if (empty($confirm_password)) {
                $confirm_password_err = "Please confirm password.";
            } else {
                if (empty($password_err) && ($password != $confirm_password)) {
                    $confirm_password_err = "Password did not match.";
                }
            }

            $user_id = $row["user_id"];

            // update password
            if (empty($password_err) && empty($confirm_password_err)) {
                $sql = "UPDATE users SET password = ? WHERE user_id = ?";

                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ss", $param_password, $param_user_id);

                    $param_password = password_hash($password, PASSWORD_DEFAULT);
                    $param_user_id = $user_id;

                    if (mysqli_stmt_execute($stmt)) {
                        $sucess = "Your password has been reset successfully. <a href='login.php'>Please login here</a>.</p>";
                        // randomize verification code again
                        $sql = "UPDATE users SET verification_code = ? WHERE user_id = ?";
                        if ($stmt = mysqli_prepare($conn, $sql)) {
                            mysqli_stmt_bind_param($stmt, "ss", $param_verification_code, $param_user_id);

                            $param_verification_code = md5($email . time());
                            $param_user_id = $user_id;

                            if (mysqli_stmt_execute($stmt)) {
                                mysqli_stmt_close($stmt);
                            }
                        } else {
                            $failed = "Oops! Something went wrong. Please try again later.";
                        }
                        mysqli_stmt_close($stmt);
                    }
                }
            } else {
                $failed = "Oops! Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_close($conn);
    } else {
        $failed = "Invalid verification code.";
    }
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
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</head>

<body>
    <div class="container">
        <h1>Password Reset</h1>
        <hr>
        <?php if (!empty($sucess)) { ?>
            <div class="alert alert-success" role="alert">
                <?php echo $sucess; ?>
            </div>
        <?php } ?>
        <?php if (!empty($failed)) { ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $failed; ?>
            </div>
        <?php } ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . '?code=' . $code, ENT_QUOTES, 'utf-8'); ?>" method="post">
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