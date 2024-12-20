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
if (!isset($_SESSION["user_type"]) || empty($_SESSION["user_type"])) {
    $_SESSION['current_page'] = $_SERVER['REQUEST_URI'];
    header("location: /login.php");
    exit;
}

// check if user_type is not company or admin, if not redirect to 404 page
if ($_SESSION["user_type"] != "company" && $_SESSION["user_type"] != "admin") {
    header("location: /404.php");
    exit;
}

// get job id and user_id from url and clean it 
$job_id = filter_input(INPUT_GET, 'job_listing_id', FILTER_SANITIZE_NUMBER_INT);
$user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);

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
                    $mail->Subject = "PESO Muntinlupa Job Portal - Your Application on " . $row3['job_title'] . " has been accepted";
                    // set content of email that the applicant is accepted
                    $content = "<b>Dear " . $row['fname'] . " " . $row['lname'] . ",</b><br><br>";
                    $content .= "Congratulations! You are <b>short-listed</b> to the job " . $row3['job_title'] . " on " . $row4['company_name'] . "<br>";
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
                    // $success = "Applicant accepted successfully";

                    // email the employer the applicant details and files
                    $website = $_ENV['WEBSITE_URL'];
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
                    $mail->AddAddress("lycoatwork@gmail.com", $row4['fname'] . " " . $row4['lname']);
                    $mail->SetFrom($_ENV['SMTP_EMAIL'], "PESO Muntinlupa");
                    $mail->Subject = "PESO Muntinlupa Job Portal - Applicant " . $row['fname'] . " " . $row['lname'] . " has been accepted";
                    // set content of email that the applicant details and attach files
                    $content = "<b>Dear " . $row4['fname'] . " " . $row4['lname'] . ",</b><br><br>";
                    $content .= "Congratulations! Applicant " . $row['fname'] . " " . $row['lname'] . " has been accepted to the job " . $row3['job_title'] . "<br>";
                    $content .= "Here are the details of the applicant:<br>";
                    $content .= "Name: " . $row['fname'] . " " . $row['lname'] . "<br>";
                    $content .= "Profile: <a href='" . $website . "/profile.php?user_id=" . $row['user_id'] . "'>View Profile</a><br>";
                    $content .= "Thank you for using PESO Muntinlupa Job Portal.<br>";
                    $biodata_path = $root . "/uploads/" . $row['user_id'] . "/" . $row['biodata_form'];
                    if (file_exists($biodata_path)) {
                        error_log("Attachment found: $biodata_path");
                        $mail->AddAttachment($biodata_path, "biodata_" . $row['lname'] . "_" . $row['fname'] . ".pdf");
                    } else {
                        error_log("Attachment missing: $biodata_path");
                    }
                    $mail->MsgHTML($content);
                    if (!$mail->Send()) {
                        error_log("Mailer Error: " . $mail->ErrorInfo);
                    } else {
                        error_log("Mail sent successfully.");
                    }

                    $mail->MsgHTML($content);
                    if (!$mail->Send()) {
                        // echo $warning = "Error while sending Email.";
                        // var_dump($mail);
                    } else {
                        // echo $alert = "Please check your email for the verification link.";
                    }
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
    <script>
        alert("Applicant has been accepted.");
        window.location.href = "/company/job_applicants.php";
    </script>
</head>

</html>