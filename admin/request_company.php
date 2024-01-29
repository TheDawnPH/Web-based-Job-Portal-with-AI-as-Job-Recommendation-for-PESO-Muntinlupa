<?php

use PHPMailer\PHPMailer\PHPMailer;

// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();
$root = $_SERVER['DOCUMENT_ROOT'];

require $root . "/config.php";

// check if user_type is admin, if not redirect to 404 page
if ($_SESSION["user_type"] != "admin") {
    header("location: /404.php");
    exit;
}

// if user is logged in, redirect to login
if (!isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: /login.php");
    exit;
}

// get user_id from url and clean it
$user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);

// Check if a verification action has been triggered
if (isset($_GET['verify_user']) && $_GET['verify_user'] == 1 && !empty($user_id)) {
    // Update the user's company_verified status
    $updateSql = "UPDATE users SET company_verified = 1 WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $updateSql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (!mysqli_stmt_execute($stmt)) {
        die("Error executing query: " . mysqli_error($conn));
    }

    // Retrieve the user's email address
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    if (!mysqli_stmt_execute($stmt)) {
        die("Error executing query: " . mysqli_error($conn));
    }
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    // smtp email to employer, use phpmailer
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
    $mail->SetFrom($_ENV['SMTP_USER'], "PESO Muntinlupa");
    $mail->Subject = "PESO Muntinlupa - Request to Verify Company";
    // set content of email that need to relogin the user to take effect
    $content = "<b>Dear {$row['fname']} {$row['lname']},</b><br><br>";
    $content .= "Your request to verify your company has been approved. Please logout and login to your account to take effect.<br><br>";
    $content .= "Thank you,<br>";
    $content .= "PESO Muntinlupa";
    $mail->MsgHTML($content);
    if (!$mail->Send()) {
        // echo $warning = "Error while sending Email.";
        // var_dump($mail);
    } else {
        // echo $alert = "Please check your email for the verification link.";
    }
    // end of phpmailer
}

// Retrieve data from the users table where company_verified is 0
$sql = "SELECT * FROM users WHERE user_type = 'company' AND company_verified = 0 ORDER BY lname ASC";
$result = mysqli_query($conn, $sql);


?>

<html>

<head>
    <title>Admin - Requests Portal</title>
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
        <h1>Requests Portal</h1>
        <br>
        <div class="table-responsive">
            <?php
            // Check if there are rows returned
            if (mysqli_num_rows($result) > 0) {
                echo "<table class='table table-striped table-bordered border-start'>
            <tr>
                <th>User ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Action</th>
            </tr>";

                // Loop through the results and display them in the table
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                <td>{$row['user_id']}</td>
                <td>{$row['fname']}</td>
                <td>{$row['lname']}</td>
                <td><a class='btn btn-success' role='button' href='?user_id={$row['user_id']}&verify_user=1'>Verify</a> <a class='btn btn-secondary' role='button' href='profile.php?user_id={$row['user_id']}'>View Profile</a></td>
        </tr>";
                }

                echo "</table>";
            } else {
                echo "<div class='alert alert-warning' role='alert'>No requests found.</div>";
            }

            // Close the database connection
            mysqli_close($conn);
            ?>
        </div>
    </div>
</body>

</html>