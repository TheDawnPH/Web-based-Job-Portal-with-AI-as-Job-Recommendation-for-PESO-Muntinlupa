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
        if ($job_application['application_status'] === '1') {
            $job_accepted_count++;
        } elseif ($job_application['application_status'] === '2') {
            $job_accepted_count--;
        } else {
            continue;
        }
    }


    if (empty($job_applications)) {
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
</head>

<body>
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v19.0" nonce="CXhHUdMr"></script>
    <?php include 'nav.php'; ?>
    <h1 class="text-container text-center" style="font-family:'Montserrat', sans-serif; color:#000080;"><strong>WELCOME TO MUNTINLUPA CITY JOB PORTAL</strong></h1>
    <p class="lead text-center text-container" style="font-family:'Montserrat', sans-serif; color:#000080;">May trabaho para sa mga Muntinlupeño</p>
    <div class="container">
        <!-- <div class="alert alert-danger fade show" role="alert">
            Hello! This site is a capstone project for Muntinlupa PESO, this site is <strong>not affiliated in Muntinlupa PESO, and Muntinlupa City Government. This is only a proposal for Muntinlupa PESO.</strong> Please do not insert actual personal information. Thank you!
        </div> -->
        <?php if ($cverify["company_verified"] == 0 && isset($_SESSION["user_type"]) && $_SESSION["user_type"] === "company") { ?>
            <div class="alert alert-danger fade show" role="alert">
                <strong>Warning!</strong><br>Your company is not yet verified. Please <a href="company/request_company_verification.php">click here</a> to verify your company.
            </div>
        <?php } ?>
        <div class="row">
            <div class="col-md-4">
                <div class="card" style="width: 100%">
                    <div class="card-body">
                        <h4 class="card-title">Facebook Feed</h4>
                        <div class="fb-page" data-href="https://www.facebook.com/MuntinlupaPESO" data-tabs="timeline" data-width="" data-height="" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="false">
                            <blockquote cite="https://www.facebook.com/MuntinlupaPESO" class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/MuntinlupaPESO">Muntinlupa PESO</a></blockquote>
                        </div>
                        <?php if (!isset($_SESSION["user_type"])) { ?>
                            <hr>
                            <h4 class="card-title">Login / Register</h4>
                            <a href="login.php" class="btn btn-primary">Login</a>
                            <a href="register.php" class="btn btn-warning">Register</a>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <?php if (isset($_SESSION["user_type"]) && $_SESSION["user_type"] === 'applicant') { ?>
                            <h3 class="card-title" style="text-align: center">Recommended Jobs</h3>
                            <hr>
                            <?php
                            $sql = "SELECT * FROM job_listing";
                            $result = mysqli_query($conn, $sql);

                            $jobs = [];

                            while ($row = mysqli_fetch_assoc($result)) {
                                $jobs[] = $row;
                            }

                            // get all number of approved job applications per job
                            foreach ($jobs as $key => $job) {
                                $jobs[$key]['job_acceptance_rate'] = getJobAcceptanceRate($job['id']);
                            }

                            // sort jobs by job acceptance rate
                            usort($jobs, function ($a, $b) {
                                return $b['job_acceptance_rate'] - $a['job_acceptance_rate'];
                            });

                            // sort recommended jobs by job category chosen by the applicant
                            if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == "applicant") {
                                //get user jininustry_id
                                $sql = "SELECT jinindustry_id FROM users WHERE user_id = " . $_SESSION['user_id'];
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);

                                $applicant_category = $row['jinindustry_id'];

                                $recommended_jobs = array_filter($jobs, function ($job) use ($applicant_category) {
                                    return $job['jinindustry_id'] == $applicant_category;
                                });

                                // get the top 5 jobs with the highest job acceptance rate
                                $recommended_jobs = array_slice($recommended_jobs, 0, 10);
                            }

                            if (empty($recommended_jobs)) {
                                echo "<p>No recommended jobs found.</p>";
                            }


                            ?>
                            <?php foreach ($recommended_jobs as $job) : ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <table>
                                            <tbody>
                                                <tr class="job-row">
                                                    <td style="width: 100%; overflow: hidden; white-space: normal;">
                                                        <div class="btn-group" role="group">
                                                            <h5><?php echo $job['job_title']; ?></h5>
                                                        </div>
                                                    </td>
                                                    <td style="width: 100%; text-align: right;">
                                                        <div class="btn-group" role="group">
                                                            <a href="job_details.php?job_id=<?php echo $job['id']; ?>" class="btn btn-warning">View</a>&nbsp;
                                                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == "applicant") : ?>
                                                                <a href="job_applications.php?job_id=<?php echo $job['id']; ?>" class="btn btn-primary">Apply</a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <?php echo $job['job_acceptance_rate']; ?>% Acceptance Rate
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php } ?>
                        <h3 class="card-title" style="text-align: center">Latest Jobs</h3>
                        <hr>
                        <?php
                        $sql = "SELECT * FROM job_listing ORDER BY created_at DESC LIMIT 10";
                        $result = mysqli_query($conn, $sql);
                        $jobs = [];

                        while ($row = mysqli_fetch_assoc($result)) {
                            $jobs[] = $row;
                        }
                        ?>
                        <?php foreach ($jobs as $job) : ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <table>
                                        <tbody>
                                            <tr class="job-row">
                                                <td style="width: 100%; overflow: hidden; white-space: normal;">
                                                    <div class="btn-group" role="group">
                                                        <h5><?php echo $job['job_title']; ?></h5>
                                                    </div>
                                                </td>
                                                <td style="width: 100%; text-align: right;">
                                                    <div class="btn-group" role="group">
                                                        <a href="job_details.php?job_id=<?php echo $job['id']; ?>" class="btn btn-warning">View</a>&nbsp;
                                                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == "applicant") : ?>
                                                            <a href="job_applications.php?job_id=<?php echo $job['id']; ?>" class="btn btn-primary">Apply</a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
