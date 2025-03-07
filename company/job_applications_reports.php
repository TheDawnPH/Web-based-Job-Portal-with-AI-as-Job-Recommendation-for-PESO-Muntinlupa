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
?>
<html>

<head>
    <title>Job Application Reports - Admin</title>
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
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-table/1.23.5/extensions/print/bootstrap-table-print.js">
    </script>
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
        <h1>Job Applications Reports</h1>
        <img src="https://muntinlupacity.gov.ph/wp-content/uploads/2022/10/line_blue_yellow_red-scaled.jpg"
            class="img-fluid" alt="Responsive image">
        <br class="no-print">
        <br class="no-print">
        <input type="button" onclick="printTable()" value="Print Everything" class="no-print btn btn-primary" />
        <input type="button" onclick="exportTableToExcel('data', 'Job Applications Reports')" value="Export to Excel"
            class="no-print btn btn-success" />
        <br class="no-print"><br class="no-print">
        <!-- search bar for job listings -->
        <input type="text" id="search" onkeyup="searchTable()" placeholder="Search for Job title/Company.."
            class="no-print form-control">
        <br>
        <!-- filter dropdown -->
        <label for="filter" class="no-print">Filter by Application Status:</label>
        <select id="filter" class="no-print form-select" onchange="filterTable()">
            <option value="all">All</option>
            <option value="Approved">Approved</option>
            <option value="Denied">Denied</option>
            <option value="Pending">Pending</option>
        </select>
        <br>
        <p>Number of Results: <span id="count"></span></p>
        <div class="table-responsive">
            <table class="table table-striped table-bordered border-start" id="data" data-show-print="true">
                <thead>
                    <tr>
                        <th>Application ID</th>
                        <th>Job Title</th>
                        <th>Company Name</th>
                        <th>Applicant Name</th>
                        <th>Applicant Email</th>
                        <th>Application Date</th>
                        <th>Updated At</th>
                        <th>Application Status</th>
                        <th class="no-print">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // get all job applications created by employer
                    $sql = "SELECT * FROM job_applications WHERE job_id IN (SELECT id FROM job_listing WHERE user_id = '" . $_SESSION['user_id'] . "') ORDER BY created_at DESC";
                    $result = mysqli_query($conn, $sql);
                    while ($row = mysqli_fetch_array($result)) {
                        $Jobname = mysqli_fetch_array(mysqli_query($conn, "SELECT job_title FROM job_listing WHERE id = '" . $row['job_id'] . "'"));
                        $userdetails = mysqli_fetch_array(mysqli_query($conn, "SELECT * FROM users WHERE user_id = '" . $row['user_id'] . "'"));
                        $companydetails = mysqli_fetch_array(mysqli_query($conn, "SELECT user_id FROM job_listing WHERE id = '" . $row['job_id'] . "'"));
                        $companydetails = mysqli_fetch_array(mysqli_query($conn, "SELECT company_name FROM users WHERE user_id = '" . $companydetails['user_id'] . "'"));

                        echo "<tr>";
                        echo "<td>" . $row['app_id'] . "</td>";
                        echo "<td>" . $Jobname['job_title'] . "</td>";
                        echo "<td>" . $companydetails['company_name'] . "</td>";
                        echo "<td>" . $userdetails['fname'] . " " . $userdetails['mname'] . " " . $userdetails['lname'] . "</td>";
                        echo "<td>" . $userdetails['email'] . "</td>";
                        echo "<td>" . date("h:i:s A F j, Y", strtotime($row["created_at"])) . "</td>";
                        echo "<td>" . date("h:i:s A F j, Y", strtotime($row["updated_at"])) . "</td>";
                        echo "<td>" . ($row['application_status'] == '1' ? 'Approved' : ($row['application_status'] == '2' ? 'Denied' : 'Pending')) . "</td>";
                        echo "<td>
                        <a href='/profile.php?user_id=" . $userdetails['user_id'] . "' class='btn btn-primary' role='button'>View User</a><br><br>
                        <a href='/job_details.php?job_id=" . $row['job_id'] . "' class='btn btn-warning' role='button'>View Job Listing</a>
                        </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
    // hide action column when printing
    function printTable() {
        // make table responsive
        var table = document.getElementById("data");
        // remove action column
        var th = table.getElementsByTagName("th")[8];
        th.style.display = "none";
        var tr = table.getElementsByTagName("tr");
        for (var i = 0; i < tr.length; i++) {
            var td = tr[i].getElementsByTagName("td")[8];
            if (td) {
                td.style.display = "none";
            }
        }
        document.body.style.margin = "0";
        table.classList.add("table-responsive");
        table.classList.add("table-print");
        window.print();
        table.classList.remove("table-print");
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

    // Function to filter the table based on application status
    function filterTable() {
        var filter = document.getElementById("filter").value;
        var table = document.getElementById("data");
        var tr = table.getElementsByTagName("tr");
        for (var i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
            var td = tr[i].getElementsByTagName("td")[7];
            if (td) {
                if (filter == "all") {
                    tr[i].style.display = "";
                } else if (td.innerHTML == filter) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
        countVisibleRows();
    }

    // Function to search the table based on company name and job title
    function searchTable() {
        var input, filter, table, tr, td, i, txtValue;
        input = document.getElementById("search");
        filter = input.value.toUpperCase();
        table = document.getElementById("data");
        tr = table.getElementsByTagName("tr");
        for (i = 1; i < tr.length; i++) { // Start from 1 to skip the header row
            tr[i].style.display = "none";
            tdCompany = tr[i].getElementsByTagName("td")[2];
            tdJobTitle = tr[i].getElementsByTagName("td")[1];
            if (tdCompany || tdJobTitle) {
                txtValueCompany = tdCompany.textContent || tdCompany.innerText;
                txtValueJobTitle = tdJobTitle.textContent || tdJobTitle.innerText;
                if (txtValueCompany.toUpperCase().indexOf(filter) > -1 || txtValueJobTitle.toUpperCase().indexOf(
                        filter) > -1) {
                    tr[i].style.display = "";
                }
            }
        }
        countVisibleRows();
    }

    // Initialize count on page load
    document.addEventListener("DOMContentLoaded", function() {
        countVisibleRows();
    });
    </script>
    <script>
    function exportTableToExcel(tableID, filename = '') {
        var downloadLink;
        var dataType = 'application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);

        // Clone the table to avoid modifying the original
        var clonedTable = tableSelect.cloneNode(true);

        // Remove the action column from the cloned table
        var actionColumnIndex = 8; // Replace with the zero-based index of the Action column
        for (var i = 0; i < clonedTable.rows.length; i++) {
            clonedTable.rows[i].deleteCell(actionColumnIndex);
        }

        // Convert the cloned table to HTML
        var tableHTML = clonedTable.outerHTML.replace(/ /g, '%20');

        // Specify file name
        filename = filename ? filename + '.xls' : 'excel_data.xls';

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

            // Triggering the function
            downloadLink.click();
        }
    }
    </script>
</body>

</html>