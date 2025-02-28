<?php
// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

$root = $_SERVER['DOCUMENT_ROOT'];

require $root . "/config.php";

$job_id = isset($_GET["id"]) && !empty($_GET["id"]) ? intval($_GET["id"]) : null;

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

// if user is not company_verified show error variable
if ($cverify["company_verified"] == 0) {
    $errortitle = "Verification Error";
    $errordesc = "Your company is not yet verified. Please verify first by clicking the Request Verification below";
    $verifylink = "<a href='/company/request_company_verification.php' class='btn btn-primary'>Request Verification</a>";
}

// Fetch job listing details from $job_id
if ($job_id) {
    $sql2 = "SELECT * FROM job_listing WHERE id = ?";
    $stmt2 = mysqli_prepare($conn, $sql2);

    if (!$stmt2) {
        die("MySQL prepare error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt2, "i", $job_id);
    mysqli_stmt_execute($stmt2);
    $result2 = mysqli_stmt_get_result($stmt2);
    $row2 = mysqli_fetch_assoc($result2);

    if (mysqli_num_rows($result2) == 1) {
        $job_title = $row2['job_title'];
        $job_description = $row2['job_description'];
        $job_requirements = $row2['job_requirements'];
        $job_salary = $row2['job_salary'];
        $show_salary = $row2['show_salary'];
        $job_type = $row2['job_type'];
        $jinindustry = $row2['jinindustry_id'];
        $shs_qualified = $row2['shs_qualified'];
        $checked = ($shs_qualified == 1) ? 'checked' : '';
    } else {
        $job_title = "";
        $job_description = "";
        $job_requirements = "";
        $job_salary = "";
        $show_salary = "";
        $job_type = "";
        $jinindustry = "";
        $shs_qualified = "";
        $checked = "";
    }
} else {
    $job_title = "";
    $job_description = "";
    $job_requirements = "";
    $job_salary = "";
    $show_salary = "";
    $job_type = "";
    $jinindustry = "";
    $shs_qualified = "";
    $checked = "";
}

// process form data when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!empty($_FILES["job_image"]["name"])) {
        $image_name = basename($_FILES["job_image"]["name"]);
        $image_name_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        if (!in_array($image_name_ext, ["png", "jpg", "jpeg"])) {
            $error = "Image must be a PNG, JPG, or JPEG file.";
        } elseif ($_FILES["job_image"]["error"] !== UPLOAD_ERR_OK) {
            $error = "Error uploading image.";
        }
    }

    $job_title = htmlspecialchars($_POST["job_title"], ENT_QUOTES);
    $job_description = htmlspecialchars($_POST["job_description"], ENT_QUOTES);
    $job_requirements = htmlspecialchars($_POST["job_requirements"], ENT_QUOTES);
    $job_salary = htmlspecialchars($_POST["job_salary"], ENT_QUOTES);
    $show_salary = !empty($_POST["show_salary"]) ? 1 : 0;
    $job_type = htmlspecialchars($_POST["job_type"], ENT_QUOTES);
    $jinindustry = intval($_POST["jinindustry"]);
    $shs_qualified = !empty($_POST["shs_qualified"]) ? 1 : 0;
    $job_image = !empty($image_name) ? $image_name : NULL;
    $user_id = $_SESSION["user_id"];
    $submit_job_id = intval($_POST["submit_id"]);
    $update_visible = 1;

    if ($submit_job_id && $submit_job_id !== $job_id) {
        $sql = "UPDATE job_listing SET job_title = ?, job_description = ?, job_requirements = ?, job_salary = ?, job_type = ?, image_name = IFNULL(?, image_name), jinindustry_id = ?, shs_qualified = ?, visible = ?, updated_at = NOW(), show_salary = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            die("MySQL prepare error: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ssssssssssi", $job_title, $job_description, $job_requirements, $job_salary, $job_type, $job_image, $jinindustry, $shs_qualified, $update_visible, $show_salary, $submit_job_id);
    } else {
        $sql = "INSERT INTO job_listing (job_title, job_description, job_requirements, job_salary, job_type, image_name, jinindustry_id, shs_qualified, show_salary, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {
            die("MySQL prepare error: " . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, "ssssssssss", $job_title, $job_description, $job_requirements, $job_salary, $job_type, $job_image, $jinindustry, $shs_qualified, $show_salary, $user_id);
    }

    if (mysqli_stmt_execute($stmt) && empty($error)) {
        $upload_dir = "uploads/" . ($submit_job_id ? $submit_job_id : mysqli_insert_id($conn)) . "/";

        if (!is_dir($upload_dir) && !mkdir($upload_dir, 0775, true) && !is_writable($upload_dir)) {
            die("Failed to create or access upload directory.");
        }

        if (!empty($_FILES["job_image"]["name"])) {
            $target_path = $upload_dir . $image_name;
            if (!move_uploaded_file($_FILES["job_image"]["tmp_name"], $target_path)) {
                die("Failed to move uploaded file.");
            }
        }

        header("location: add_job_applications.php?" . ($submit_job_id ? "update=1" : "success=1"));
    } else {
        header("location: add_job_applications.php?error=1");
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>


<html>

<head>
    <title>Add/Update Job Listing - Company</title>
    <link rel="stylesheet" href="/css/index.css">
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
                <?php if ($cverify["company_verified"] == 1) { ?>
                    <?php if (isset($error)) {
                        echo "<div class='alert alert-danger' role='alert'>
                    <h4 class='alert-sheading'>Error!</h4>
                    <p>$error</p>
                    </div>";
                    } ?>
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
                    <h1>Add Job Listing</h1>
                    <img src="https://muntinlupacity.gov.ph/wp-content/uploads/2022/10/line_blue_yellow_red-scaled.jpg" class="img-fluid" alt="Responsive image">
                    <br><br>
                    <form action="<?php echo htmlentities(htmlspecialchars($_SERVER["PHP_SELF"]), ENT_QUOTES); ?>" method="POST" enctype="multipart/form-data">
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
                                <input type="checkbox" id="show_salary" name="show_salary" value="0" <?php echo ($show_salary == 0) ? 'checked' : ''; ?>>
                                <label for="show_salary" class="form-label">Hide Salary to Applicants?</label>
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
                <?php } else { ?>
                <?php
                    if (isset($errortitle)) {
                        echo "<div class='alert alert-danger' role='alert'>
                    <h4 class='alert-heading'>$errortitle</h4>
                    <p>$errordesc</p>
                    $verifylink
                    </div>";
                    }
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>