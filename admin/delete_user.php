<?php

$root = $_SERVER['DOCUMENT_ROOT'];

require $root . "/config.php";

// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

// if user is not logged in redirect to login page
if (!isset($_SESSION["user_type"]) || empty($_SESSION["user_type"])) {
    $_SESSION['current_page'] = $_SERVER['REQUEST_URI'];
    header("location: /login.php");
    exit;
}

// if user is not admin show 404.php
if ($_SESSION["user_type"] != "admin") {
    header("location: /404.php");
    exit;
}

// get user_id from url and clean it
$user_id = filter_input(INPUT_GET, 'user_id', FILTER_SANITIZE_NUMBER_INT);

// delete user from users table
// get all job listings related to the user
$sql_get_jobs = "SELECT * FROM job_listing WHERE user_id = ?";
$stmt_get_jobs = mysqli_prepare($conn, $sql_get_jobs);
mysqli_stmt_bind_param($stmt_get_jobs, "i", $user_id);
if (!mysqli_stmt_execute($stmt_get_jobs)) {
    die("Error executing query: " . mysqli_error($conn));
}
$result_get_jobs = mysqli_stmt_get_result($stmt_get_jobs);

foreach ($result_get_jobs as $job) {
    // Delete job applications related to the job
    $sql_delete_applications = "DELETE FROM job_applications WHERE job_id = ?";
    $stmt_delete_applications = mysqli_prepare($conn, $sql_delete_applications);
    mysqli_stmt_bind_param($stmt_delete_applications, "i", $job['id']);
    if (!mysqli_stmt_execute($stmt_delete_applications)) {
        die("Error executing query: " . mysqli_error($conn));
    } else {
        // Delete job listing
        $sql_delete_job = "DELETE FROM job_listing WHERE id = ?";
        $stmt_delete_job = mysqli_prepare($conn, $sql_delete_job);
        mysqli_stmt_bind_param($stmt_delete_job, "i", $job['id']);
        if (!mysqli_stmt_execute($stmt_delete_job)) {
            die("Error executing query: " . mysqli_error($conn));
        }
    }
}

// then Delete user
$sql = "DELETE FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
if (!mysqli_stmt_execute($stmt)) {
    die("Error executing query: " . mysqli_error($conn));
}

?>

<html>

<head>
    <script>
        alert("User has been deleted.");
        window.location.href = "/admin/users.php";
    </script>
</head>

</html>