<?php
session_start();

$root = $_SERVER['DOCUMENT_ROOT'];

require $root . "/config.php";

// check if user_type is admin, if not redirect to 404 page
if ($_SESSION["user_type"] != "admin") {
    header("location: 404.php");
    exit;
}

// Retrieve data from the users table sort alphabetically by last name
$sql = "SELECT * FROM users ORDER BY lname ASC";
$result = mysqli_query($conn, $sql);

?>

<html>

<head>
    <title>Admin - Requests Portal</title>
    <link rel="stylesheet" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" href="/img/peso_muntinlupa.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
</head>

<body>
    <?php include $root . '/nav.php'; ?>
    <div class="container">
        <h1>Users</h1>
        // table of Users
        <?php
            // Check if there are rows returned
if (mysqli_num_rows($result) > 0) {
    echo "<table border='1'>
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
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['fname'] . "</td>";
        echo "<td>" . $row['lname'] . "</td>";
        echo "<td>" . $row['user_type'] . "</td>";
        echo "<td><a href='/profile.php?user_id=" . $row['user_id'] . "'>View</a> <a href='/delete_profile.php?user_id=" . $row['user_id'] . "'>Delete Profile</a></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No users found.";
}

// Close the database connection
mysqli_close($conn);
?>
    </div>
</body>

</html>