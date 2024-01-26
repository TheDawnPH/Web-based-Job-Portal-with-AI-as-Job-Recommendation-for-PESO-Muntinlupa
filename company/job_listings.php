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

if(isset($_GET["delete"]) && $_GET["delete"] == "success") {
    $deletebox = "1";
} else {
    $deletebox = "0";
}

// get all job listings of the session user
$sql = "SELECT * FROM job_listing WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION["user_id"]);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>
<html>

<head>
    <title>PESO Job Portal - Job Listings</title>
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
    <?php include $root . "/nav.php"; ?>
    <br>
    <div class="container">
        <h1>Job Listings</h1>
        <?php if ($deletebox === "1") { ?>
            <div class="alert alert-success" role="alert">
                Job listing deleted successfully!
            </div>
        <?php } ?>
        <a href="/add_job_listings.php" class="btn btn-primary">Add Job Listing</a><br>
        <br>
        <div class="table-responsive">
            <!-- display all job listing from session user_id and add actions such as view, edit and delete -->
            <table class="table table-striped table-bordered border-start">
                <thead>
                    <tr>
                        <th scope="col">Job Title</th>
                        <th scope="col">Job Category</th>
                        <th scope="col">Job Type</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($row = mysqli_fetch_array($result)) {
                        // get jinindustry_name from jinindustry_id on job_listing table
                        $sql2 = "SELECT jinindustry_name FROM jinindustry WHERE jinindustry_id = ?";
                        $stmt2 = mysqli_prepare($conn, $sql2);
                        mysqli_stmt_bind_param($stmt2, "i", $row["jinindustry_id"]);
                        mysqli_stmt_execute($stmt2);
                        $result2 = mysqli_stmt_get_result($stmt2);
                        $row2 = mysqli_fetch_array($result2);

                        echo "<tr>";
                        echo "<td>" . $row["job_title"] . "</td>";
                        echo "<td>" . $row2["jinindustry_name"] . "</td>";
                        echo "<td>" . $row["job_type"] . "</td>";
                        echo "<td><a class='btn btn-primary' href='/job_details.php?job_id=" . $row["id"] . "'>View</a> <a class='btn btn-warning' href='add_job_applications.php?id=" . $row["id"] . "'>Edit</a> <a class='btn btn-danger' href='delete_job_listing.php?id=" . $row["id"] . "'>Delete</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
        </div>
    </div>
</body>