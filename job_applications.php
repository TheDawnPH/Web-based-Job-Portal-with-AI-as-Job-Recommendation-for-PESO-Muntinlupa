<?php

use PHPMailer\PHPMailer\PHPMailer;

// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (isset($_GET["job_id"])) {
    $job_id = $_GET["job_id"];
    $user_id = $_SESSION["user_id"];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT * FROM job_listing WHERE id = ?");
    $stmt->bind_param("i", $job_id);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $job_id_user = $row["user_id"];

    // Prepare and bind
    $stmt2 = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
    $stmt2->bind_param("i", $job_id_user);

    // Execute the statement
    $stmt2->execute();

    // Get the result
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_assoc();

    // check if the job application is already sent
    $sql = "SELECT * FROM job_applications WHERE job_id = '$job_id' AND user_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    if (!empty(mysqli_num_rows($result))) {
        $failed = "Job application already sent.";
    } else {
        // Prepare and bind
        $stmt3 = $conn->prepare("INSERT INTO job_applications (job_id, user_id) VALUES (?, ?)");
        $stmt3->bind_param("ii", $job_id, $user_id);

        // Execute the statement
        if ($stmt3->execute()) {
            // smtp email to company user email
            require 'PHPMailer/src/Exception.php';
            require 'PHPMailer/src/PHPMailer.php';
            require 'PHPMailer/src/SMTP.php';

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
            $mail->AddAddress($row2['email'], $row2['fname'] . " " . $row2['lname']);
            $mail->SetFrom($_ENV['SMTP_EMAIL'], "PESO Muntinlupa Job Portal");
            $mail->Subject = "New Job Application - PESO Muntinlupa Job Portal";
            // set content of email that a job application has been sent and click the link to view the job application
            $content = "<b>Dear " . $row2['fname'] . " " . $row2['lname'] . ",</b><br><br>";
            $content .= "A new job application has been sent to you. Please click the link below to view the job application.<br><br>";
            $content .= "<a href='https://{$_ENV['WEBSITE_URL']}/company/job_applicants.php'>View Job Applications</a><br><br>";
            $content .= "This is the applicant information:<br><br>";
            $content .= "<a href='https://{$_ENV['WEBSITE_URL']}/profile.php?user_id=" . $user_id . "'>View Profile</a><br><br>";
            $content .= "Thank you,<br>";
            $content .= "PESO Muntinlupa";
            $mail->MsgHTML($content);
            if (!$mail->Send()) {
                // echo $warning = "Error while sending Email.";
                // var_dump($mail);
            } else {
                $alert = "Job application sent successfully.";
            }
            // end of phpmailer
        } else {
            $failed = "Job application failed to send.";
        }
    }
}




?>
<html>

<head>
    <title>Pending Job Applications - PESO Muntinlupa Job Portal</title>
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
    <?php include "nav.php"; ?>
    <div class="container">
        <h1>Your Job Applications</h1>
        <img src="https://muntinlupacity.gov.ph/wp-content/uploads/2022/10/line_blue_yellow_red-scaled.jpg" class="img-fluid" alt="Responsive image">
        <br><br>
        <?php if (isset($sucess)) { ?>
            <div class="alert alert-success" role="alert">
                <?php echo $sucess; ?>
            </div>
        <?php } ?>
        <?php if (isset($failed)) { ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $failed; ?>
            </div>
        <?php } ?>
        <br>
        <div class="table-responsive">
            <table class="table table-striped table-bordered border-start">
                <thead>
                    <tr>
                        <th scope="col">Job Title</th>
                        <th scope="col">Company</th>
                        <th scope="col">Date Applied</th>
                        <th scope="col">Status</th>
                        <th scope="col">Date Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $user_id = $_SESSION["user_id"];
                    $sql = "SELECT * FROM job_applications WHERE user_id = '$user_id'";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $job_id = $row["job_id"];
                        $sql2 = "SELECT * FROM job_listing WHERE id = '$job_id'";
                        $result2 = mysqli_query($conn, $sql2);
                        $row2 = mysqli_fetch_assoc($result2);
                        $company_id = $row2["user_id"];
                        $sql3 = "SELECT * FROM users WHERE user_id = '$company_id'";
                        $result3 = mysqli_query($conn, $sql3);
                        $row3 = mysqli_fetch_assoc($result3);
                    ?>
                        <tr>
                            <td><?php echo $row2["job_title"]; ?></td>
                            <td><?php echo $row3["company_name"]; ?></td>
                            <td><?php echo date("h:i:s A F j, Y", strtotime($row["created_at"])); ?></td>
                            <td><?php echo ($row["application_status"] === '0') ? 'Pending' : (($row["application_status"] === '2') ? 'Rejected' : 'Approved'); ?>
                            <td><?php echo date("h:i:s A F j, Y", strtotime($row["updated_at"])); ?></td>                            
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>