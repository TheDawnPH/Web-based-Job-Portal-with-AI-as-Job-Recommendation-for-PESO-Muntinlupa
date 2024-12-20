<?php
session_start();

include 'config.php';

function getJobAcceptanceRate($job_id)
{
    global $conn;

    $sql = "SELECT * FROM job_applications WHERE job_id = " . $job_id;
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Error executing query: " . mysqli_error($conn));
    }

    $totalApplications = mysqli_num_rows($result);
    $acceptedApplications = 0;

    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['application_status'] === '1') {
            $acceptedApplications++;
        }
    }

    return $totalApplications ? round(($acceptedApplications / $totalApplications) * 100) : 0;
}



?>

<html>

<head>
    <title>PESO Muntinlupa Job Portal</title>
    <link rel="stylesheet" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/img/peso_muntinlupa.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div id="fb-root"></div>
    <script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v21.0">
    </script>
    <?php include 'nav.php'; ?>
    <h1 class="text-container text-center" style="font-family:'Montserrat', sans-serif; color:#003595;"><strong>WELCOME
            TO PESO MUNTINLUPA JOB PORTAL</strong></h1>
    <p class="lead text-center text-container" style="font-family:'Montserrat', sans-serif; color:#003595;">May trabaho
        para sa mga Muntinlupeño</p>
    <img src="https://muntinlupacity.gov.ph/wp-content/uploads/2023/10/CGM_Line-Footer_final-scaled.jpg"
        class="img-fluid" alt="Responsive image">
    <div class="container">
        <!-- <div class="alert alert-danger fade show" role="alert">
            Hello! This site is a capstone project for Muntinlupa PESO, this site is <strong>not affiliated in Muntinlupa PESO, and Muntinlupa City Government. This is only a proposal for Muntinlupa PESO.</strong> Please do not insert actual personal information. Thank you!
        </div> -->
        <!-- <div class="alert alert-danger fade show" role="alert">
            <strong>Notice:</strong><br>This site is currently undergoing migration to a new server. Some features may
            not work as expected. We apologize for the inconvenience.
        </div> -->
        <?php
        $today = date('Y-m-d');
        // non working days holiday api
        $holiday_api = "https://holidayapi.com/v1/holidays?pretty&key=" . $_ENV['HOLIDAY_API_KEY'] . "&country=PH&year=" . date('Y');
        $holiday_json = file_get_contents($holiday_api);
        $holiday_array = json_decode($holiday_json, true);
        $holidays = array_column($holiday_array, 'date');
        $holidays = array_map(function ($holiday) {
            return date('Y-m-d', strtotime($holiday));
        }
        , $holidays);
        $holidays = array_filter($holidays, function ($holiday) use ($today) {
            return $holiday === $today;
        });

        $christmas = date('Y') . '-12-21';
        $new_year = date('Y') . '-01-01';

        if (!empty($holidays)) {
            echo "<div class='alert alert-warning fade show' role='alert'>
            <h4 class='alert-heading'>A Message from PESO Muntinlupa</h4>
            <p>Today is a non-working holiday. Please be advised that there may be delays in processing your applications.</p>
            <p>Thank you for your understanding.</p>
            </div>";
        }

        if ($today === $christmas) {
            echo "<div class='alert alert-success fade show' role='alert'>
            <h4 class='alert-heading'>A Message from PESO Muntinlupa</h4>
            <p>Today is Christmas Day. Please be advised that there may be delays in processing your applications.</p>
            <p>Thank you for your understanding.</p>
            <hr>
            <p class='mb-0'>Merry Christmas and Happy Holidays!</p>
            <p class='mb-0'>- PESO Muntinlupa</p>
            </div>";
        }

        if ($today === $new_year) {
            echo "<div class='alert alert-success fade show' role='alert'>
            <h4 class='alert-heading'>A Message from PESO Muntinlupa</h4>
            <p>Today is New Year's Day. Please be advised that there may be delays in processing your applications.</p>
            <p>Thank you for your understanding.</p>
            <hr>
            <p class='mb-0'>We wish you a Happy New Year!</p>
            <p class='mb-0'>- PESO Muntinlupa</p>
            </div>";
        }

        ?>
        <?php if ($cverify["company_verified"] == 0 && isset($_SESSION["user_type"]) && $_SESSION["user_type"] === "company") { ?>
        <div class="alert alert-danger fade show" role="alert">
            <strong>Warning!</strong><br>Your company is not yet verified. Please <a
                href="company/request_company_verification.php">click here</a> to verify your company.
        </div>
        <?php } ?>
        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <?php if (isset($_SESSION["user_type"]) && $_SESSION["user_type"] === 'applicant') { ?>
                        <h3 class="card-title text-center">Recommended Jobs</h3>
                        <img src="https://muntinlupacity.gov.ph/wp-content/uploads/2022/10/line_blue_yellow_red-scaled.jpg"
                            class="img-fluid" alt="Responsive image"><br><br>
                        <?php
                            $sql = "SELECT * FROM job_listing WHERE visible = 1";
                            $result = mysqli_query($conn, $sql);

                            if (!$result) {
                                die("Error executing query: " . mysqli_error($conn));
                            }

                            $jobs = mysqli_fetch_all($result, MYSQLI_ASSOC);

                            foreach ($jobs as &$job) {
                                $job['job_acceptance_rate'] = getJobAcceptanceRate($job['id']);
                            }

                            usort($jobs, function ($a, $b) {
                                return $b['job_acceptance_rate'] <=> $a['job_acceptance_rate'];
                            });

                            if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == "applicant") {
                                $applicant_category = mysqli_fetch_assoc(mysqli_query($conn, "SELECT jinindustry_id FROM users WHERE user_id = " . $_SESSION['user_id']))['jinindustry_id'];
                                $recommended_jobs = array_filter($jobs, function ($job) use ($applicant_category) {
                                    return $job['jinindustry_id'] == $applicant_category;
                                });

                                $recommended_jobs = array_slice($recommended_jobs, 0, 10);

                                if (empty($recommended_jobs)) {
                                     echo "<div class='alert alert-danger fade show' role='alert'>No jobs available.</div>";
                                }

                                foreach ($recommended_jobs as $job) : ?>
                        <div class="card mb-3">
                            <div class="card-body">
                                <table>
                                    <tbody>
                                        <tr class="job-row">
                                            <td style="width: 100%; overflow: hidden; white-space: normal;">
                                                <div class="btn-group" role="group">
                                                    <h5><?php echo htmlspecialchars($job['job_title']); ?></h5>
                                                </div>
                                            </td>
                                            <td style="width: 100%; text-align: right;">
                                                <div class="btn-group" role="group">
                                                    <a href="job_details.php?job_id=<?php echo urlencode($job['id']); ?>"
                                                        class="btn btn-warning">View</a>&nbsp;
                                                    <a href="job_applications.php?job_id=<?php echo urlencode($job['id']); ?>"
                                                        class="btn btn-primary">Apply</a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php } ?>
                        <?php } ?>
                        <h3 class="card-title" style="text-align: center">Latest Jobs</h3>
                        <img src="https://muntinlupacity.gov.ph/wp-content/uploads/2022/10/line_blue_yellow_red-scaled.jpg"
                            class="img-fluid" alt="Responsive image"><br><br>
                        <?php
                        $sql = "SELECT * FROM job_listing WHERE visible = 1 ORDER BY created_at DESC LIMIT 10";
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
                                                    <a href="job_details.php?job_id=<?php echo $job['id']; ?>"
                                                        class="btn btn-warning">View</a>&nbsp;
                                                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == "applicant") : ?>
                                                    <a href="job_applications.php?job_id=<?php echo $job['id']; ?>"
                                                        class="btn btn-primary">Apply</a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($jobs)) : ?>
                        <div class="alert alert-danger fade show" role="alert">
                            No jobs available.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card" style="width: 100%">
                    <div class="card-body">
                        <h4 class="card-title">Facebook Feed</h4>
                        <img src="https://muntinlupacity.gov.ph/wp-content/uploads/2022/10/line_blue_yellow_red-scaled.jpg"
                            class="img-fluid" alt="Responsive image"><br>
                        <div class="d-block d-sm-none">
                            <br>
                            <p class="text-center">Facebook feed is not available on mobile/tablet devices. Click the
                                button below to view our Facebook page.</p>
                            <a href="https://www.facebook.com/MuntinlupaPESO" class="btn btn-primary">ⓕ Muntinlupa
                                PESO</a><br><br>
                        </div>
                        <div class="fb-page" data-href="https://www.facebook.com/MuntinlupaPESO" data-show-posts="true"
                            data-width="500" data-height="" data-small-header="false" data-adapt-container-width="true"
                            data-hide-cover="false" data-show-facepile="false">
                            <blockquote cite="https://www.facebook.com/MuntinlupaPESO" class="fb-xfbml-parse-ignore"><a
                                    href="https://www.facebook.com/MuntinlupaPESO">Muntinlupa PESO</a></blockquote>
                        </div>
                        <img src="https://muntinlupacity.gov.ph/wp-content/uploads/2022/10/line_blue_yellow_red-scaled.jpg"
                            class="img-fluid" alt="Responsive image"><br>
                        <?php if (!isset($_SESSION["user_type"])) { ?>
                        <hr>
                        <h4 class="card-title">Login / Register</h4>
                        <a href="login.php" class="btn btn-primary">Login</a>
                        <a href="register.php" class="btn btn-warning">Register</a>
                        <?php } ?>
                        <hr>
                        <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                            data-bs-target="#PrivacyPopup">
                            Data Privacy Clause
                        </button>
                        <a href="https://muntinlupacity.gov.ph/" class="btn btn-primary" role="button">City Website</a>
                        <hr>
                        <a href="about.php" class="btn btn-primary" role="button">About Website</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade modal-lg PrivacyPopup" id="PrivacyPopup" tabindex="-1" role="dialog"
            aria-labelledby="PrivacyPopuplabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="PrivacyPopuplabel">Data Privacy Clause and Privacy Notice</h5>
                    </div>
                    <div class="modal-body">
                        <h3>Data Privacy Clause</h3>
                        <p>This is to certify that all data/information that I have provided in this form are true to
                            the best of my knowledge. I was informed of the nature, purpose and extent of the use of
                            such information and my right to withdraw my consent in accordance with the <a
                                target="_blank"
                                href="https://privacy.gov.ph/implementing-rules-regulations-data-privacy-act-2012/">Implementing
                                Rules and Regulations of the Data Privacy Act of 2012</a>.</p>
                        <br>
                        <h3>Privacy Notice</h3>
                        <p><b>PERSONAL DATA COLLECTED:</b><br>
                            We at the Public Employment Service Office shall collect and process the following personal
                            data, such as but not limited to the following: (Name, address, contact information, other
                            pertinent and relevant information for the application/requests) through (online form or
                            physical form or whatever medium).<br><br>
                            <b>PURPOSE AND DATA USAGE:</b><br>
                            The personal data collected shall be used for the following legitimate purposes: Employment
                            and shall only be processed to the extent necessary for compliance with the office’s legal
                            obligation and mandates and to further the City Government’s legitimate interest.<br><br>
                            <b>STORAGE AND DISPOSAL:</b><br>
                            The data shall be stored in our (Archive/Filing Cabinets/Vault/Database/etc.) and shall be
                            disposed of in accordance with the National Archives of the Philippines and other relevant
                            issuances.<br><br>
                            <b>DISCLOSURE:</b><br>
                            We treat your personal information with utmost confidentiality and shall not be disclosed of
                            nor shared with any unauthorized person, unless necessary for any other basis for lawful
                            processing of information under the Data Privacy Act of 2012 and in adherence to the general
                            data principles of transparency, legitimate purpose and proportionality.<br><br>
                            As our data subject, we assure you that we extend all reasonable efforts to ensure that the
                            data collected and processed is complete, accurate and up to date. You have the right to
                            access and to ask for a copy of your personal information, as well as to ask for its
                            correction, erasure or blocking, and to file a complaint, if necessary.<br><br>
                            Exercise of any of the following data subject rights may be coursed through our Data
                            Protection Officer through <a
                                href="mailto:dpo@muntinlupacity.gov.ph">dpo@muntinlupacity.gov.ph</a>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal">Yes, I'm in</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>