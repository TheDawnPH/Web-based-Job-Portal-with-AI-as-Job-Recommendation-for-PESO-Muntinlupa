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

// Retrieve data from the users table sort alphabetically by last name
$sql = "SELECT * FROM users ORDER BY lname ASC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error executing query: " . mysqli_error($conn));
}

?>

<html>

<head>
    <title>Users - Admin</title>
    <link rel="stylesheet" href="/css/index.css">
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
    <script>
    function confirmAction() {
        return confirm("Are you sure you want to delete this user?");
    }

    // search and filter table
    function searchTable() {
        // Declare variables
        var input, filter, table, tr, td, i, txtValue, filterType, filterValue;
        input = document.getElementById("search");
        filter = input.value.toUpperCase();
        table = document.getElementById("data");
        tr = table.getElementsByTagName("tr");
        filterType = document.getElementById("filter").value;

        // Loop through all table rows, and hide those who don't match the search query
        for (i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
            td = tr[i].getElementsByTagName("td")[25]; // Filter by user type
            if (filterType === "all" || td.innerText === filterType) {
                td = tr[i].getElementsByTagName("td")[1]; // Filter by full name
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            } else {
                tr[i].style.display = "none";
            }
        }

        // Update the count of visible rows
        countVisibleRows();
    }

    // Function to count visible rows and update the result count
    function countVisibleRows() {
        var table = document.getElementById("data");
        var tr = table.getElementsByTagName("tr");
        var count = 0;
        for (var i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
            if (tr[i].style.display !== "none") {
                count++;
            }
        }
        document.getElementById("count").innerText = count;
    }

    // Initialize count on page load
    document.addEventListener("DOMContentLoaded", function() {
        countVisibleRows();
    });

    function printTable() {
        var tableData = '<table border="1" style="text-align:center;">' + document.getElementsByTagName('table')[0]
            .innerHTML + '</table>';
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
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.23.5/extensions/print/bootstrap-table-print.js">
    </script>
    <style>
    @media print {

        /* Ensure table layout is proper */
        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: avoid;
        }

        /* Ensure table headers and rows are visible */
        th,
        td {
            padding: 8px;
            text-align: left;
        }

        /* Avoid page breaks inside table rows */
        tr {
            page-break-inside: avoid;
        }

        /* Handle large tables that may overflow */
        table {
            overflow: visible;
        }

        /* Hide unnecessary elements when printing */
        .no-print,
        .hide-on-print {
            display: none;
        }

        /* Set the font and make adjustments for print readability */
        body {
            font-size: 12pt;
            color: black;
            background-color: white;
        }

        /* Ensure full-width tables print properly */
        @page {
            size: auto;
            margin: 20mm;
        }

        /* Make sure long content inside a cell wraps correctly */
        td {
            word-wrap: break-word;
        }
    }
    </style>
</head>

