<?php
    session_start();
    require $root . "/config.php";
// Establish a PDO connection (Update credentials based on your database)
try {
    $conn = new PDO("mysql:host=localhost;dbname=peso_db", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Fetch all users from the database
$sql = "SELECT * FROM users";
$result = $conn->query($sql);

// Check if there are any users
if ($result->rowCount() > 0) {
    // Display the users in a table
    echo "<table border='1'>";
    echo "<tr><th>User ID</th><th>Name</th><th>Email</th><th>User Type</th><th>Action</th></tr>";

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['fname'] . " " . $row['lname'] . "</td>";
        echo "<td>" . $row['email'] . "</td>";
        echo "<td>" . $row['user_type'] . "</td>";
        echo "<td>";
        
        // Link to view profile
        echo "<a href='view_profile.php?user_id=" . $row['user_id'] . "'>View</a> | ";
        
        // Link to delete user 
        echo "<a href='delete_profile.php?user_id=" . $row['user_id'] . "' onclick='return confirm(\"Are you sure?\")'>Delete</a>";
        
        echo "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No users found.";
}



// Close the database connection
$conn = null;
?>



<html>

<head>
    <title>PESO Job Portal - User's List</title>
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
    <!-- Button to redirect to create user page -->
    <a href="/profile.php">
        <button>Create User</button>
    </a>
</body>
</html>
