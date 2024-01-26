<?php

// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require_once "config.php";

if (!isset($_SESSION["user_type"]) || empty($_SESSION["user_type"])) {
    header("location: login.php");
    exit;
}

// get user type and user id
$user_id = $_SESSION["user_id"];

if (isset($_GET["user_id"]) && !empty($_GET["user_id"])) {
    $profile_user_id = $_GET["user_id"];
} else {
    $profile_user_id = $user_id;
}

//if user in $_GET["user_id"] does not exists, show 404.php
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("s", $profile_user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($result->num_rows == 0) {
    header("location: 404.php");
    exit;
}

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
$profile_picture = "uploads/{$profile_user_id}/{$row['profile_image']}";
$jinindustry = $row['jinindustry_id'];
$user_type = $row['user_type'];

// name jinindustry_id
$stmt2 = $conn->prepare("SELECT * FROM jinindustry WHERE jinindustry_id = ?");
$stmt2->bind_param("s", $jinindustry);
$stmt2->execute();
$result2 = $stmt2->get_result();
$row2 = $result2->fetch_assoc();

$stmt2->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>PESO Job Portal - Profile</title>
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
    <script src="https://kit.fontawesome.com/690deb639d.js" crossorigin="anonymous"></script>
</head>

<body>
    <?php include "nav.php"; ?>
    <div class="container mt-3">
        <h1 class="mb-4">User Profile</h1>
        <div class="row row-cols-1 row-cols-md-3 g-2">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Personal Information</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>User Type:</strong> <?php echo ($user_type == 'applicant') ? "Applicant" : (($user_type == 'company') ? "Company" : "Admin"); ?></li>
                            <li class="list-group-item"><strong>Name:</strong> <?php echo "{$row['fname']} {$row['mname']} {$row['lname']} {$row['suffix']}"; ?></li>
                            <li class="list-group-item"><strong>Email:</strong> <?php echo $row['email']; ?></li>
                            <li class="list-group-item"><strong>Gender:</strong> <?php echo ($gender == 'male') ? 'Male' : 'Female'; ?></li>
                            <li class="list-group-item"><strong>Birthday:</strong>
                                <?php
                                $months = [
                                    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                                    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                                    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                                ];
                                $birth_month_word = isset($months[$birth_month]) ? $months[$birth_month] : '';
                                echo "{$birth_month_word} {$birth_day}, {$birth_year}";
                                ?>
                            </li>
                            <li class="list-group-item"><strong>Contact Number:</strong> <?php echo $contact_number; ?></li>
                            <li class="list-group-item"><strong>Address:</strong>
                                <?php echo "{$house_number}, {$street}, {$subdivision}, {$barangay}, {$city}, {$province}, {$zip_code}"; ?>
                            </li>
                            <li class="list-group-item"><strong>Selected Job Industry:</strong> <?php if(!empty($row2['jinindustry_name'])){echo $row2['jinindustry_name'];} ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <br>
                    <div class="d-flex justify-content-center align-items-center">
                        <?php
                        if ($profile_picture == "uploads/{$profile_user_id}/") {
                            echo '<img src="img/cat.png" style="width:20vw;" class="card-img-top" alt="Profile Picture">';
                        } else {
                            echo '<img src="' . $profile_picture . '" style="width:20vw;" class="card-img-top" alt="Profile Picture">';
                        }
                        ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Education Information</h5>
                        <p class="card-text"><strong>School Name:</strong>
                            <?php echo "{$school_name} ({$school_year_begin} - {$school_year_end})"; ?></p>
                        <p class="card-text"><strong>Technical School Name:</strong><br><br></p>
                        <?php
                        if ($user_id == $profile_user_id) {
                            echo '<a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>';
                        }
                        if ($_SESSION['user_type'] === "company" && $user_id === $profile_user_id) {
                            echo '<br><br><a href="/company/request_company_verification.php" class="btn btn-primary">Request Company Verification</a>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <?php if ($user_type === "applicant" || $user_type === "admin") : ?>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Applicant Files</h5>
                            <hr>
                            <?php
                            $sql = "SELECT * FROM users WHERE user_id = '$profile_user_id'";
                            $result = mysqli_query($conn, $sql);
                            $row = mysqli_fetch_assoc($result);
                            if ($row['nsrp_form'] != "") {
                                echo '<a target="_blank" href="uploads/' . $profile_user_id . '/' . $row['nsrp_form'] . '" class="btn btn-primary">View NSRP Form</a>&nbsp;';
                            }
                            if ($row['biodata_form'] != "") {
                                echo '<a target="_blank" href="uploads/' . $profile_user_id . '/' . $row['biodata_form'] . '" class="btn btn-primary">View Biodata Form</a>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ($user_type === "company" || $user_type === "admin") : ?>
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Company Documents</h5>
                            <hr>
                            <?php
                            $documents = array(
                                'loi' => 'Letter of Intent',
                                'cp' => 'Company Profile',
                                'sec_accredit' => 'SEC Accreditation',
                                'cda_license' => 'CDA License',
                                'dole_license' => 'DOLE License',
                                'loc' => 'Location Map',
                                'mbpermit' => 'Mayor\'s Business Permit (MB Permit)',
                                'job_vacant' => 'Job Vacant Announcement',
                                'job_solicitation' => 'Job Solicitation',
                                'phjobnet_reg' => 'PH JobNet Registration',
                                'cert_nopendingcase' => 'Certificate of No Pending Case',
                                'cert_regSSS' => 'Certificate of SSS Registration',
                                'cert_regPhHealth' => 'Certificate of PhilHealth Registration',
                                'cert_regPGIBG' => 'Certificate of PAG-IBIG Registration',
                                'sketch_map' => 'Sketch Map',
                                '2303_bir' => 'BIR Form 2303'
                            );

                            foreach ($documents as $documentKey => $documentLabel) {
                                $sql = "SELECT * FROM company_documents WHERE user_id = '$profile_user_id'";
                                $result = mysqli_query($conn, $sql);
                                $row = mysqli_fetch_assoc($result);
                                if (isset($row[$documentKey]) && $row[$documentKey] != "") {
                                    echo '<a target="_blank" href="uploads/' . $profile_user_id . '/' . $row[$documentKey] . '" class="btn btn-primary fluid">View ' . $documentLabel . '</a><hr>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>