<?php
session_start();

include 'config.php';

// function for getting job acceptance rate
function getJobAcceptanceRate($job_id)
{
    global $conn;

    $sql = "SELECT * FROM job_applications WHERE job_id = " . $job_id;
    $result = mysqli_query($conn, $sql);
    $job_applications = [];
    $job_applications_count = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        $job_applications[] = $row;
        $job_applications_count++;
    }

    $job_accepted_count = 0;

    foreach ($job_applications as $job_application) {
        if ($job_application['application_status'] == '1') {
            $job_accepted_count++;
        }
    }

    if ($job_applications_count == 0) {
        return 0;
    } else {
        return round(($job_accepted_count / $job_applications_count) * 100);
    }
}

?>

<html>

<head>
    <title>PESO Muntinlupa - Job Portal</title>
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
    <style>
        .cover {
            /* use 1350x300px image */
            background-image: url('img/banner.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            height: 300px;

        }

        @media (max-width: 1200px) {
            .cover {
                display: none;
                /* Hide the cover image on devices with resolution 1200px and below */
            }
        }
    </style>
    <style>
        @media screen and (max-width: 500px) {
            .desktop {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="col-md-12">
        <div class="cover"></div>
    </div>
    <div class="container">
        <?php if ($cverify["company_verified"] == 0 && isset($_SESSION["user_type"])) { ?>
            <div class="alert alert-danger fade show" role="alert">
                <strong>Warning!</strong><br>Your company is not yet verified. Please <a href="company/request_company_verification.php">click here</a> to verify your company.
            </div>
        <?php } ?>
        <div class="row row-cols-1 row-cols-md-2 g-3">
            <?php
            if (isset($_SESSION["user_type"]) && $_SESSION["user_type"] === 'applicant') {
                // Get the user's preferred industry
                $user_skill_sql = "SELECT * FROM users WHERE user_id =" . $_SESSION['user_id'];
                $user_skill_result = mysqli_query($conn, $user_skill_sql);
                $row = mysqli_fetch_assoc($user_skill_result);
                $user_preferred_industry = $row['jinindustry_id'];

                // Retrieve all job_listings
                $job_sql = "SELECT id, job_title, job_description, jinindustry_id FROM job_listing";
                $job_result = mysqli_query($conn, $job_sql);
                $jobs = [];

                while ($row = mysqli_fetch_assoc($job_result)) {
                    $jobs[] = $row;
                }

                // retrieve all job applications
                $job_applications_sql = "SELECT * FROM job_applications";
                $job_applications_result = mysqli_query($conn, $job_applications_sql);
                $job_applications_count = mysqli_num_rows($job_applications_result);
                $job_applications = [];

                while ($row = mysqli_fetch_assoc($job_applications_result)) {
                    $job_applications[] = $row;
                }

                $recommended_jobs = [];

                // implement job recommendation algorithm based on accepted job applications
                foreach ($jobs as $job) {
                    $job_industry = $job['jinindustry_id'];

                    foreach ($job_applications as $job_application) {
                        if ($job['id'] == $job_application['job_id']) {
                            if (getJobAcceptanceRate($job['id']) >= 60) {
                                // Check if the user and job have the same industry
                                if ($user_preferred_industry == $job_industry) {
                                    $recommended_jobs[] = array(
                                        'job_id' => $job['id'],
                                        'job_title' => $job['job_title']
                                    );
                                }
                            } else {
                                // Check if the user and job have the same industry
                                if ($user_preferred_industry == $job_industry) {
                                    $recommended_jobs[] = array(
                                        'job_id' => $job['id'],
                                        'job_title' => $job['job_title']
                                    );
                                }
                            }
                        }
                    }

                    if (empty($job_applications)) {
                        // Check if the user and job have the same industry
                        if ($user_preferred_industry == $job_industry) {
                            $recommended_jobs[] = array(
                                'job_id' => $job['id'],
                                'job_title' => $job['job_title']
                            );
                        }
                    }
                }



                /*
                foreach ($jobs as $job) {
                    $job_industry = $job['jinindustry_id'];

                    // Check if the user and job have the same industry
                    if ($user_preferred_industry == $job_industry) {
                        $recommended_jobs[] = array(
                            'job_id' => $job['id'],
                            'job_title' => $job['job_title']
                        );
                    }
                }*/

                // Display the recommended jobs
            ?>

                <div class="col-md-4">
                    <h1>Recommended Jobs</h1>
                    <hr>
                    <?php if (empty($user_preferred_industry)) { ?>
                        <div class="alert alert-warning fade show" role="alert">
                            <strong>Warning!</strong><br>You have not set your preferred skills yet. Please update your profile to get recommended jobs.
                        </div>
                    <?php } ?>
                    <?php
                    foreach ($recommended_jobs as $job) :
                        $job_list_sql = mysqli_query($conn, "SELECT * FROM job_listing WHERE id =" . $job['job_id']);
                        $job_list = mysqli_fetch_assoc($job_list_sql);

                        $jinindustry_name = mysqli_query($conn, "SELECT * FROM jinindustry WHERE jinindustry_id =" . $job_list['jinindustry_id']);
                        $jinindustry = mysqli_fetch_assoc($jinindustry_name);
                    ?>
                        <div>
                            <h2><?php echo $job['job_title']; ?></h2>
                            <p>Job Industry: <?php echo $jinindustry['jinindustry_name']; ?></p>
                            <p>Job Salary: ₱<?php echo number_format($job_list['job_salary']); ?></p>
                            <a href="job_details.php?job_id=<?php echo $job['job_id']; ?>" class="btn btn-primary">View Job</a>
                            <hr>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php
            }
            ?>
            <div class="col-md-4">
                <h1>Latest Jobs</h1>
                <hr>
                <?php
                // Assuming $conn is your database connection
                $sql = "SELECT * FROM job_listing ORDER BY id DESC LIMIT 10";
                $result = mysqli_query($conn, $sql);

                // Check if there are any rows in the result set
                if (mysqli_num_rows($result) > 0) {
                    // Loop through the rows and display job information with job industry
                    while ($row = mysqli_fetch_assoc($result)) {
                        $sql2 = "SELECT * FROM jinindustry WHERE jinindustry_id =" . $row['jinindustry_id'];
                        $result2 = mysqli_query($conn, $sql2);
                        $row2 = mysqli_fetch_assoc($result2);

                        echo '<div>';
                        echo '<h2>' . $row['job_title'] . '</h2>';
                        // show job industry from $row2
                        echo '<p>Job Industry: ' . $row2['jinindustry_name'] . '</p>';
                        echo '<p>Job Salary: ₱' . number_format($row['job_salary']) . '</p>';
                        echo '<a href="job_details.php?job_id=' . $row['id'] . '" class="btn btn-primary">View Job</a>';
                        echo '</div><hr>';
                    }
                } else {
                    echo '<p>No job listings found.</p>';
                }

                // Close the result set and database connection
                mysqli_free_result($result);
                mysqli_close($conn);
                ?>
            </div><br>
            <div class="col-md-4">
                <h1>Facebook Feed</h1>
                <hr>
                <iframe src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2FMuntinlupaPESO%2F&tabs=timeline&small_header=true&adapt_container_width=true&hide_cover=true&show_facepile=false&appId" width="350" height="500" style="border:none;overflow:hidden;" scrolling="no" frameborder="0" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
            </div><br>
            <?php if (!isset($_SESSION["user_type"])) { ?>
                <div class="col-md-3">
                    <h1>Login or Register</h1>
                    <hr>
                    <div class="d-grid gap-2">
                        <a href="login.php" class="btn btn-primary btn-lg">Login</a>
                        <a href="register.php" class="btn btn-primary btn-lg">Register</a>
                    </div><br>
                </div>
            <?php
            } ?>
        </div>
    </div>
</body>

</html>