<?php
session_start();

require_once __DIR__ . "./htdocs/project/config.php"; // Adjust the path based on the location of your config.php file

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "admin") {
    header("location: 404.php");
    exit;
}

// Handle add user form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password

    $insertSql = "INSERT INTO users (fname, lname, email, password, user_type) 
                  VALUES ('$fname', '$lname', '$email', '$password', 'user')";

    if (mysqli_query($conn, $insertSql)) {
        echo "User added successfully";
    } else {
        echo "Error: " . $insertSql . "<br>" . mysqli_error($conn);
    }
}

// Handle delete user action
if (isset($_GET['delete_user']) && $_GET['delete_user'] == 1) {
    $user_id = mysqli_real_escape_string($conn, $_GET["user_id"]);

    $deleteSql = "DELETE FROM users WHERE user_id = '$user_id'";
    if (mysqli_query($conn, $deleteSql)) {
        echo "User deleted successfully";
    } else {
        echo "Error: " . $deleteSql . "<br>" . mysqli_error($conn);
    }
}

// Handle update user form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $updateSql = "UPDATE users SET fname = '$fname', lname = '$lname', email = '$email' WHERE user_id = '$user_id'";
    if (mysqli_query($conn, $updateSql)) {
        echo "User updated successfully";
    } else {
        echo "Error: " . $updateSql . "<br>" . mysqli_error($conn);
    }
}

// Retrieve all users
$selectSql = "SELECT * FROM users";
$result = mysqli_query($conn, $selectSql);

?>

<html>

<head>
    <title>Admin - Manage Users</title>
    <!-- Include necessary stylesheets or scripts -->
</head>

<body>
    <?php include('nav.php'); ?>
    <div class="container">
        <h1>Manage Users</h1>

        <!-- Add User Form -->
        <h2>Add User</h2>
        <form method="post">
            <label for="fname">First Name:</label>
            <input type="text" name="fname" required>
            <label for="lname">Last Name:</label>
            <input type="text" name="lname" required>
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            <button type="submit" name="add_user">Add User</button>
        </form>

        <!-- List of Users -->
        <h2>Users</h2>
        <?php
        if (mysqli_num_rows($result) > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>User ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Action</th>
                    </tr>";

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$row['user_id']}</td>
                        <td>{$row['fname']}</td>
                        <td>{$row['lname']}</td>
                        <td>{$row['email']}</td>
                        <td>
                            <a href='?user_id={$row['user_id']}&delete_user=1'>Delete</a>
                            <a href='edit_user.php?user_id={$row['user_id']}'>Edit</a>
                        </td>
                      </tr>";
            }

            echo "</table>";
        } else {
            echo "No records found.";
        }
        ?>

    </div>
</body>

</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
