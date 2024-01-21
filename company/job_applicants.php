<?php

//require_once "config.php";

session_start();

// Database connection parameters
$host = "localhost";
$username = "root";
$password = "";
$database = "peso_db";

// Connect to MySQL
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the application ID from the URL parameter
$users = isset($_GET['user_id']) ? $_GET['user_id'] : null;

// Check if the parameter is set
if ($users === null) {
    echo "Error: Missing 'user_id' parameter in the URL";
    // You may want to handle this case appropriately (e.g., redirect to an error page)
    exit();
}

// Update the status of the job application to 'accepted'
$sql = "UPDATE users SET status = 'accepted' WHERE id = $user_id";

if ($conn->query($sql) === TRUE) {
    echo "Applicant accepted successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close the database connection
$conn->close();

?>



<html>

<head>
    <title>PESO Muntinlupa - Job Portal</title>
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
    <style>
    .cover {
        /* use 1350x300px image */
        background-image: url('img/test_cover2.png');
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
</head>
<body>
<a href="job_applications.php?user_id=1">Accept Applicant</a>
</body>
</html>
