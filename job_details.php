<?php
session_start();
require_once "config.php";

// Get job id from URL
$job_id = $_GET['job_id'];

// Get job details from jobs listings table
$sql = "SELECT * FROM job_listing WHERE id = '$job_id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

// Get fname and lname from users table
$sql2 = "SELECT * FROM users WHERE user_id = '$row[user_id]'";
$result2 = mysqli_query($conn, $sql2);
$row2 = mysqli_fetch_assoc($result2);
$name = $row2['fname'] . " " . $row2['lname'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PESO Job Portal - Job Details</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/img/peso_muntinlupa.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</head>

<body>
    <?php include "nav.php"; ?>
    <div class="container mt-4">
        <div class="row row-cols-1 row-cols-md-3 g-2">
            <div class="col-md-6">
                <div class="card h-100">
                    <?php if (!empty($row['image_name'])) : ?>
                        <img src='/company/uploads/<?php echo $row['id']; ?>/<?php echo $row['image_name']; ?>' class="card-img-bottom" alt='Company Image'>
                    <?php endif; ?>
                    <div class="card-body">
                        <h1 class="card-title"><?php echo $row['job_title']; ?></h1>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-subtitle mb-2 text-muted">Job Description</h5>
                        <p class="card-text"><?php echo $row['job_description']; ?></p>
                        <h5 class="card-subtitle mb-2 text-muted">Job Requirements</h5>
                        <p class="card-text"><?php echo $row['job_requirements']; ?></p>
                        <h5 class="card-subtitle mb-2 text-muted">Job Salary</h5>
                        <p class="card-text"><?php echo $row['job_salary']; ?></p>
                        <h5 class="card-subtitle mb-2 text-muted">Job Type</h5>
                        <p class="card-text"><?php echo $row['job_type']; ?></p>
                        <h5 class="card-subtitle mb-2 text-muted">Is SHS Qualified</h5>
                        <p class="card-text"><?php echo ($row['shs_qualified'] == 1) ? "Yes" : "No"; ?></p>
                        <h5 class="card-subtitle mb-2 text-muted">Job Industry</h5>
                        <p class="card-text"><?php
                                                $industrySql = "SELECT * FROM jinindustry WHERE jinindustry_id = '" . $row['jinindustry_id'] . "'";
                                                $industryResult = mysqli_query($conn, $industrySql);
                                                $industryRow = mysqli_fetch_assoc($industryResult);
                                                echo $industryRow['jinindustry_name'];
                                                ?></p>
                        <h5 class="card-subtitle mb-2 text-muted">Posted by</h5>
                        <p class="card-text"><?php echo $name; ?></p>
                    </div>
                    <div class="card-footer text-muted">
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == "applicant") : ?>
                            <a href='job_applications.php?job_id=<?php echo $job_id; ?>' class='btn btn-success'>Apply in this Job</a>
                        <?php else : ?>
                            <a href='login.php' class='btn btn-success'>Login to Apply</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>


    </div>
</body>

</html>