<?php

session_start();

require_once "config.php";

// get job id from url
$job_id = $_GET['job_id'];

// get job details from jobs listings table
$sql = "SELECT * FROM job_listings WHERE job_id = '$job_id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
?>

<html>
<head>
    <title>PESO Job Portal - Job Details</title>
    <link rel="stylesheet" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/img/peso_muntinlupa.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</head>
<body>
    <?php include "nav.php"; ?>
    <div class="container">
        <br>
        <h1><?php echo $row['job_title']; ?></h1><br>
        <h5>Job Description</h5>
        <p><?php echo $row['job_description']; ?></p>
        <h5>Job Requirements</h5>
        <p><?php echo $row['job_requirements']; ?></p>
        <h5>Job Salary</h5>
        <p><?php echo $row['job_salary']; ?></p>
        <h5>Job Type</h5>
        <p><?php echo $row['job_type']; ?></p>
        <h5>Is SHS Qualified</h5>
        <p><?php 
        if ($row['shs_qualified'] == 1) {
            echo "Yes";
        } else {
            echo "No";
        }
        ?></p>
        <h5>Job Industry</h5>
        <p><?php
        $sql = "SELECT * FROM jinindustry WHERE jinindustry_id = '" . $row['jinindustry_id'] . "'";
        $result = mysqli_query($conn, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            echo $row['jinindustry_name'];
        }
        ?></p>
        // show image if there is one
        <?php
        if ($row['job_image'] != "/company/uploads/" . $row['user_id']) {
            echo "<img src='img/job_images/$row[job_image]' width='100%'>";
        }
        ?>
        <br><br>
        // if user logged in and is an applicant, show apply button, else show login button
        <?php
        if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == "applicant") {
            echo "<a href='job_applications.php?job_id=$job_id' class='btn btn-success'>Apply in this Job</a>";
        } else {
            echo "<a href='login.php' class='btn btn-success'>Login to Apply</a>";
        }
        ?>
    </div>
</body>
</html>