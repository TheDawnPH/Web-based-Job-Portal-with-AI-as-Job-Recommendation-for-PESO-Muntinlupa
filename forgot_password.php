<?php

use PHPMailer\PHPMailer\PHPMailer;

// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: index.php");
    exit;
}

require "config.php";

$email = "";
$email_err = "";

// process reset password request by email the user a link to reset password, use verification code to reset password
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Verify reCAPTCHA response
    if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
        $verification_err = "Please complete the reCAPTCHA challenge.";
    } else {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify?secret=' . $_ENV['SECRET_KEY'] . '&response=' . $_POST['g-recaptcha-response']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $response = json_decode($response);
        if (!$response->success) {
            $verification_err = "reCAPTCHA verification failed. Please try again.";
        }
    }

    if (empty($email_err)) {
        $sql = "SELECT * FROM users WHERE email = ?";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_email);

            $param_email = $email;

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $sql = "UPDATE users SET verification_code = ? WHERE email = ?";

                    if ($stmt = mysqli_prepare($conn, $sql)) {
                        mysqli_stmt_bind_param($stmt, "ss", $param_verification_code, $param_email);

                        $param_verification_code = bin2hex(random_bytes(32));
                        $param_email = $email;

                        if (mysqli_stmt_execute($stmt)) {
                            // use phpmailer
                            require 'PHPMailer/src/Exception.php';
                            require 'PHPMailer/src/PHPMailer.php';
                            require 'PHPMailer/src/SMTP.php';
                            loadEnv();

                            $mail = new PHPMailer();
                            $mail->IsSMTP();
                            $mail->Mailer = "smtp";
                            $mail->SMTPDebug  = 0;
                            $mail->SMTPAuth   = TRUE;
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port       = $_ENV['SMTP_PORT'];
                            $mail->Host       = $_ENV['SMTP_HOST'];
                            $mail->Username   = $_ENV['SMTP_USER']; // email address
                            $mail->Password   = $_ENV['SMTP_PASS']; // password
                            $mail->IsHTML(true);
                            $mail->AddAddress($email, $fname . " " . $lname);
                            $mail->SetFrom($_ENV['SMTP_EMAIL'], "PESO Muntinlupa");
                            $mail->Subject = "PESO Muntinlupa - Reset Password";
                            $content = "<b>Hi " . $fname . " " . $lname . ",</b><br><br>";
                            $content .= "Please click the link below to reset your password.<br><br>";
                            $content .= "<a href='http://" . $website . "/password_reset_success.php?code=$param_verification_code'>Verify Email</a><br><br>";
                            $content .= "Thank you!<br>";
                            $content .= "PESO Muntinlupa";
                            $mail->MsgHTML($content);
                            if (!$mail->Send()) {
                                $warning = "Error while sending Email.";
                                // var_dump($mail);
                            } else {
                                $alert = "Please check your email for the verification link.";
                            }
                            // end of phpmailer
                            $alert = "Please check your email for the verification link.";
                        } else {
                            echo "Oops! Something went wrong. Please try again later.";
                        }
                    }
                } else {
                    $email_err = "No account found with that email.";
                }
            } else {
                $warning = "Oops! Something went wrong. Please try again later.";
            }
        }
        mysqli_stmt_close($stmt);
    }
}
?>


<html>

<head>
    <title>PESO Job Portal - Forgot Password</title>
    <link rel="stylesheet" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/img/peso_muntinlupa.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <script src='https://www.google.com/recaptcha/api.js' async defer></script>

</head>

<body>
    <div class="container">
        <a href="login.php" class="btn btn-secondary">Back to Login</a><br><br>
        <div class="row">
            <div class="col-md">
                <img src="img/peso_muntinlupa.png" alt="PESO Logo" class="img-fluid">
            </div>
            <div class="col-md">
                <br>
                <h1>Forgot Password</h1>
                <?php
                if (!empty($alert)) {
                    echo '<div class="alert alert-success">' . $alert . '</div>';
                }
                if (!empty($warning)) {
                    echo '<div class="alert alert-danger">' . $warning . '</div>';
                }
                if (!empty($email_err)) {
                    echo '<div class="alert alert-danger">' . $email_err . '</div>';
                }
                ?>
                <form action="<?php echo htmlentities(htmlspecialchars($_SERVER["PHP_SELF"]), ENT_QUOTES); ?>" method="post">
                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="text" name="email" class="form-control" id="email" aria-describedby="emailHelp" s>
                        <div id="emailHelp" class="form-text">Please insert your registered email here</div>
                    </div>
                    <div class="g-recaptcha" data-sitekey="<?php echo $_ENV['SITE_KEY']; ?>"></div>
                    <br>
                    <button type="submit" class="btn btn-success">Recover Password</button>
                </form>
            </div>
        </div>

    </div>
</body>

</html>