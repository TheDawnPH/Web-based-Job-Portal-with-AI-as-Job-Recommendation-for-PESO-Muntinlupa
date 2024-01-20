<?php
session_start();

require_once "config.php";

if (!isset($_SESSION["user_type"]) || empty($_SESSION["user_type"])) {
    header("location: login.php");
    exit;
}

// if user is applicant show 404.php
if ($_SESSION["user_type"] == "applicant") {
    header("location: 404.php");
    exit;
}

// get fname and lname from user id
$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

// smtp email to peso admin, use phpmailer
                require 'PHPMailer/src/Exception.php';
                require 'PHPMailer/src/PHPMailer.php';
                require 'PHPMailer/src/SMTP.php';

                $mail = new PHPMailer\PHPMailer\PHPMailer();
                $mail->IsSMTP();
                $mail->Mailer = "smtp";
                $mail->SMTPDebug  = 1;
                $mail->SMTPAuth   = TRUE;
                $mail->SMTPSecure = "tls";
                $mail->Port       = $smtp_port;
                $mail->Host       = $smtp_host;
                $mail->Username   = $stmp_username; // email address
                $mail->Password   = $smtp_password; // password
                $mail->IsHTML(true);
                $mail->AddAddress($email_admin, "PESO Admin");
                $mail->SetFrom("", $fname . " " . $lname);
                $mail->Subject = "PESO Muntinlupa - Email Verification";
                // set content of email that redirect admin to verify company
                $content = "Hello! There is a request to verification of company. Please click the link below to verify the company.<br><br><a href='http://{$website}/admin/verify_company.php?user_id={$user_id}'>Verify Company</a><br><br>Thank you!";
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
    <?php include('nav.php'); ?>
    <div class="container">
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Request Company Verification</h4>
            <p>Thank you for registering as a company. Please wait for the PESO to verify your company.</p>
            <hr>
            <p class="mb-0">You will be notified via email once your company is verified.</p>
            <a href="index.php" class="btn btn-primary">Go back to home</a>
        </div>
</body>

</html>