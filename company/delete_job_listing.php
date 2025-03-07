<?php

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

// get job id from url and clean it
$job_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

// if job id is not set redirect to 404 page
if (empty($job_id)) {
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
        // echo "Something went wrong when deleting job applications. Please try again later.";
        exit;
    }
}

// then delete job listing from database
$sql = "DELETE FROM job_listing WHERE id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $job_id);
    if (mysqli_stmt_execute($stmt)) {
       // $success = "Job listing deleted successfully.";
    } else {
        // echo "Something went wrong when deleting job listing. Please try again later.";
    }
}
?>
<html>

<head>
<script>
            alert("Job Listing has been deleted successfully.");
            window.location.href = "/company/job_listings.php";
        </script>
</head>

</html>