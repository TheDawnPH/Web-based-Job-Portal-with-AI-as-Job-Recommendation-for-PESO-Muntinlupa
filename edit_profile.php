<?php

session_start();

require_once "config.php";

if (!isset($_SESSION["user_type"]) || empty($_SESSION["user_type"])) {
    header("location: login.php");
    exit;
}

// get user type and user id
$user_type = $_SESSION["user_type"];
$user_id = $_SESSION["user_id"];

// get user data from user id
$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

// Define variables and initialize with values from row
$gender = $row["sex"];
$birth_day = $row["birth_day"];
$birth_month = $row["birth_month"];
$birth_year = $row["birth_year"];
$contact_number = $row["contact_number"];
$house_number = $row["house_number"];
$street = $row["street"];
$subdivision = $row["subdivision"];
$barangay = $row["barangay"];
$city = $row["city"];
$province = $row["province"];
$zip_code = $row["zip_code"];
$school_name = $row["school_name"];
$school_year_begin = $row["school_year_begin"];
$school_year_end = $row["school_year_end"];
$technicalschool_name = $row["technicalschool_name"];
$nsrp_form = $biodata_form = $profile_picture = "";


// Processing form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Prepare SQL statement
    $sql = "UPDATE users SET sex=?, birth_day=?, birth_month=?, birth_year=?, contact_number=?, house_number=?, street=?, subdivision=?, barangay=?, city=?, province=?, zip_code=?, school_name=?, school_year_begin=?, school_year_end=?, technicalschool_name=?, nsrp_form = IFNULL(?, nsrp_form), biodata_form = IFNULL(?, biodata_form), profile_image = IFNULL(?, profile_image) WHERE user_id=?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Bind variables to the prepared statement as parameters
        mysqli_stmt_bind_param($stmt, "siiiissssssisiisssss", $param_sex, $param_birth_day, $param_birth_month, $param_birth_year, $param_contact_number, $param_house_number, $param_street, $param_subdivision, $param_barangay, $param_city, $param_province, $param_zip_code, $param_school_name, $param_school_year_begin, $param_school_year_end, $param_technicalschool_name, $param_nsrp_form, $param_biodata_form, $param_profile_picture, $param_user_id);

        // Set parameters
        $param_sex = $_POST["gender"];
        $param_birth_day = $_POST["birth_day"];
        $param_birth_month = $_POST["birth_month"];
        $param_birth_year = $_POST["birth_year"];
        $param_contact_number = $_POST["contact_number"];
        $param_house_number = $_POST["house_number"];
        $param_street = $_POST["street"];
        $param_subdivision = $_POST["subdivision"];
        $param_barangay = $_POST["barangay"];
        $param_city = $_POST["city"];
        $param_province = $_POST["province"];
        $param_zip_code = $_POST["zip_code"];
        $param_school_name = $_POST["school_name"];
        $school_year_begin = !empty($row['school_year_begin']) ? $row['school_year_begin'] : null;
        $school_year_end = !empty($row['school_year_end']) ? $row['school_year_end'] : null;
        $param_technicalschool_name = $_POST["technicalschool_name"];
        $param_nsrp_form = !empty($_FILES["nsrp_form"]["name"]) ? $_FILES["nsrp_form"]["name"] : NULL;
        $param_biodata_form = !empty($_FILES["biodata_form"]["name"]) ? $_FILES["biodata_form"]["name"] : NULL;
        $param_profile_picture = !empty($_FILES["profile_picture"]["name"]) ? $_FILES["profile_picture"]["name"] : NULL;
        $param_user_id = $user_id;
        // Execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            // Create a directory for the user if it doesn't exist
            $user_upload_dir = "uploads/" . $user_id;
            if (!is_dir($user_upload_dir)) {
                mkdir($user_upload_dir, 0755, true);
            }

            // Move uploaded files to the user-specific folder
            move_uploaded_file($_FILES["nsrp_form"]["tmp_name"], $user_upload_dir . "/" . $_FILES["nsrp_form"]["name"]);
            move_uploaded_file($_FILES["biodata_form"]["tmp_name"], $user_upload_dir . "/" . $_FILES["biodata_form"]["name"]);
            move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $user_upload_dir . "/" . $_FILES["profile_picture"]["name"]);

            $success = "Profile updated successfully!";
        } else {
            $error =  "Error updating profile.";
        }


        // Close statement
        mysqli_stmt_close($stmt);
    }

    // Insert data into the company_documents table
    $company_documents_sql = "INSERT INTO company_documents (user_id, loi, cp, sec_accredit, cda_license, dole_license, loc, mbpermit, job_vacant, job_solicitation, phjobnet_reg, cert_nopendingcase, cert_regSSS, cert_regPhHealth, cert_regPGIBG, sketch_map, 2303_bir) VALUES (?, IFNULL(?, NULL), IFNULL(?, NULL), IFNULL(?, NULL), IFNULL(?, NULL), IFNULL(?, NULL), IFNULL(?, NULL), IFNULL(?, NULL), IFNULL(?, NULL), IFNULL(?, NULL), IFNULL(?, NULL), IFNULL(?, NULL), IFNULL(?, NULL), IFNULL(?, NULL), IFNULL(?, NULL), IFNULL(?, NULL), IFNULL(?, NULL))";

    if ($stmt = mysqli_prepare($conn, $company_documents_sql)) {
        mysqli_stmt_bind_param($stmt, "isssssssssssssss", $param_user_id, $param_loi, $param_cp, $param_sec_accredit, $param_cda_license, $param_dole_license, $param_loc, $param_mbpermit, $param_job_vacant, $param_job_solicitation, $param_phjobnet_reg, $param_cert_nopendingcase, $param_cert_regSSS, $param_cert_regPhHealth, $param_cert_regPGIBG, $param_sketch_map, $param_2303_bir);

        // Set parameters for company_documents table
        $param_user_id = $user_id;
        $param_loi = !empty($_FILES["loi"]["name"]) ? $_FILES["loi"]["name"] : null;
        $param_cp = !empty($_FILES["cp"]["name"]) ? $_FILES["cp"]["name"] : null;
        $param_sec_accredit = !empty($_FILES["sec_accredit"]["name"]) ? $_FILES["sec_accredit"]["name"] : null;
        $param_cda_license = !empty($_FILES["cda_license"]["name"]) ? $_FILES["cda_license"]["name"] : null;
        $param_dole_license = !empty($_FILES["dole_license"]["name"]) ? $_FILES["dole_license"]["name"] : null;
        $param_loc = !empty($_FILES["loc"]["name"]) ? $_FILES["loc"]["name"] : null;
        $param_mbpermit = !empty($_FILES["mbpermit"]["name"]) ? $_FILES["mbpermit"]["name"] : null;
        $param_job_vacant = !empty($_FILES["job_vacant"]["name"]) ? $_FILES["job_vacant"]["name"] : null;
        $param_job_solicitation = !empty($_FILES["job_solicitation"]["name"]) ? $_FILES["job_solicitation"]["name"] : null;
        $param_phjobnet_reg = !empty($_FILES["phjobnet_reg"]["name"]) ? $_FILES["phjobnet_reg"]["name"] : null;
        $param_cert_nopendingcase = !empty($_FILES["cert_nopendingcase"]["name"]) ? $_FILES["cert_nopendingcase"]["name"] : null;
        $param_cert_regSSS = !empty($_FILES["cert_regSSS"]["name"]) ? $_FILES["cert_regSSS"]["name"] : null;
        $param_cert_regPhHealth = !empty($_FILES["cert_regPhHealth"]["name"]) ? $_FILES["cert_regPhHealth"]["name"] : null;
        $param_cert_regPGIBG = !empty($_FILES["cert_regPGIBG"]["name"]) ? $_FILES["cert_regPGIBG"]["name"] : null;
        $param_sketch_map = !empty($_FILES["sketch_map"]["name"]) ? $_FILES["sketch_map"]["name"] : null;
        $param_2303_bir = !empty($_FILES["2303_bir"]["name"]) ? $_FILES["2303_bir"]["name"] : null;


        // Execute the prepared statement
        if (mysqli_stmt_execute($stmt)) {
            // Move uploaded files to the user-specific folder
            $user_upload_dir = "uploads/" . $user_id;
            if (!is_dir($user_upload_dir)) {
                mkdir($user_upload_dir, 0755, true);
            }

            move_uploaded_file($_FILES["loi"]["tmp_name"], $user_upload_dir . "/" . $_FILES["loi"]["name"]);
            move_uploaded_file($_FILES["cp"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cp"]["name"]);
            move_uploaded_file($_FILES["sec_accredit"]["tmp_name"], $user_upload_dir . "/" . $_FILES["sec_accredit"]["name"]);
            move_uploaded_file($_FILES["cda_license"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cda_license"]["name"]);
            move_uploaded_file($_FILES["dole_license"]["tmp_name"], $user_upload_dir . "/" . $_FILES["dole_license"]["name"]);
            move_uploaded_file($_FILES["loc"]["tmp_name"], $user_upload_dir . "/" . $_FILES["loc"]["name"]);
            move_uploaded_file($_FILES["mbpermit"]["tmp_name"], $user_upload_dir . "/" . $_FILES["mbpermit"]["name"]);
            move_uploaded_file($_FILES["job_vacant"]["tmp_name"], $user_upload_dir . "/" . $_FILES["job_vacant"]["name"]);
            move_uploaded_file($_FILES["job_solicitation"]["tmp_name"], $user_upload_dir . "/" . $_FILES["job_solicitation"]["name"]);
            move_uploaded_file($_FILES["phjobnet_reg"]["tmp_name"], $user_upload_dir . "/" . $_FILES["phjobnet_reg"]["name"]);
            move_uploaded_file($_FILES["cert_nopendingcase"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cert_nopendingcase"]["name"]);
            move_uploaded_file($_FILES["cert_regSSS"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cert_regSSS"]["name"]);
            move_uploaded_file($_FILES["cert_regPhHealth"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cert_regPhHealth"]["name"]);
            move_uploaded_file($_FILES["cert_regPGIBG"]["tmp_name"], $user_upload_dir . "/" . $_FILES["cert_regPGIBG"]["name"]);
            move_uploaded_file($_FILES["sketch_map"]["tmp_name"], $user_upload_dir . "/" . $_FILES["sketch_map"]["name"]);
            move_uploaded_file($_FILES["2303_bir"]["tmp_name"], $user_upload_dir . "/" . $_FILES["2303_bir"]["name"]);

            $success = "Profile updated successfully!";
        } else {
            $error = "Error updating profile.";
        }

        // Close statement
        mysqli_stmt_close($stmt);
    }
}   

?>
<html>

<head>
    <title>PESO Job Portal - Edit Profile</title>
    <link rel="stylesheet" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
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
        <h1>Edit Profile</h1>
        <?php if (isset($success)) : ?>
        <div class="alert alert-success" role="alert">
            <?php echo $success; ?>
        </div>
        <?php endif; ?>
        <?php if (isset($error)) : ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
            enctype="multipart/form-data">
            <!-- display user type, First Name, Middle Name, Last Name, Suffix, Email -->
            <div class="form-group">
                <label for="user_type">User Type</label>
                <select class="form-control" disabled>
                    <option value="Applicant" <?php echo ($user_type === 'applicant') ? 'selected' : ''; ?>>Applicant
                    </option>
                    <option value="Company" <?php echo ($user_type === 'company') ? 'selected' : ''; ?>>Company</option>
                </select>
            </div><br>


            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['fname']); ?>" disabled>
            </div><br>

            <div class="form-group">
                <label for="middle_name">Middle Name</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['mname']); ?>" disabled>
            </div><br>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['lname']); ?>" disabled>
            </div><br>

            <div class="form-group">
                <label for="suffix">Suffix</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['suffix']); ?>"
                    disabled>
            </div><br>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>" disabled>
            </div>

            <div class="form-text">To Request for Change of Name, Email Adress, please email us at
                jpinquiry@muntinlupa.site</div><br>

            <!-- display editable fields -->
            <div class="form-group">
                <label for="sex">Gender</label>
                <select name="gender" class="form-control" required>
                    <option value="">-- Please Select --</option>
                    <option value="male" <?php echo ($gender == 'male') ? 'selected' : ''; ?>>Male</option>
                    <option value="female" <?php echo ($gender == 'female') ? 'selected' : ''; ?>>Female</option>
                </select>
            </div><br>
            <div class="form-group">
                <label for="birth_day">Birth Day</label>
                <input type="text" name="birth_day" class="form-control" value="<?php echo $birth_day; ?>" required>
            </div><br>
            <div class="form-group">
                <label for="birth_month">Birth Month</label>
                <div class="form-text">Please use numbers for month (e.g. 1 for January, 2 for February, etc.)</div>
                <input type="text" name="birth_month" class="form-control" value="<?php echo $birth_month; ?>" required>
            </div><br>
            <div class="form-group">
                <label for="birth_year">Birth Year</label>
                <input type="text" name="birth_year" class="form-control" value="<?php echo $birth_year; ?>" required>
            </div><br>
            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input type="text" name="contact_number" class="form-control" value="<?php echo $contact_number; ?>"
                    required>
            </div><br>
            <div class="form-group">
                <label for="house_number">House Number</label>
                <input type="text" name="house_number" class="form-control" value="<?php echo $house_number; ?>"
                    required>
            </div><br>
            <div class="form-group">
                <label for="street">Street</label>
                <input type="text" name="street" class="form-control" value="<?php echo $street; ?>" required>
            </div><br>
            <div class="form-group">
                <label for="subdivision">Subdivision/Suburb</label>
                <input type="text" name="subdivision" class="form-control" value="<?php echo $subdivision; ?>" required>
            </div><br>
            <div class="form-group">
                <label for="barangay">Barangay</label>
                <input type="text" name="barangay" class="form-control" value="<?php echo $barangay; ?>" required>
            </div><br>
            <div class="form-group">
                <label for="city">City</label>
                <input type="text" name="city" class="form-control" value="<?php echo $city; ?>" required>
            </div><br>
            <div class="form-group">
                <label for="province">Province</label>
                <input type="text" name="province" class="form-control" value="<?php echo $province; ?>" required>
            </div><br>
            <div class="form-group">
                <label for="zip_code">Zip Code</label>
                <input type="text" name="zip_code" class="form-control" value="<?php echo $zip_code; ?>" required>
            </div><br>
            <div class="form-group">
                <label for="school_name">Last Finsihed School Name</label>
                <input type="text" name="school_name" class="form-control" value="<?php echo $school_name; ?>">
            </div><br>
            <div class="form-group">
                <label for="school_year_begin">School Year Begin</label>
                <input type="text" name="school_year_begin" class="form-control"
                    value="<?php echo $school_year_begin; ?>">
            </div><br>
            <div class="form-group">
                <label for="school_year_end">School Year End</label>
                <input type="text" name="school_year_end" class="form-control" value="<?php echo $school_year_end; ?>">
            </div><br>
            <div class="form-group">
                <label for="technicalschool_name">Technical School Name</label>
                <input type="text" name="technicalschool_name" class="form-control"
                    value="<?php echo $technicalschool_name; ?>">
            </div><br>
            <?php if ($user_type == 'applicant') : ?>
            <div class="form-group">
                <label for="nsrp_form">NSRP Form (PDF or DOCX only)</label>
                <div class="form-text">No NSRP Form? <a href="admin/forms/NSRP-Form.pdf" target="_blank">Click here
                        to obtain one</a>.</div>
                <input type="file" name="nsrp_form" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="biodata_form">Biodata Form (PDF or DOCX only)</label>
                <input type="file" name="biodata_form" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <?php endif; ?>

            <div class="form-group">
                <label for="profile_picture">Profile Picture (PNG format only)</label>
                <input type="file" name="profile_picture" class="form-control" accept="image/png">
            </div><br>

            <!-- loi, cp, sec_accredit, cda_license, dole_license, loc, mbpermit, job_vacant, job_solicitation, phjobnet_reg, cert_nopendingcase, cert_regSSS, cert_regPhHealth, cert_regPGIBG, setch_map, 2303_bir document upload as company -->
            <?php if ($user_type == 'company') : ?>
            <div class="form-group">
                <label for="loi">Letter of Intent (PDF or DOCX only)</label>
                <input type="file" name="loi" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="cp">Company Profile (PDF or DOCX only)</label>
                <input type="file" name="cp" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="sec_accredit">SEC Accreditation (PDF or DOCX only)</label>
                <input type="file" name="sec_accredit" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="cda_license">CDA License (PDF or DOCX only)</label>
                <input type="file" name="cda_license" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="dole_license">DOLE License (PDF or DOCX only)</label>
                <input type="file" name="dole_license" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="loc">Location Map (PDF or DOCX only)</label>
                <input type="file" name="loc" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="mbpermit">Mayor's Business Permit (PDF or DOCX only)</label>
                <input type="file" name="mbpermit" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="job_vacant">Job Vacant (PDF or DOCX only)</label>
                <input type="file" name="job_vacant" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="job_solicitation">Job Solicitation (PDF or DOCX only)</label>
                <input type="file" name="job_solicitation" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="phjobnet_reg">PhilJobNet Registration (PDF or DOCX only)</label>
                <input type="file" name="phjobnet_reg" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="cert_nopendingcase">Certificate of No Pending Case (PDF or DOCX only)</label>
                <input type="file" name="cert_nopendingcase" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="cert_regSSS">Certificate of Registration with SSS (PDF or DOCX only)</label>
                <input type="file" name="cert_regSSS" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="">Certificate of Registration with PhilHealth (PDF or DOCX only)</label>
                <input type="file" name="cert_regPhHealth" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="">Certificate of Registration with Pag-IBIG (PDF or DOCX only)</label>
                <input type="file" name="cert_regPGIBG" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="">Sketch Map (PDF or DOCX only)</label>
                <input type="file" name="sketch_map" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <div class="form-group">
                <label for="">BIR Form 2303 (PDF or DOCX only)</label>
                <input type="file" name="2303_bir" class="form-control" accept="application/pdf,.docx">
            </div><br>
            <?php endif; ?>
            <!-- submit button -->
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Update Profile">
        </form>

    </div>
</body>

</html>