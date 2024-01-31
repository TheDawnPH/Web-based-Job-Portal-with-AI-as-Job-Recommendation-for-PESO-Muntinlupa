<?php

use PHPMailer\PHPMailer\PHPMailer;

// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

$root = $_SERVER['DOCUMENT_ROOT'];
require $root . "/config.php";

// if user is not logged in redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /login.php");
    exit;
}

// check if user_type is not company or admin, if not redirect to 404 page
if ($_SESSION["user_type"] != "company" && $_SESSION["user_type"] != "admin") {
    header("location: /404.php");
    exit;
}

// get job id and user_id from url and clean it
$job_id = trim($_GET["job_listing_id"]);
$user_id = trim($_GET["user_id"]);

// check if job_id and user_id is empty, if yes redirect to 404 page
if (empty($job_id) || empty($user_id)) {
    $failed = "Something went wrong. Please try again later.";
    exit;
}

// set application status to 
$sql = "UPDATE job_applications SET application_status = 2 WHERE job_id = ? AND user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $job_id, $user_id);
if (mysqli_stmt_execute($stmt)) {
    // mail the user that his application is denied
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
                    $content .= "Hello, Your application for " . $row3['job_title'] . " has been declined.<br>";
                    $content .= "Don't worry, you can browse more jobs in our Job Portal again.<br><br>";
                    $content .= "Thank you for using PESO Muntinlupa Job Portal.<br>";
                    $content .= "Best Regards,<br>";
                    $content .= "PESO Muntinlupa on behalf of " . $row4['fname'] . " " . $row4['lname'] . "<br>";
                    $mail->MsgHTML($content);
                    if (!$mail->Send()) {
                        // echo $warning = "Error while sending Email.";
                        // var_dump($mail);
                    } else {
                        // echo $alert = "Please check your email for the verification link.";
                    }
                    // end of phpmailer

                    //$success = "Applicant denied successfully";
                }
            }
        }
    }
} else {
    // if failed $failed
    $failed = "Something went wrong. Please try again later.";
    exit;
}
?>
<html>

<head>
<script>
            alert("Applicant has been denied.");
            window.location.href = "/company/job_applicants.php";
        </script>
</head>

</html>