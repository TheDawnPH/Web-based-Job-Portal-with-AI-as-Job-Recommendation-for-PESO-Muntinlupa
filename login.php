<?php
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

require_once "config.php";

$email = $password = "";
$email_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter password.";
    } else {
        $password = trim($_POST["password"]);
    }


    if (empty($email_err) && empty($password_err)) {
        $sql = "SELECT user_id, user_type, fname, mname, lname, suffix, email, user_password, verification_status, company_verified FROM users WHERE email = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);

            $param_email = $email;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    mysqli_stmt_bind_result($stmt, $user_id, $user_type, $fname, $mname, $lname, $suffix, $email, $hashed_password, $verification_status, $company_verified);

                    if (mysqli_stmt_fetch($stmt)) {
                        if (password_verify($password, $hashed_password)) {
                            if ($verification_status == 1) {
                                session_start();

                                // set session variables
                                $_SESSION["loggedin"] = true;
                                $_SESSION["user_id"] = $user_id;
                                $_SESSION["user_type"] = $user_type;
                                $_SESSION["company_verified"] = $company_verified;
                                // use cookies on fname, lname, suffix, email
                                $_COOKIE["fname"] = $fname;
                                $_COOKIE["lname"] = $lname;
                                $_COOKIE["suffix"] = $suffix;
                                $_COOKIE["email"] = $email;

                                header("location: index.php");
                            } else {
                                $verification_err = "Your account is not yet verified. Please check your email for the verification link.";
                            }
                        } else {
                            $password_err = "The password you entered is incorrect.";
                        }
                    }
                } else {
                    $email_err = "No account found with that email.";
                }
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }

        mysqli_stmt_close($stmt);
    }

    mysqli_close($conn);
}

?>

<html>

<head>
    <title>PESO Job Portal - Login</title>
    <link rel="stylesheet" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</head>

<body>
    <div class="container">
        <a href="index.php" class="btn btn-secondary">Back to Homepage</a><br><br>
        <div class="row">
            <div class="col-md">
                <img src="img/peso_muntinlupa.png" alt="PESO Logo" class="img-fluid">
            </div>
            <div class="col-md">
                <br>
                <h1>Login</h1>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <?php
                    if (!empty($verification_err)) {
                        echo '<div class="alert alert-danger">' . $verification_err . '</div>';
                    }
                    ?>
                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" name="email"
                            class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" id="email"
                            aria-describedby="emailHelp" value="<?php echo $email; ?>">
                        <div class="invalid-feedback"><?php echo $email_err; ?></div>
                    </div>
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="text" name="password"
                            class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>"
                            id="password" aria-describedby="passwordHelp" value="<?php echo $password; ?>">
                        <div class="invalid-feedback"><?php echo $password_err; ?></div>
                    </div>
                    <div class="mb-4 text-end"><a href="forgot_password.php">Forgot Password?</a></div>
                    <button type="submit" class="btn btn-primary">Login</button>
                    <a href="register.php" class="btn btn-secondary">Register</a>
                </form>
            </div>
        </div>

    </div>
</body>

</html>