<?php
// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
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

// show all job listing that is created by the session user_id
$sql = "SELECT * FROM job_listing WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);


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
        <!--- php code where show the title of job listing created by user_id and show the table with applicants name and action to accept or deny in the Job -->
        <h1>Job Applicants</h1>
        <br><br>
        <div class="table-responsive">
            <table class="table table-striped table-bordered border-start">
                <thead>
                    <tr>
                        <th scope="col">Job Title</th>
                        <th scope="col">Applicant Name</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sql = "SELECT * FROM job_applications WHERE job_id = ? && application_status = '0'";
                        $stmt = mysqli_prepare($conn, $sql);
                        mysqli_stmt_bind_param($stmt, "i", $row["id"]);
                        mysqli_stmt_execute($stmt);
                        $result2 = mysqli_stmt_get_result($stmt);
                        while ($row2 = mysqli_fetch_assoc($result2)) {
                            $sql = "SELECT * FROM users WHERE user_id = ?";
                            $stmt = mysqli_prepare($conn, $sql);
                            mysqli_stmt_bind_param($stmt, "i", $row2["user_id"]);
                            mysqli_stmt_execute($stmt);
                            $result3 = mysqli_stmt_get_result($stmt);
                            $row3 = mysqli_fetch_assoc($result3);
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row["job_title"]) . "</td>";
                            // make name a link
                            echo "<td><a target='_blank' href='/profile.php?user_id=" . htmlspecialchars($row3["user_id"]) . "'>" . htmlspecialchars($row3["fname"]) . " " . htmlspecialchars($row3["lname"]) . "</a></td>";
                            echo "<td><a href='accept_applicant.php?job_listing_id=" . htmlspecialchars($row["id"]) . "&user_id=" . htmlspecialchars($row3["user_id"]) . "' class='btn btn-success'>Accept</a> <a href='deny_applicant.php?job_listing_id=" . htmlspecialchars($row["id"]) . "&user_id=" . htmlspecialchars($row3["user_id"]) . "' class='btn btn-danger'>Deny</a></td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>