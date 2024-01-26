<?php

// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

$root = $_SERVER['DOCUMENT_ROOT'];

require $root . "/config.php";

// check if user_type is admin, if not redirect to 404 page
if ($_SESSION["user_type"] != "admin") {
    header("location: /404.php");
    exit;
}

// if user is not logged in redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: /login.php");
    exit;
}

// Retrieve data from the users table sort alphabetically by last name
$sql = "SELECT * FROM users ORDER BY lname ASC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error executing query: " . mysqli_error($conn));
}

?>

<html>

<head>
    <title>Admin - Users</title>
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
        <br>
        <h1>Users</h1>
        <a class="btn btn-primary" role="button" href="/admin/add_users.php">Add User</a>
        <br><br>
        <div class="table-responsive">
            <?php
            // Check if there are rows returned
            if (mysqli_num_rows($result) > 0) {
                echo "<table class='table table-striped table-bordered border-start'>
            <tr>
                <th>User ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>User Type</th>
                <th>Action</th>
            </tr>";
            
                // Loop through each row returned by the query
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['fname']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['lname']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['user_type']) . "</td>";
                    echo "<td><a class='btn btn-secondary' role='button' href='/profile.php?user_id=" . htmlspecialchars($row['user_id']) . "'>View Profile</a> <a class='btn btn-danger' role='button' href='/admin/delete_user.php?user_id=" . htmlspecialchars($row['user_id']) . "'>Delete User</a></td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<div class='alert alert-danger' role='alert'>No users found.</div>";
            }
            
            // Close the database connection
            mysqli_close($conn);
            ?>
        </div>
    </div>
</body>

</html>