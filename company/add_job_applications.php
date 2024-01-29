<?php
// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

$root = $_SERVER['DOCUMENT_ROOT'];

require $root . "/config.php";

if (isset($_GET["id"]) && !empty($_GET["id"])) {
    $job_id = $_GET["id"];
} else {
    $job_id = "";
}

// if user is not logged in redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /login.php");
    exit;
}

// if user is applicant show 404.php
if ($_SESSION["user_type"] == "applicant") {
    header("location: /404.php");
    exit;
}

// if user is not company_verified show error variable
if ($_SESSION["company_verified"] == 0) {
    $errortitle = "Verification Error";
    $errordesc = "Your company is not yet verified. Please verify first by clicking the Request Verification below";
    $verifylink = "<a href='/company/request_company_verification.php' class='btn btn-primary'>Request Verification</a>";
}

// Fetch job listing details from $job_id
$sql2 = "SELECT * FROM job_listing WHERE id = ?";
$stmt2 = mysqli_prepare($conn, $sql2);
mysqli_stmt_bind_param($stmt2, "i", $job_id);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$row2 = mysqli_fetch_assoc($result2);

// define variables and initialize with values from row2
if (mysqli_num_rows($result2) == 1) {
    $job_title = $row2['job_title'];
    $job_description = $row2['job_description'];
    $job_requirements = $row2['job_requirements'];
    $job_salary = $row2['job_salary'];
    $job_type = $row2['job_type'];
    $jinindustry = $row2['jinindustry_id'];
    $shs_qualified = $row2['shs_qualified'];
    $checked = ($shs_qualified == 1) ? 'checked' : '';
} else {
    $job_title = "";
    $job_description = "";
    $job_requirements = "";
    $job_salary = "";
    $job_type = "";
    $jinindustry = "";
    $shs_qualified = "";
    $checked = "";
}

// process form data when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $job_title = $_POST["job_title"];
    $job_description = $_POST["job_description"];
    $job_requirements = $_POST["job_requirements"];
    $job_salary = $_POST["job_salary"];
    $job_type = $_POST["job_type"];
    $jinindustry = $_POST["jinindustry"];
    $shs_qualified = !empty($_POST["shs_qualified"]) ? $_POST["shs_qualified"] : 0;
    $job_image = !empty($_FILES["job_image"]["name"]) ? $_FILES["job_image"]["name"] : NULL;
    $user_id = $_SESSION["user_id"];
    $submit_job_id = $_POST["submit_id"];

    // prepare update statement if submitted with job_id
    if ($submit_job_id != $job_id) {
        $sql = "UPDATE job_listing SET job_title = ?, job_description = ?, job_requirements = ?, job_salary = ?, job_type = ?, image_name = ?, jinindustry_id = ?, shs_qualified = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssi", $job_title, $job_description, $job_requirements, $job_salary, $job_type, $job_image, $jinindustry, $shs_qualified, $submit_job_id);
    } else {
        // prepare an insert statement
        $sql = "INSERT INTO job_listing (job_title, job_description, job_requirements, job_salary, job_type, image_name, jinindustry_id, shs_qualified, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssss", $job_title, $job_description, $job_requirements, $job_salary, $job_type, $job_image, $jinindustry, $shs_qualified, $user_id);
    }

    // attempt to execute the prepared statement
    if (mysqli_stmt_execute($stmt)) {
        if ($submit_job_id != $job_id) {
            // Update: create upload directory if not exists and move uploaded file
            $upload_dir = "uploads/" . $submit_job_id . "/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (!empty($_FILES["job_image"]["name"])) {
                move_uploaded_file($_FILES["job_image"]["tmp_name"], $upload_dir . "/" . $_FILES["job_image"]["name"]);
            }
            header("location: add_job_applications.php?update=1");
        } else {
            // Insert: create upload directory if not exists and move uploaded file
            $get_job_id = mysqli_insert_id($conn);
            $upload_dir = "uploads/" . $get_job_id . "/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            if (!empty($_FILES["job_image"]["name"])) {
                move_uploaded_file($_FILES["job_image"]["tmp_name"], $upload_dir . "/" . $_FILES["job_image"]["name"]);
            }
            header("location: add_job_applications.php?success=1");
        }
    } else {
        // redirect to page with error message
        header("location: add_job_applications.php?error=1");
    }

    // close statement
    mysqli_stmt_close($stmt);

    // close connection
    mysqli_close($conn);
}
?>

