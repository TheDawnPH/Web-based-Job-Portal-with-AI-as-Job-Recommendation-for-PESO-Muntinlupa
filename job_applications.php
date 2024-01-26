<?php
use PHPMailer\PHPMailer\PHPMailer;

session_start();

require_once "config.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if (isset($_GET["job_id"])) {
    $job_id = $_GET["job_id"];
    $user_id = $_SESSION["user_id"];

    // get company user id frm jobs table
    $sql = "SELECT * FROM job_listing WHERE id = '$job_id'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $job_id_user = $row["user_id"];

    // get company user email
    $sql4 = "SELECT * FROM users WHERE user_id = '$job_id_user'";
    $result4 = mysqli_query($conn, $sql4);
    $row4 = mysqli_fetch_assoc($result4);

    $sql = "INSERT INTO job_applications (job_id, user_id) VALUES ('$job_id', '$user_id')";
    if (mysqli_query($conn, $sql)) {
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
        $mail->AddAddress($row4['email'], $row4['fname'] . " " . $row4['lname']);
        $mail->SetFrom($_ENV['SMTP_USER'], "PESO Muntinlupa - Job Application System");
        $mail->Subject = "New Job Application - PESO Muntinlupa";
        // set content of email that a job application has been sent and click the link to view the job application
        $content = "<b>Dear " . $row4['fname'] . " " . $row4['lname'] . ",</b><br><br>";
        $content .= "A new job application has been sent to you. Please click the link below to view the job application.<br><br>";
        $content .= "<a href='http://{$_ENV['WEBSITE_URL']}/company/job_applicants.php'>View Job Applications</a><br><br>";
        $content .= "This is the applicant information:<br><br>";
        $content .= "<a href='http://{$_ENV['WEBSITE_URL']}/profile.php?user_id=" . $user_id . "'>View Profile</a><br><br>";
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


?>
<html>

<head>
    <title>PESO Job Portal - Pending Job Application</title>
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
        <br>
        <h1>Your Job Applications</h1>
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
                        <th scope="col">Date Applied</th>
                        <th scope="col">Status</th>
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
                    ?>
                        <tr>
                            <td><?php echo $row2["job_title"]; ?></td>
                            <td><?php echo $row["created_at"]; ?></td>
                            <td><?php echo ($row["application_status"] === '0') ? 'Pending' : 'Approved'  ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>