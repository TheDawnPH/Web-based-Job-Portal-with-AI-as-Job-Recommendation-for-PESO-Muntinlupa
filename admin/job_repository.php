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

?>
<html>

<head>
    <title>Job Listings Repository - Admin</title>
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

        // Function to search the table
        function searchTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("search");
            filter = input.value.toUpperCase();
            table = document.getElementsByTagName("table")[0];
            tr = table.getElementsByTagName("tr");
            for (i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
                tr[i].style.display = "none";
                tdCompany = tr[i].getElementsByTagName("td")[0];
                tdJobTitle = tr[i].getElementsByTagName("td")[1];
                if (tdCompany || tdJobTitle) {
                    txtValueCompany = tdCompany.textContent || tdCompany.innerText;
                    txtValueJobTitle = tdJobTitle.textContent || tdJobTitle.innerText;
                    if (txtValueCompany.toUpperCase().indexOf(filter) > -1 || txtValueJobTitle.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    }
                }
            }
            updateResultCount();
        }

        function updateResultCount() {
            var table, tr, count = 0;
            table = document.getElementsByTagName("table")[0];
            tr = table.getElementsByTagName("tr");
            for (var i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
                if (tr[i].style.display !== "none") {
                    count++;
                }
            }
            document.getElementById("count").innerHTML = count;
        }
    </script>
</head>

<body>
    <?php include $root . '/nav.php'; ?>
    <div class="container">
        <h1>All Job Listings</h1>
        <img src="https://muntinlupacity.gov.ph/wp-content/uploads/2022/10/line_blue_yellow_red-scaled.jpg" class="img-fluid" alt="Responsive image">
        <br><br>
        <!-- search bar for job listings use js to filter table -->
        <input type="text" id="search" onkeyup="searchTable()" placeholder="Search for Job title/Company.." class="form-control">
        <br>
        <p>Number of Results: <span id="count"></span></p>
        <div class="table-responsive">
            <!-- display all job listing from session user_id and add actions such as view, edit and delete -->
            <table class="table table-striped table-bordered border-start">
                <thead>
                    <tr>
                        <th scope="col">Company Name</th>
                        <th scope="col">Job Title</th>
                        <th scope="col">Job Category</th>
                        <th scope="col">Job Type</th>
                        <th scope="col">Visible on Listing</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // get company name from users who posted the job
                            $sql3 = "SELECT * FROM users WHERE user_id = ?";
                            $stmt3 = mysqli_prepare($conn, $sql3);
                            mysqli_stmt_bind_param($stmt3, "i", $row['user_id']);
                            if (!mysqli_stmt_execute($stmt3)) {
                                die("Error executing query: " . mysqli_error($conn));
                            }
                            $result3 = mysqli_stmt_get_result($stmt3);
                            $company = mysqli_fetch_assoc($result3);
                            mysqli_stmt_close($stmt3);

                            // get industry name
                            $sql2 = "SELECT * FROM jinindustry WHERE jinindustry_id = ?";
                            $stmt2 = mysqli_prepare($conn, $sql2);
                            mysqli_stmt_bind_param($stmt2, "i", $row["jinindustry_id"]);
                            if (!mysqli_stmt_execute($stmt2)) {
                                die("Error executing query: " . mysqli_error($conn));
                            }
                            $result2 = mysqli_stmt_get_result($stmt2);
                            $row2 = mysqli_fetch_assoc($result2);
                            mysqli_stmt_close($stmt2);
                    ?>
                            <tr>
                                <td><?php echo htmlspecialchars($company["company_name"]); ?></td>
                                <td><?php echo htmlspecialchars($row["job_title"]); ?></td>
                                <td><?php echo htmlspecialchars($row2["jinindustry_name"]); ?></td>
                                <td><?php echo htmlspecialchars($row["job_type"]); ?></td>
                                <td><?php echo ($row["visible"] == 1) ? 'Yes' : 'No'; ?></td>
                                <td>
                                    <a href="/job_details.php?job_id=<?php echo htmlspecialchars($row["id"]); ?>" class="btn btn-primary">View</a>
                                    <a href="/company/add_job_applications.php?id=<?php echo htmlspecialchars($row["id"]); ?>" class="btn btn-warning">Edit</a>
                                    <a href="/company/delete_job_listing.php?id=<?php echo htmlspecialchars($row["id"]); ?>" onclick="return confirmAction_delete();" class="btn btn-danger">Delete</a>
                                </td>
                            </tr>
                    <?php
                        }
                    } else {
                        echo "<tr><td colspan='6'>No job listings found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        // Initial count update on page load
        updateResultCount();
    </script>
</body>

</html>
