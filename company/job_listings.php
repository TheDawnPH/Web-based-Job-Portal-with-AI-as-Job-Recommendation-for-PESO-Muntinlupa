<?php
session_start();

require_once "../config.php";

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] !== "company") {
    header("location: 404.php");
    exit;
}

// Establish a PDO connection
try {
    $conn = new PDO("mysql:host=localhost;dbname=peso_db", "root", "");
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Handle add job form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_job'])) {
    $userId = $_POST['user_id'];
    $jobTitle = $_POST['job_title'];
    $jobDescription = $_POST['job_description'];
    $jobSalary = $_POST['job_salary'];
    $imageName = $_POST['image_name'];
    $jinindustryId = $_POST['jinindustry_id'];
    $joccId = $_POST['jocc_id'];
    $shsQualified = $_POST['shs_qualified'];
    $jobPosted = $_POST['job_posted'];
    $createdAt = $_POST['created_at'];

    try {
        $insertSql = "INSERT INTO job_listings (user_id, job_title, job_description, job_salary, image_name, jinindustry_id, jocc_id, shs_qualified, job_posted, created_at) 
                      VALUES (:userId, :jobTitle, :jobDescription, :jobSalary, :imageName, :jinindustryId, :joccId, :shsQualified, :jobPosted, :createdAt)";

        $stmt = $conn->prepare($insertSql);
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':jobTitle', $jobTitle);
        $stmt->bindParam(':jobDescription', $jobDescription);
        $stmt->bindParam(':jobSalary', $jobSalary);
        $stmt->bindParam(':imageName', $imageName);
        $stmt->bindParam(':jinindustryId', $jinindustryId);
        $stmt->bindParam(':joccId', $joccId);
        $stmt->bindParam(':shsQualified', $shsQualified);
        $stmt->bindParam(':jobPosted', $jobPosted);
        $stmt->bindParam(':createdAt', $createdAt);

        $stmt->execute();

        echo "Job listing added successfully";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Handle delete job action
if (isset($_GET['delete_job']) && $_GET['delete_job'] == 1) {
    $jobId = $_GET["job_id"];

    try {
        $deleteSql = "DELETE FROM job_listings WHERE id = :jobId";
        $stmt = $conn->prepare($deleteSql);
        $stmt->bindParam(':jobId', $jobId);
        $stmt->execute();

        echo "Job listing deleted successfully";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Handle update job form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_job'])) {
    $jobId = $_POST['job_id'];
    $userId = $_POST['user_id'];
    $jobTitle = $_POST['job_title'];
    $jobDescription = $_POST['job_description'];
    $jobSalary = $_POST['job_salary'];
    $imageName = $_POST['image_name'];
    $jinindustryId = $_POST['jinindustry_id'];
    $joccId = $_POST['jocc_id'];
    $shsQualified = $_POST['shs_qualified'];
    $jobPosted = $_POST['job_posted'];
    $createdAt = $_POST['created_at'];

    try {
        $updateSql = "UPDATE job_listing 
                      SET job_title = :jobTitle, job_description = :jobDescription, job_salary = :jobSalary, image_name = :imageName, jinindustry_id = :jinindustryId, jocc_id = :joccId, shs_qualified = :shsQualified, job_posted = :jobPosted, created_at = :createdAt
                      WHERE id = :jobId";

        $stmt = $conn->prepare($updateSql);
        $stmt->bindParam(':jobTitle', $jobTitle);
        $stmt->bindParam(':jobDescription', $jobDescription);
        $stmt->bindParam(':jobSalary', $jobSalary);
        $stmt->bindParam(':imageName', $imageName);
        $stmt->bindParam(':jinindustryId', $jinindustryId);
        $stmt->bindParam(':joccId', $joccId);
        $stmt->bindParam(':shsQualified', $shsQualified);
        $stmt->bindParam(':jobPosted', $jobPosted);
        $stmt->bindParam(':createdAt', $createdAt);
        $stmt->bindParam(':jobId', $jobId);

        $stmt->execute();

        echo "Job listing updated successfully";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Retrieve all job listings
try {
    $selectSql = "SELECT * FROM job_listings";
    $result = $conn->query($selectSql);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

?>

<html>

<head>
    <title>Company - Manage Job Listings</title>
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
        if ($result->rowCount() > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>Job ID</th>
                        <th>Job Title</th>
                        <th>Job Description</th>
                        <th>Job Salary</th>
                        <th>Action</th>
                    </tr>";

            foreach ($result as $row) {
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
// Close the PDO connection
$conn = null;
?>
