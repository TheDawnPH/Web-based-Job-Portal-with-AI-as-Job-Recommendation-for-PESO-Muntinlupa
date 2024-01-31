<?php
use PHPMailer\PHPMailer\PHPMailer;

$root = $_SERVER['DOCUMENT_ROOT'];

require $root . "/config.php";

// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
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

$alert = $success = "";

$user_type = $fname = $mname = $lname = $suffix = $email  = "";
$user_type_err = $fname_err = $mname_err = $lname_err = $suffix_err = $email_err = "";

function isEmailExists($conn, $email)
{
    $sql = "SELECT user_id FROM users WHERE email = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $param_email);
        $param_email = $email;
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        $result = mysqli_stmt_num_rows($stmt);
        mysqli_stmt_close($stmt);
        return $result > 0;
    }
    return false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["user_type"]))) {
        $user_type_err = "Please select registration type.";
    } else {
        $user_type = trim($_POST["user_type"]);
    }

    if (empty(trim($_POST["fname"]))) {
        $fname_err = "Please enter first name.";
    } else {
        $fname = trim($_POST["fname"]);
    }

    $mname = trim($_POST["mname"]);

    if (empty(trim($_POST["lname"]))) {
        $lname_err = "Please enter last name.";
    } else {
        $lname = trim($_POST["lname"]);
    }

    $suffix = trim($_POST["suffix"]);

    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } else {
        $email = trim($_POST["email"]);
    }

    if (empty($email_err) && isEmailExists($conn, $email)) {
        $email_err = "Email already exists.";
    }

    if (empty($user_type_err) && empty($fname_err) && empty($mname_err) && empty($lname_err) && empty($suffix_err) && empty($email_err)) {
        $sql = "INSERT INTO users (user_type, fname, mname, lname, suffix, email, verification_code, company_verified, verification_status, user_password) VALUES (?,?,?,?,?,?,?,?,?,?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssssssssss", $param_user_type, $param_fname, $param_mname, $param_lname, $param_suffix, $param_email, $param_verification_code, $param_company_verified, $param_verification_status, $param_password);

            $param_user_type = $user_type;
            $param_fname = $fname;
            $param_mname = $mname;
            $param_lname = $lname;
            $param_suffix = $suffix;
            $param_email = $email;
            $param_verification_status = 1;
            $param_password = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            $param_verification_code = bin2hex(random_bytes(16));
            $param_company_verified = ($user_type == "company") ? 0 : 1;

            if (mysqli_stmt_execute($stmt)) {
                // phpmailer
                require $root . '/PHPMailer/src/Exception.php';
                require $root . '/PHPMailer/src/PHPMailer.php';
                require $root . '/PHPMailer/src/SMTP.php';
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
                $mail->SetFrom($_ENV['SMTP_USER'], "PESO Muntinlupa");
                $mail->Subject = "PESO Muntinlupa Admin - Provision of Account";
                $content = "<b>Hi " . $fname . " " . $lname . ",</b><br><br>";
                $content .= "An PESO Admin has been provided you an account,<br>Please click the link below to set your password.<br><br>";
                $content .= "<a href='https://".$_ENV['WEBSITE_URL']."/password_reset_success.php?code=$param_verification_code'>Set Password</a><br><br>";
                $content .= "Thank you!<br>";
                $content .= "PESO Muntinlupa";
                $mail->MsgHTML($content);

                if (!$mail->Send()) {
                    $alert = "Error while sending Email.";
                } else {
                    $success = "Please check your email for the verification link.";
                }
                // end of phpmailer

            } else {
                $alert = "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    mysqli_close($conn);
}

?>

<html>

<head>
    <title>Admin - Users</title>
    <link rel="stylesheet" href="/css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/img/peso_muntinlupa.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</head>

<body>
    <?php include $root . '/nav.php'; ?>
    <div class="container">
        <h1>Add Users</h1>
        <form action="<?php echo htmlentities(htmlspecialchars($_SERVER["PHP_SELF"]), ENT_QUOTES); ?>" method="post">
            <?php
            if (!empty($alert)) {
                echo '<div class="alert alert-danger" role="alert">' . $alert . '</div>';
            } elseif (!empty($success)) {
                echo '<div class="alert alert-success" role="alert">' . $success . '</div>';
            }
            ?>

            <div class="mb-4">
                <label for="user_type" class="form-label">Registration Type</label>
                <select class="form-select" name="user_type" id="user_type">
                    <option value="applicant">As Applicant</option>
                    <option value="company">As Company</option>
                    <option value="admin">As Admin</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="fname" class="form-label">First Name</label>
                <input type="text" name="fname" class="form-control" id="fname" aria-describedby="fnameHelp">
            </div>
            <div class="mb-4">
                <label for="mname" class="form-label">Middle Name</label>
                <input type="text" name="mname" class="form-control" id="mname" aria-describedby="mnameHelp">
            </div>
            <div class="mb-4">
                <label for="lname" class="form-label">Last Name</label>
                <input type="text" name="lname" class="form-control" id="lname" aria-describedby="lnameHelp">
            </div>
            <div class="mb-4">
                <label for="suffix" class="form-label">Suffix</label>
                <input type="text" name="suffix" class="form-control" id="suffix" aria-describedby="suffixHelp">
            </div>
            <div class="mb-4">
                <label for="email" class="form-label">Email</label>
                <input type="text" name="email" class="form-control" id="email" aria-describedby="emailHelp">
            </div>
            <div class="mb-4">
                <div class="form-text">Their Account Verification will be sent to their email, once clicked it will prompt them to set their password.</div>
            </div>
            <button type="submit" class="btn btn-primary">Add User</button>
        </form>
    </div>
</body>

</html>
