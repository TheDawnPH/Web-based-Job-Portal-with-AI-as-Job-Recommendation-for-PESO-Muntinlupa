<?php
session_start();

require_once "./config.php"; // Adjust the path based on the location of your config.php file

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "admin") {
    header("location: 404.php");
    exit;
}

// Handle add job form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_job'])) {
    $userId = mysqli_real_escape_string($conn, $_POST['user_id']);
    $jobTitle = mysqli_real_escape_string($conn, $_POST['job_title']);
    $jobDescription = mysqli_real_escape_string($conn, $_POST['job_description']);
    $jobSalary = mysqli_real_escape_string($conn, $_POST['job_salary']);
    $imageName = mysqli_real_escape_string($conn, $_POST['image_name']);
    $jinindustryId = mysqli_real_escape_string($conn, $_POST['jinindustry_id']);
    $joccId = mysqli_real_escape_string($conn, $_POST['jocc_id']);
    $shsQualified = mysqli_real_escape_string($conn, $_POST['shs_qualified']);
    $jobPosted = mysqli_real_escape_string($conn, $_POST['job_posted']);
    $createdAt = mysqli_real_escape_string($conn, $_POST['created_at']);







    $insertSql = "INSERT INTO job_listings (user_id, job_title, job_description, job_salary, image_name, jinindustry_id, jocc_id, shs_qualified, job_posted, created_at) 
                  VALUES ('$userId', '$jobTitle', '$jobDescription', '$jobSalary', '$imageName', '$jinindustryId', '$joccId', '$shsQualified', '$jobPosted', '$createdAt')";

    if (mysqli_query($conn, $insertSql)) {
        echo "Job listing added successfully";
    } else {
        echo "Error: " . $insertSql . "<br>" . mysqli_error($conn);
    }
}

// Handle delete job action
if (isset($_GET['delete_job']) && $_GET['delete_job'] == 1) {
    $jobId = mysqli_real_escape_string($conn, $_GET["job_id"]);

    $deleteSql = "DELETE FROM job_listings WHERE id = '$jobId'";
    if (mysqli_query($conn, $deleteSql)) {
        echo "Job listing deleted successfully";
    } else {
        echo "Error: " . $deleteSql . "<br>" . mysqli_error($conn);
    }
}

// Handle update job form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_job'])) {
    $jobId = mysqli_real_escape_string($conn, $_POST['job_id']);
    $userId = mysqli_real_escape_string($conn, $_POST['user_id']);
    $jobTitle = mysqli_real_escape_string($conn, $_POST['job_title']);
    $jobDescription = mysqli_real_escape_string($conn, $_POST['job_description']);
    $jobSalary = mysqli_real_escape_string($conn, $_POST['job_salary']);
    $imageName = mysqli_real_escape_string($conn, $_POST['image_name']);
    $jinindustryId = mysqli_real_escape_string($conn, $_POST['jinindustry_id']);
    $joccId = mysqli_real_escape_string($conn, $_POST['jocc_id']);
    $shsQualified = mysqli_real_escape_string($conn, $_POST['shs_qualified']);
    $jobPosted = mysqli_real_escape_string($conn, $_POST['job_posted']);
    $createdAt = mysqli_real_escape_string($conn, $_POST['created_at']);

    $updateSql = "UPDATE job_listings 
                  SET job_title = '$jobTitle', job_description = '$jobDescription', job_salary = '$jobSalary', image_name = '$imageName', jinindustry_id = '$jinindustryId', jocc_id = '$joccId', shs_qualified = '$shsQualified', job_posted = '$jobPosted', created_at = '$createdAt'
                  WHERE id = '$jobId'";

    if (mysqli_query($conn, $updateSql)) {
        echo "Job listing updated successfully";
    } else {
        echo "Error: " . $updateSql . "<br>" . mysqli_error($conn);
    }
}

// Retrieve all job listings
$selectSql = "SELECT * FROM job_listings";
$result = mysqli_query($conn, $selectSql);

?>

<html>

<head>
    <title>Admin - Manage Job Listings</title>
    <!-- Include necessary stylesheets or scripts -->
</head>

<body>
    <?php include('nav.php'); ?>
    <div class="container">
        <h1>Manage Job Listings</h1>

        <!-- Add Job Form -->
        <h2>Add Job Listing</h2>
        <form method="post">
            <label for="job_title">Job Title:</label>
            <input type="text" name="job_title" required>
            <label for="job_description">Job Description:</label>
            <textarea name="job_description" required></textarea>
            <label for="job_salary">Job Salary:</label>
            <input type="number" name="job_salary" required>
            <label for="image_name">Image Name:</label>
            <input type="number" name="image_name" required>
            <label for="jinindustry_id">Jinindustry ID:</label>
            <input type="number" name="jinindustry_id" required>
            <label for="jocc_id">Jocc ID:</label>
            <input type="number" name="jocc_id" required>
            <label for="shs_qualified">SHS Qualified:</label>
            <input type="number" name="shs_qualified" required>
            <label for="created_at">Created At:</label>
            <input type="number" name="created_at" required>
            <button type="submit" name="add_job">Add Job Listing</button>
        </form>

        <!-- List of Job Listings -->
        <h2>Job Listings</h2>
        <?php
        if (mysqli_num_rows($result) > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>Job ID</th>
                        <th>Job Title</th>
                        <th>Job Description</th>
                        <th>Job Salary</th>
                        <th>Action</th>
                    </tr>";

            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['job_title']}</td>
                        <td>{$row['job_description']}</td>
                        <td>{$row['job_salary']}</td>
                        <td>{$row['image_name']}</td>
                        <td>{$row['jinindustry_id']}</td>
                        <td>{$row['jocc_id']}</td>
                        <td>{$row['shs_qualified']}</td>
                        <td>{$row['created_at']}</td>
                        <td>
                            <a href='?job_id={$row['id']}&delete_job=1'>Delete</a>
                            <a href='edit_job.php?job_id={$row['id']}'>Edit</a>
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