<html>

<head>
    <title>PESO Job Portal - Add/Update Job Listing</title>
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
    <?php include $root . '/nav.php'; ?>
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <br>
                <?php
                if (isset($errortitle)) {
                    echo "<div class='alert alert-danger' role='alert'>
                    <h4 class='alert-heading'>$errortitle</h4>
                    <p>$errordesc</p>
                    $verifylink
                    </div>";
                }
                ?>
                <?php if (isset($_GET["success"])) {
                    echo "<div class='alert alert-success' role='alert'>
                    <h4 class='alert-heading'>Success!</h4>
                    <p>Job Listing has been added successfully.</p>
                    </div>";
                } ?>
                <?php if (isset($_GET["update"])) {
                    echo "<div class='alert alert-success' role='alert'>
                    <h4 class='alert-heading'>Success!</h4>
                    <p>Job Listing has been updated successfully.</p>
                    </div>";
                } ?>
                <?php if (isset($_GET["error"])) {
                    echo "<div class='alert alert-danger' role='alert'>
                    <h4 class='alert-heading'>Error!</h4>
                    <p>Something went wrong. Please try again.</p>
                    </div>";
                } ?>

                <?php if ($_SESSION["company_verified"] == 1) { ?>
                    <h1>Add Job Listing</h1>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="job_title" class="form-label">Job Title</label>
                            <input type="text" class="form-control" id="job_title" name="job_title" value="<?php echo $job_title ?>" placeholder="Sample Title" required>
                        </div>
                        <div class="mb-3">
                            <label for="job_description" class="form-label">Job Description</label>
                            <textarea class="form-control" id="job_description" name="job_description" rows="3" required><?php echo $job_description ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="job_requirements" class="form-label">Job Requirements</label>
                            <textarea class="form-control" id="job_requirements" name="job_requirements" rows="3" required><?php echo $job_requirements ?></textarea>
                            <div class="form-text">Please use comma to seperate requirements details.</div>
                        </div>
                        <div class="mb-3">
                            <label for="job_salary" class="form-label">Job Salary</label>
                            <input type="number" class="form-control" id="job_salary" name="job_salary" value="<?php echo $job_salary ?>" placeholder="15000" required>
                        </div>
                        <div class="mb-3">
                            <label for="job_type" class="form-label">Job Type</label>
                            <select class="form-select" aria-label="Default select example" id="job_type" name="job_type" required>
                                <option value="Full Time" <?php echo ($job_type === 'Full Time') ? 'selected' : ''; ?>>Full Time</option>
                                <option value="Part Time" <?php echo ($job_type === 'Part Time') ? 'selected' : ''; ?>>Part Time</option>
                                <option value="Contractual" <?php echo ($job_type === 'Contractual') ? 'selected' : ''; ?>>Contractual</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="jinindustry" class="form-label">Job Industry</label>
                            <select class="form-select" aria-label="Default select example" id="jinindustry" name="jinindustry" required>
                                <?php
                                $sql = "SELECT * FROM jinindustry";
                                $result = mysqli_query($conn, $sql);
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $selected = ($row['jinindustry_id'] == $row2['jinindustry_id']) ? 'selected' : '';
                                    echo "<option value='" . $row['jinindustry_id'] . "' " . $selected . ">" . $row['jinindustry_name'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <input type="checkbox" id="shs_qualified" name="shs_qualified" value="1" <?php echo $checked; ?>>
                            <label for="shs_qualified" class="form-label">SHS Applicants can apply?</label>
                        </div>
                        <div class="mb-3">
                            <label for="job_image" class="form-label">Job Image</label>
                            <input class="form-control" type="file" id="job_image" name="job_image" accept="image/png">
                        </div>
                        <input hidden type="text" name="submit_id" value="<?php echo $job_id ?>">
                        <button type="submit" class="btn btn-primary">Add/Update Job Listing</button>
                    </form>
                <?php } ?>
            </div>
        </div>
    </div>
</body>

</html>