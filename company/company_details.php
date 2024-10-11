<?php
// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

$root = $_SERVER['DOCUMENT_ROOT'];

require $root . "/config.php";

if (isset($_GET["id"]) && !empty($_GET["id"])) {
    $cdocu_id = $_GET["id"];
} else {
    $cdocu_id = "";
}

// if user is not logged in redirect to login page
if (!isset($_SESSION["user_type"]) || empty($_SESSION["user_type"])) {
    $_SESSION['current_page'] = $_SERVER['REQUEST_URI'];
    header("location: /login.php");
    exit;
}

// if user is applicant show 404.php
if ($_SESSION["user_type"] == "applicant") {
    header("location: /404.php");
    exit;
}

// retrieve company document details
$sql = "SELECT * FROM company_documents WHERE user_id = ?";
if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $cdocu_id);
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
            $user_id = $row["user_id"];
            $loi = $row["loi"];
            $cp = $row["cp"];
            $sec_accredit = $row["sec_accredit"];
            $cda_license = $row["cda_license"];
            $dole_license = $row["dole_license"];
            $loc = $row["loc"];
            $mb_permit = $row["mb_permit"];
            $job_vacant = $row["job_vacant"];
            $job_solicitation = $row["job_solicitation"];
            $phjobnet_reg = $row["phjobnet_reg"];
            $cert_nopendingcase = $row["cert_nopendingcase"];
            $cert_regSSS = $row["cer_regSSS"];
            $cert_regPhHealth = $row["cert_regPhHealth"];
            $cert_regPGIBG = $row["cert_regPGIBG"];
            $sketch_map = $row["sketch_map"];
            $bir_2303 = $row["2303_bir"];
            $business_name = $row["business_name"];
            $trade_name = $row["trade_name"];
            $tin_number = $row["tin_number"];
            $pjn_accredited = $row["pjn_accredited"];
            $employment_type = $row["employment_type"];
            $employment_size = $row["employment_size"];
            $location_address = $row["location_address"];
            $location_barangay = $row["location_barangay"];
            $location_city = $row["location_city"];
            $location_province = $row["location_province"];
            $location_region = $row["location_region"];
            $contact_person_name = $row["contact_person_name"];
            $contact_person_position = $row["contact_person_position"];
            $telephone_number = $row["telephone_number"];
            $cellphone_number = $row["cellphone_number"];
            $fax_number = $row["fax_number"];
            $email = $row["email"];
            $registration_date = $row["registration_date"];
            $created_at = $row["created_at"];
            $updated_at = $row["updated_at"];
        } else {
            header("location: /404.php");
            exit;
        }
    } else {
        die("Error executing query: " . mysqli_error($conn));
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Company Profile - PESO Muntinlupa Job Portal</title>
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
    <div class="container">
        <h1 class="mb-4">Company Profile</h1>
        <img src="https://muntinlupacity.gov.ph/wp-content/uploads/2022/10/line_blue_yellow_red-scaled.jpg" class="img-fluid" alt="Responsive image">
        <br><br>
        <div class="row row-cols-1 row-cols-md-3 g-2">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"></h5>Company Information</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item"><strong>Business Name:</strong> <?php echo $business_name; ?></li>
                            <li class="list-group-item"><strong>Trade Name:</strong> <?php echo $trade_name; ?></li>
                            <li class="list-group-item"><strong>TIN Number:</strong> <?php echo $tin_number; ?></li>
                            <li class="list-group-item"><strong>PJN Accredited:</strong> <?php echo $pjn_accredited; ?></li>
                            <li class="list-group-item"><strong>Employment Type:</strong> <?php echo $employment_type; ?></li>
                            <li class="list-group-item"><strong>Employment Size:</strong> <?php echo $employment_size; ?></li>
                            <li class="list-group-item"><strong>Location Address:</strong> <?php echo $location_address; ?></li>
                            <li class="list-group-item"><strong>Location Barangay:</strong> <?php echo $location_barangay; ?></li>
                            <li class="list-group-item"><strong>Location City:</strong> <?php echo $location_city; ?></li>
                            <li class="list-group-item"><strong>Location Province:</strong> <?php echo $location_province; ?></li>
                            <li class="list-group-item"><strong>Location Region:</strong> <?php echo $location_region; ?></li>
                            <li class="list-group-item"><strong>Contact Person Name:</strong> <?php echo $contact_person_name; ?></li>
                            <li class="list-group-item"><strong>Contact Person Position:</strong> <?php echo $contact_person_position; ?></li>
                            <li class="list-group-item"><strong>Telephone Number:</strong> <?php echo $telephone_number; ?></li>
                            <li class="list-group-item"><strong>Cellphone Number:</strong> <?php echo $cellphone_number; ?></li>
                            <li class="list-group-item"><strong>Fax Number:</strong> <?php echo $fax_number; ?></li>
                            <li class="list-group-item"><strong>Email:</strong> <?php echo $email; ?></li>
                            <li class="list-group-item"><strong>Registration Date:</strong> <?php echo $registration_date; ?></li>
                            <li class="list-group-item"><strong>Created At:</strong> <?php echo $created_at; ?></li>
                            <li class="list-group-item"><strong>Updated At:</strong> <?php echo $updated_at; ?></li>
                        </ul>
                    </div>
                    // company documents
                    <div class="card-body">
                        <h5 class="card-title"></h5>Company Documents</h5>
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
                                'bir_2303' => 'BIR Form 2303'
                            );
                            foreach ($documents as $key => $value) {
                                if (!empty($row[$key])) {
                                    echo "<a href='/company/documents/" . $row[$key] . "' target='_blank' class='btn btn-primary'>" . $value . "</a>";
                                } else {
                                    echo "<a href='#' class='btn btn-secondary disabled'>" . $value . "</a>";
                                }
                            } 
                        ?>
                </div>
