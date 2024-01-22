<?php

session_start();

require_once "config.php";

if (!isset($_SESSION["user_type"]) || empty($_SESSION["user_type"])) {
    header("location: login.php");
    exit;
}

// get user type and user id
$user_type = $_SESSION["user_type"];
$user_id = mysqli_real_escape_string($conn, $_SESSION["user_id"]);


if (isset($_GET["user_id"]) && !empty($_GET["user_id"])) {
    $profile_user_id = $_GET["user_id"];
} else {
    $profile_user_id = "";
}

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("s", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();


// Define variables and initialize with empty values
$gender = $row['sex']; // Assuming 'gender' is a column in your users table
$birth_day = $row['birth_day'];
$birth_month = $row['birth_month'];
$birth_year = $row['birth_year'];
$contact_number = $row['contact_number'];
$house_number = $row['house_number'];
$street = $row['street'];
$subdivision = $row['subdivision'];
$barangay = $row['barangay'];
$city = $row['city'];
$province = $row['province'];
$zip_code = $row['zip_code'];
$school_name = $row['school_name'];
$school_year_begin = $row['school_year_begin'];
$school_year_end = $row['school_year_end'];
$technicalschool_name = $row['technicalschool_name'];
$profile_picture = "uploads/{$user_id}/{$row['profile_image']}";

?>

<html lang="en">

<head>
    <title>PESO Job Portal - Profile</title>
    <link rel="stylesheet" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/img/peso_muntinlupa.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>

<body>
    <?php include "nav.php"; ?>
    <div class="container">
        <h1 class="mb-4">User Profile</h1>
        <div class="row gx-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Personal Information</h5>
                        <p class="card-text"><strong>User Type:</strong>
                            <?php echo ($user_type === "applicant") ? "Applicant" : "Company"; ?></p>
                        <p class="card-text"><strong>Name:</strong>
                            <?php echo "{$row['fname']} {$row['mname']} {$row['lname']} {$row['suffix']}"; ?></p>
                        <p class="card-text"><strong>Email:</strong> <?php echo $row['email']; ?></p>
                        <p class="card-text"><strong>Gender:</strong>
                            <?php echo ($gender == 'male') ? 'Male' : 'Female'; ?></p>
                        <p class="card-text">
                            <strong>Birthday:</strong>
                            <?php 
        // Convert birth month number to words
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        $birth_month_word = isset($months[$birth_month]) ? $months[$birth_month] : '';

        // Output the formatted birthday
        echo "{$birth_month_word} {$birth_day}, {$birth_year}"; 
    ?>
                        </p>
                        <p class="card-text"><strong>Contact Number:</strong> <?php echo $contact_number; ?></p>
                        <p class="card-text"><strong>Address:</strong>
                            <?php echo "{$house_number}, {$street}, {$subdivision}, {$barangay}, {$city}, {$province}, {$zip_code}"; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="d-flex justify-content-center align-items-center">
                        <img src="<?php echo $profile_picture; ?>" style="width:20vw;" class="card-img-top"
                            alt="Profile Picture">
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Education Information</h5>
                        <p class="card-text"><strong>School Name:</strong>
                            <?php echo "{$school_name} ({$school_year_begin} - {$school_year_end})"; ?></p>
                        <p class="card-text"><strong>Technical School Name:</strong>
                            <br><br>
                            <?php
                        // Show the "Edit Profile" link only if the user IDs do not match
                        if ($user_id != $profile_user_id) {
                            echo '<a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>';
                        }

                        // show request company_verification button if user is company to have permission to post job listing
                        if ($user_type === "company") {
                            echo '<br><br><a href="/company/request_company_verification.php" class="btn btn-primary">Request Company Verification</a>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php if ($user_type === "admin") : ?>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Files</h5>
                        <!-- Add content for file information here -->
                        <?php
                    // if user is applicant show button link to view nsrp form and biodata
                    if ($user_type === "applicant") {
                        // get applicant documents data from user id
                        $sql = "SELECT * FROM applicant_documents WHERE user_id = '$user_id'";
                        $result = mysqli_query($conn, $sql);
                        $row = mysqli_fetch_assoc($result);

                        // if nsrp_form is present show button link to view nsrp_form
                        if ($row['nsrp_form'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['nsrp_form'] . '" class="btn btn-primary">View NSRP Form</a>';
                        }
                        // if biodata_form is present show button link to view biodata_form
                        if ($row['biodata_form'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['biodata_form'] . '" class="btn btn-primary">View Biodata Form</a>';
                        }
                    }

                    // if user is company show button link to view loi, cp, sec_accredit, cda_license, dole_license, loc, mbpermit, job_vacant, job_solicitation, phjobnet_reg, cert_nopendingcase, cert_regSSS, cert_regPhHealth, cert_regPGIBG, sketch_map, 2303_bir document
                    if ($user_type === "company") {
                        // get company documents data from user id
                        $sql = "SELECT * FROM company_documents WHERE user_id = '$user_id'";
                        $result = mysqli_query($conn, $sql);
                        $row = mysqli_fetch_assoc($result);

                        // if loi is present show button link to view loi
                        if ($row['loi'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['loi'] . '" class="btn btn-primary">View LOI</a>';
                        }
                        // if cp is present show button link to view cp
                        if ($row['cp'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['cp'] . '" class="btn btn-primary">View CP</a>';
                        }
                        // if sec_accredit is present show button link to view sec_accredit
                        if ($row['sec_accredit'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['sec_accredit'] . '" class="btn btn-primary">View SEC Accreditation</a>';
                        }
                        // if cda_license is present show button link to view cda_license
                        if ($row['cda_license'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['cda_license'] . '" class="btn btn-primary">View CDA License</a>';
                        }
                        // if dole_license is present show button link to view dole_license
                        if ($row['dole_license'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['dole_license'] . '" class="btn btn-primary">View DOLE License</a>';
                        }
                        // if loc is present show button link to view loc
                        if ($row['loc'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['loc'] . '" class="btn btn-primary">View LOC</a>';
                        }
                        // if mbpermit is present show button link to view mbpermit
                        if ($row['mbpermit'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['mbpermit'] . '" class="btn btn-primary">View MB Permit</a>';
                        }
                        // if job_vacant is present show button link to view job_vacant
                        if ($row['job_vacant'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['job_vacant'] . '" class="btn btn-primary">View Job Vacant</a>';
                        }
                        // if job_solicitation is present show button link to view job_solicitation
                        if ($row['job_solicitation'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['job_solicitation'] . '" class="btn btn-primary">View Job Solicitation</a>';
                        }
                        // if phjobnet_reg is present show button link to view phjobnet_reg
                        if ($row['phjobnet_reg'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['phjobnet_reg'] . '" class="btn btn-primary">View PH Jobnet Registration</a>';
                        }
                        // if cert_nopendingcase is present show button link to view cert_nopendingcase
                        if ($row['cert_nopendingcase'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['cert_nopendingcase'] . '" class="btn btn-primary">View Certificate of No Pending Case</a>';
                        }
                        // if cert_regSSS is present show button link to view cert_regSSS
                        if ($row['cert_regSSS'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['cert_regSSS'] . '" class="btn btn-primary">View Certificate of Registration SSS</a>';
                        }
                        // if cert_regPhHealth is present show button link to view cert_regPhHealth
                        if ($row['cert_regPhHealth'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['cert_regPhHealth'] . '" class="btn btn-primary">View Certificate of Registration PhilHealth</a>';
                        }
                        // if cert_regPGIBG is present show button link to view cert_regPGIBG
                        if ($row['cert_regPGIBG'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['cert_regPGIBG'] . '" class="btn btn-primary">View Certificate of Registration PAG-IBIG</a>';
                        }
                        // if sketch_map is present show button link to view sketch_map
                        if ($row['sketch_map'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['sketch_map'] . '" class="btn btn-primary">View Sketch Map</a>';
                        }
                        // if 2303_bir is present show button link to view 2303_bir
                        if ($row['2303_bir'] != "") {
                            echo '<a href="uploads/' . $user_id . '/' . $row['2303_bir'] . '" class="btn btn-primary">View 2303 BIR</a>';
                        }
                    }
                    ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</body>

</html>