<body>
    <?php include $root . '/nav.php'; ?>
    <div class="container">
        <h1>Users</h1>
        <img src="https://muntinlupacity.gov.ph/wp-content/uploads/2022/10/line_blue_yellow_red-scaled.jpg"
            class="img-fluid" alt="Responsive image">
        <br class="no-print"><br class="no-print">
        <a class="no-print btn btn-primary" role="button" href="/admin/add_users.php">Add User</a>
        <input type="button" onclick="exportTableToExcel('data', 'users')" value="Export to Excel"
            class="no-print btn btn-success" />
        <br class="no-print"><br class="no-print">
        <!-- search bar -->
        <input type="text" id="search" class="form-control no-print" onkeyup="searchTable()"
            placeholder="Search for users..."><br>
        <!-- filter dropdown user type -->
        <label for="filter" class="no-print">Filter by User Type:</label>
        <select id="filter" class="form-select no-print" onchange="searchTable()">
            <option value="all">All</option>
            <option value="admin">Admin</option>
            <option value="company">Employer</option>
            <option value="applicant">Applicant</option>
        </select>
        <br>
        <p>Number of Results: <span id="count"></span></p>
        <div class="table-responsive table-striped">
            <?php
            // Check if there are rows returned
            if (mysqli_num_rows($result) > 0) {
                echo "<table class='table table-striped table-bordered border-start table-print' id='data'>
                <thead>
                    <tr>
                        <th class='no-print'>Action</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Birthdate (dd-mm-yyyy)</th>
                        <th>Gender</th>
                        <th>Civil Status</th>
                        <th>Contact Number</th>
                        <th>Complete Address</th>
                        <th>Latest School Name</th>
                        <th>Latest School Year Attended</th>
                        <th>Technical School Name</th>
                        <th>Skills</th>
                        <th>Work Experience</th>
                        <th>4P's Member</th>
                        <th>PWD</th>
                        <th>Disability Type</th>
                        <th>Currently OFW?</th>
                        <th>Current OFW Country</th>
                        <th>Former OFW?</th>
                        <th>Former OFW Country</th>
                        <th>Former OFW Year</th>
                        <th>Preferred Industry</th>
                        <th>Company Name (Employer)</th>
                        <th>Company Position (Employer)</th>
                        <th>Company Details (Employer)</th>
                        <th>User Type</th>
                        <th>Date and Time of Registration</th>

                    </tr>
                </thead>
                <tbody>";

                // Loop through each row returned by the query
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td class='no-print'><a class='btn btn-warning' role='button' href='/profile.php?user_id=" . htmlspecialchars($row['user_id']) . "'>View Profile</a> <a class='btn btn-danger' role='button' onclick='return confirmAction();' href='/admin/delete_user.php?user_id=" . htmlspecialchars($row['user_id']) . "'>Delete User</a></td>";
                    echo "<td>" . htmlspecialchars($row['fname']) . " " . htmlspecialchars($row['mname']) . " " . htmlspecialchars($row['lname']) . " " . htmlspecialchars($row['suffix']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['birth_day']) . "-" . htmlspecialchars($row['birth_month']) . "-" . htmlspecialchars($row['birth_year']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['sex']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['civil_status']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['contact_number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['house_number']) . " " . htmlspecialchars($row['street']) . ", " . htmlspecialchars($row['barangay']) . ", " . htmlspecialchars($row['city']) . ", " . htmlspecialchars($row['province']) . " " . htmlspecialchars($row['zip_code']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['latest_school_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['latest_school_year_begin']) . " - " . htmlspecialchars($row['latest_school_year_end']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['technicalschool_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['skills']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['work_experience']) . "</td>";
                    echo "<td>" . ($row['fourps_member'] ? 'Yes' : 'No') . "</td>";
                    echo "<td>" . ($row['pwd_member'] ? 'Yes' : 'No') . "</td>";
                    echo "<td>" . htmlspecialchars($row['disability_type']) . "</td>";
                    echo "<td>" . ($row['ofw'] ? 'Yes' : 'No') . "</td>";
                    echo "<td>" . htmlspecialchars($row['ofw_country']) . "</td>";
                    echo "<td>" . ($row['former_ofw'] ? 'Yes' : 'No') . "</td>";
                    echo "<td>" . htmlspecialchars($row['last_ofw_country']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['last_ofw_year']) . "</td>";

                    // Retrieve the jinindustry_id from the current row
                    $jinindustry_id = $row['jinindustry_id'];

                    // Prepare and execute the query to get the industry name
                    $query = "SELECT jinindustry_name FROM jinindustry WHERE jinindustry_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $jinindustry_id);
                    $stmt->execute();
                    $stmt->bind_result($industry_name);
                    $stmt->fetch();
                    $stmt->close();

                    // Display the industry name in the table cell
                    echo "<td>" . htmlspecialchars($industry_name) . "</td>";

                    // if user is not a company, display "N/A" for company name and position
                    if ($row['user_type'] != 'company') {
                        echo "<td>N/A</td>";
                        echo "<td>N/A</td>";
                    } else {
                        // Retrieve the cdocu_id from the current user
                        $cdocu_id_user = $row['user_id'];
                        echo "<td>" . htmlspecialchars($row['company_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['company_position']) . "</td>";
                    }

                    // Retrieve the cdocu_id from the current user
                    $cdocu_id_user = $row['user_id'];

                    // Prepare and execute the query to get the industry name
                    $query = "SELECT cdocu_id FROM company_documents WHERE user_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $cdocu_id_user);
                    $stmt->execute();
                    $stmt->bind_result($cdocu_id);
                    $stmt->fetch();
                    $stmt->close();

                    // if no results found, display "No company details"
                    if (empty($cdocu_id)) {
                        echo "<td>No company details</td>";
                    } else {
                        // Display the link for viewing the company details
                        echo "<td>" . "<a href='/company/company_details.php?cdocu_id=" . htmlspecialchars($cdocu_id) . "'>View Company Details</a>" . "</td>";
                    }

                    echo "<td>" . htmlspecialchars($row['user_type']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";

                }
                echo "</tbody></table>";
            } else {
                echo "<div class='alert alert-danger' role='alert'>No users found.</div>";
            }

            // Close the database connection
            mysqli_close($conn);
            ?>
        </div>
    </div>
    <script>
    function exportTableToExcel(tableID, filename = '') {
        var downloadLink;
        var dataType = 'application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);
        var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

        // Specify file name
        filename = filename ? filename + '.xls' : 'users.xls';

        // Create download link element
        downloadLink = document.createElement("a");

        document.body.appendChild(downloadLink);

        if (navigator.msSaveOrOpenBlob) {
            var blob = new Blob(['\ufeff', tableHTML], {
                type: dataType
            });
            navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            // Create a link to the file
            downloadLink.href = 'data:' + dataType + ', ' + tableHTML;

            // Setting the file name
            downloadLink.download = filename;

            //triggering the function
            downloadLink.click();
        }
    }
    </script>
</body>

</html>