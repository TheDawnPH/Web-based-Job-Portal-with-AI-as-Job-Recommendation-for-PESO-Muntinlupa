<?php

use PHPMailer\PHPMailer\PHPMailer;

session_start();

$root = $_SERVER['DOCUMENT_ROOT'];
require $root . "/config.php";

// check if user_type is not company or admin, if not redirect to 404 page
if ($_SESSION["user_type"] != "company" && $_SESSION["user_type"] != "admin") {
    header("location: /404.php");
    exit;
}

// if user is not logged in redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /login.php");
    exit;
}

// get job id and user_id from url and clean it
$job_id = trim($_GET["job_listing_id"]);
$user_id = trim($_GET["user_id"]);

// check if job_id and user_id is empty, if yes redirect to 404 page
if (empty($job_id) || empty($user_id)) {
    header("location: /404.php");
    exit;
}

// set application status to 
$sql = "UPDATE job_applications SET application_status = 1 WHERE job_id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $job_id, $user_id);
if (mysqli_stmt_execute($stmt)) {
    // mail the user that his application is accepted
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);

    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_array($result);

        // get job id from job applications
        $sql2 = "SELECT job_id FROM job_applications WHERE job_id = ?";
        $stmt2 = mysqli_prepare($conn, $sql2);
        mysqli_stmt_bind_param($stmt2, "i", $job_id);
        if (mysqli_stmt_execute($stmt2)) {
            $result2 = mysqli_stmt_get_result($stmt2);
            $row2 = mysqli_fetch_array($result2);

            // get user id details from job listings
            $sql3 = "SELECT * FROM job_listing WHERE id = ?";
            $stmt3 = mysqli_prepare($conn, $sql3);
            mysqli_stmt_bind_param($stmt3, "i", $row2['job_id']);
            if (mysqli_stmt_execute($stmt3)) {
                $result3 = mysqli_stmt_get_result($stmt3);
                $row3 = mysqli_fetch_array($result3);

                // get user details from results of job listings user_id
                $sql4 = "SELECT * FROM users WHERE user_id = ?";
                $stmt4 = mysqli_prepare($conn, $sql4);
                mysqli_stmt_bind_param($stmt4, "i", $row3['user_id']);
                if (mysqli_stmt_execute($stmt4)) {
                    $result4 = mysqli_stmt_get_result($stmt4);
                    $row4 = mysqli_fetch_array($result4);

                    // use phpmailer    
                    require $root . '/PHPMailer/src/Exception.php';
                    require $root . '/PHPMailer/src/PHPMailer.php';
                    require $root . '/PHPMailer/src/SMTP.php';

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
                    $mail->AddAddress($row['email'], $row['fname'] . " " . $row['lname']);
                    $mail->SetFrom($_ENV['SMTP_EMAIL'], $row4['fname'] . " " . $row4['lname']);
                    $mail->Subject = "PESO Muntinlupa - Email Verification";
                    // set content of email that the applicant is accepted
                    $content = "<b>Dear " . $row['fname'] . " " . $row['lname'] . ",</b><br><br>";
                    $content .= "Congratulations! You are accepted to the job " . $row3['job_title'] . ".<br>";
                    $content .= "Please wait for the recruitment agent to contact you about your application.<br><br>";
                    $content .= "Thank you for using PESO Muntinlupa Job Portal.<br>";
                    $content .= "Best Regards,<br>";
                    $content .= "PESO Muntinlupa on behalf of " . $row4['fname'] . " " . $row4['lname'] . "<br><br>";
                    $mail->MsgHTML($content);
                    if (!$mail->Send()) {
                        // echo $warning = "Error while sending Email.";
                        // var_dump($mail);
                    } else {
                        // echo $alert = "Please check your email for the verification link.";
                    }
                    // end of phpmailer
                    $success = "Applicant accepted successfully";
                }
            }
        }
    }

    // close statement
    mysqli_stmt_close($stmt);
    mysqli_stmt_close($stmt2);
    mysqli_stmt_close($stmt3);
    mysqli_stmt_close($stmt4);

    // close connection
    mysqli_close($conn);
} else {
    // if failed redirect to 404 page
    header("location: /404.php");
    exit;
}
?>
<html>

<head>
    <title>PESO Job Portal - Job Applicants</title>
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
</head>

<body>
    <?php include $root . '/nav.php'; ?>
    <div class="container">
        <br>
        <div class="alert alert-success" role="alert">
            <?php echo $success; ?>
        </div>
        <a href="/company/job_applicants.php" class="btn btn-secondary">Go Back</a>
    </div>
</body>

</html>