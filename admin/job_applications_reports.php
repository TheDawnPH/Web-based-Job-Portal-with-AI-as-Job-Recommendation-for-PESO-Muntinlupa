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
    <script src="extensions/print/bootstrap-table-print.js"></script>
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <?php include $root . '/nav.php'; ?>
    <div class="container">
        <br>
        <h1>Job Applications Reports</h1>
        <div class="table-responsive">
        <input type="button" onclick="printTable()" value="Print Everything" class="no-print btn btn-primary" />
<br><br>
            <table class="table table-striped table-bordered border-start" id="data" data-show-print="true">
                <thead>
                    <tr>
                        <th>Application ID</th>
                        <th>Job Title</th>
                        <th>Applicant Name</th>
                        <th>Applicant Email</th>
                        <th>Application Date</th>
                        <th>Application Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM job_applications ORDER BY created_at DESC";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_array($result)) {
                        $Jobname = mysqli_fetch_array(mysqli_query($conn, "SELECT job_title FROM job_listing WHERE id = '" . $row['job_id'] . "'"));
                        $userdetails = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM users WHERE user_id = '" . $row['user_id'] . "'"));

                        echo "<tr>";
                        echo "<td>" . $row['app_id'] . "</td>";
                        echo "<td>" . $Jobname['job_title'] . "</td>";
                        echo "<td>" . $userdetails['fname'] . " " . $userdetails['mname'] . " " . $userdetails['lname'] . "</td>";
                        echo "<td>" . $userdetails['email'] . "</td>";
                        echo "<td>" . date("h:i:s A F j, Y", strtotime($row["created_at"])) . "</td>";
                        echo "<td>" . ($row['application_status'] == '1' ? 'Approved' : ($row['application_status'] == '2' ? 'Denied' : 'Pending')) . "</td>";
                        echo "<td><a href='/profile.php?user_id=" . $userdetails['user_id'] . "' class='btn btn-primary'>View User</a></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function printTable() {
            var tableData = '<table border="1" style="text-align:center;">' + document.getElementsByTagName('table')[0].innerHTML + '</table>';
            var data = tableData;
            var myWindow = window.open('', '', 'width=800,height=600');
            myWindow.document.write(data);
            myWindow.document.close();
            myWindow.focus();
            myWindow.print();
            myWindow.close();
            return true;
        }
    </script>
</body>