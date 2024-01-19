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


if (isset($_GET["user_id"]) && !empty($_GET["user_id"])) {
    $profile_user_id = $_GET["user_id"];
} else {
    $profile_user_id = "";
}

// get user data from user id
$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

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
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</body>

</html>