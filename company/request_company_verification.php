<?php

use PHPMailer\PHPMailer\PHPMailer;

// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

$root = $_SERVER['DOCUMENT_ROOT'];

require $root . "/config.php";

if (!isset($_SESSION["user_type"]) || empty($_SESSION["user_type"])) {
    header("location: /login.php");
    exit;
}

// if user is applicant show 404.php
if ($_SESSION["user_type"] == "applicant") {
    header("location: /404.php");
    exit;
}

// get fname and lname from user id
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);

$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

// smtp email to peso admin, use phpmailer
require $root .'/PHPMailer/src/Exception.php';
require $root .'/PHPMailer/src/PHPMailer.php';
require $root .'/PHPMailer/src/SMTP.php';

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
$mail->AddAddress($_ENV['SMTP_EMAIL'], "PESO Admin");
$mail->SetFrom($_ENV['SMTP_USER'], $row['fname'] . " " . $row['lname']);
$mail->Subject = "PESO Muntinlupa - Email Verification";
// set content of email that redirect admin to verify company
$content = "Hello! There is a request to verification of company. Please click the link below to verify the company.<br><br><a href='https://{$website}/admin/verify_company.php?user_id={$_SESSION['user_id']}'>Verify Company</a><br><br>Thank you!";
$mail->MsgHTML($content);
if (!$mail->Send()) {
    // echo $warning = "Error while sending Email.";
    // var_dump($mail);
} else {
    // echo $alert = "Please check your email for the verification link.";
}
// end of phpmailer

?>

<html>

<head>
    <title>PESO Job Portal - Request Company Verification</title>
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
        <br>
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Request Company Verification</h4>
            <p>Thank you for registering as a company. Please wait for the PESO to verify your company.</p>
            <hr>
            <p class="mb-0">You will be notified via email once your company is verified.</p><br>
            <a href="/index.php" class="btn btn-primary">Go back to home</a>
        </div>
    </div>
</body>

</html>