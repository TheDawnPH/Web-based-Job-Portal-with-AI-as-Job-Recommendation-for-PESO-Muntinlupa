<?php

// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

$root = $_SERVER['DOCUMENT_ROOT'];

require $root . "/config.php";

// if user is not logged in redirect to login page
if (!isset($_SESSION["user_type"]) || empty($_SESSION["user_type"])) {
    $_SESSION['current_page'] = $_SERVER['REQUEST_URI'];
    header("location: /login.php");
    exit;
}

// check if user_type is admin, if not redirect to 404 page
if ($_SESSION["user_type"] != "admin") {
    header("location: /404.php");
    exit;
}

// get all job listings
$sql = "SELECT * FROM job_listing";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error executing query: " . mysqli_error($conn));
}

$row = mysqli_fetch_assoc($result);

?>
<html>

<head>
    <title>Admin - Job Listings Repository</title>
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
    <script>
        function confirmAction_delete() {
            return confirm("Are you sure to delete this listing?");
        }
    </script>
</head>

<body>
    <?php include $root . '/nav.php'; ?>
    <div class="container">
        <h1>All Job Listings</h1>
        <div class="table-responsive">
            <!-- display all job listing from session user_id and add actions such as view, edit and delete -->
            <table class="table table-striped table-bordered border-start">
                <thead>
                    <tr>
                        <th scope="col">Job Title</th>
                        <th scope="col">Job Category</th>
                        <th scope="col">Job Type</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        do {
                            $sql2 = "SELECT * FROM jinindustry WHERE jinindustry_id = ?";
                            $stmt = mysqli_prepare($conn, $sql2);
                            mysqli_stmt_bind_param($stmt, "i", $row["jinindustry_id"]);
                            if (!mysqli_stmt_execute($stmt)) {
                                die("Error executing query: " . mysqli_error($conn));
                            }
                            $result2 = mysqli_stmt_get_result($stmt);
                            $row2 = mysqli_fetch_assoc($result2);
                    ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row["job_title"]); ?></td>
                                <td><?php echo htmlspecialchars($row2["jinindustry_name"]); ?></td>
                                <td><?php echo htmlspecialchars($row["job_type"]); ?></td>
                                <td>
                                    <a href="/job_details.php?job_id=<?php echo htmlspecialchars($row["id"]); ?>" class="btn btn-primary">View</a>
                                    <a href="/company/add_job_applications.php?id=<?php echo htmlspecialchars($row["id"]); ?>" class="btn btn-warning">Edit</a>
                                    <a href="/company/delete_job_listing.php?id=<?php echo htmlspecialchars($row["id"]); ?>" onclick="return confirmAction();" class="btn btn-danger">Delete</a>
                                </td>
                            </tr>
                    <?php
                        } while ($row = mysqli_fetch_assoc($result));
                    } else {
                        echo "<tr><td colspan='4'>No job listings found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>