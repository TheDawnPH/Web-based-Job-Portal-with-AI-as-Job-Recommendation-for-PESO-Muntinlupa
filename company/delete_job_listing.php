<?php
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
$job_id = trim($_GET["id"]);

// if job id is not set redirect to 404 page
if (!isset($job_id) || empty($job_id)) {
    header("location: /404.php");
    exit;
}

// if user is not the owner of the job redirect to 404 page
if ($_SESSION["user_type"] == "company") {
    $user_id = $_SESSION["id"];
    $sql = "SELECT * FROM job_listings WHERE id = ? AND user_id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ii", $job_id, $user_id);
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 0) {
                header("location: /404.php");
                exit;
            }
        }
    }
}

// delete associated job applications first
$sql = "DELETE FROM job_applications WHERE job_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $job_id);
    if (!mysqli_stmt_execute($stmt)) {
        echo "Something went wrong when deleting job applications. Please try again later.";
        exit;
    }
}

// then delete job listing from database
$sql = "DELETE FROM job_listing WHERE id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $job_id);
    if (mysqli_stmt_execute($stmt)) {
        $success = "Job listing deleted successfully.";
    } else {
        echo "Something went wrong when deleting job listing. Please try again later.";
    }
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
        <a href="/company/job_listings.php" class="btn btn-secondary">Go Back</a>
    </div>
</body>

</html>