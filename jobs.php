<?php
// Start secure session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_only_cookies', 1);
session_start();

include 'config.php';
?>

<html>

<head>
    <title>PESO Muntinlupa - Job Portal</title>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha384-GLhlTQ8iKtTUKh9+8FqSZTWFeRnN43aZ2ACUmTMYGXmi1tU6jBsdFeNxmjQV+ABe6" crossorigin="anonymous"></script>
</head>

<body>
    <?php include 'nav.php'; ?>
    <br>
    <div class="container">
        <h1>Jobs Available</h1><br>
        <div class="row row-cols-1 row-cols-md-2">
            <div class="col-md-5">
                <!-- Search input -->
                <input type="text" id="searchInput" class="form-control form-control-lg" placeholder="Search for a Job"><br>

                <!-- Filter dropdowns -->
                <label for="filterType">Filter by Job Type:</label>
                <select id="filterType" class="form-control">
                    <option value="">All</option>
                    <option value="Full Time">Full Time</option>
                    <option value="Part Time">Part Time</option>
                    <option value="Contractual">Contractual</option>
                </select><br>

                <label for="filterIndustry">Filter by Job Industry:</label>
                <select id="filterIndustry" class="form-control">
                    <option value="">All</option>
                    <?php
                    $sql = "SELECT * FROM jinindustry";
                    $result = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                            <option value="<?php echo $row['jinindustry_name']; ?>"><?php echo $row['jinindustry_name']; ?></option>
                    <?php
                        }
                    }
                    ?>
                </select><br>

                <label for="salaryRange">Filter by Salary Range:</label>
                <select id="salaryRange" class="form-control">
                    <option value="">All</option>
                    <option value="10000">Below 10,000</option>
                    <option value="20000">11,000 - 20,000</option>
                    <option value="30000">21,000 - 30,000</option>
                    <option value="40000">31,000 - 40,000</option>
                    <option value="50000">41,000 - 50,000</option>
                    <option value="60000">51,000 - 60,000</option>
                    <option value="70000">61,000 - 70,000</option>
                    <option value="80000">71,000 - 80,000</option>
                    <option value="90000">81,000 - 90,000</option>
                    <option value="100000">91,000 - 100,000</option>
                    <option value="100000">Above 100,000</option>
                </select><br>

                <!-- JavaScript code for search and filter -->
                <script>
                    var searchInput = document.getElementById('searchInput');
                    var filterType = document.getElementById('filterType');
                    var filterIndustry = document.getElementById('filterIndustry');
                    var salaryRange = document.getElementById('salaryRange');

                    searchInput.addEventListener('input', applyFilters);
                    filterType.addEventListener('change', applyFilters);
                    filterIndustry.addEventListener('change', applyFilters);
                    salaryRange.addEventListener('change', applyFilters);

                    function applyFilters() {
                        var searchQuery = searchInput.value;
                        var selectedType = filterType.value;
                        var selectedIndustry = filterIndustry.value;
                        var selectedSalary = salaryRange.value;
                        searchAndFilterJobs(searchQuery, selectedType, selectedIndustry, selectedSalary);
                    }

                    function searchAndFilterJobs(query, type, industry, salary) {
                        var cards = document.querySelectorAll('.card');

                        cards.forEach(function(card) {
                            var found = false;
                            var title = card.getAttribute('data-title').toLowerCase();
                            var jobType = card.getAttribute('data-type');
                            var jobIndustry = card.getAttribute('data-industry');
                            var jobSalary = parseInt(card.getAttribute('data-salary'), 10);

                            if (
                                (title.indexOf(query.toLowerCase()) > -1) &&
                                (type === '' || type === jobType) &&
                                (industry === '' || industry === jobIndustry)
                            ) {
                                if (salary === '') {
                                    found = true;
                                } else if (salary === '10000' && jobSalary < 10000) {
                                    found = true;
                                } else if (salary === '20000' && jobSalary >= 11000 && jobSalary <= 20000) {
                                    found = true;
                                } else if (salary === '30000' && jobSalary >= 21000 && jobSalary <= 30000) {
                                    found = true;
                                } else if (salary === '40000' && jobSalary >= 31000 && jobSalary <= 40000) {
                                    found = true;
                                } else if (salary === '50000' && jobSalary >= 41000 && jobSalary <= 50000) {
                                    found = true;
                                } else if (salary === '60000' && jobSalary >= 51000 && jobSalary <= 60000) {
                                    found = true;
                                } else if (salary === '70000' && jobSalary >= 61000 && jobSalary <= 70000) {
                                    found = true;
                                } else if (salary === '80000' && jobSalary >= 71000 && jobSalary <= 80000) {
                                    found = true;
                                } else if (salary === '90000' && jobSalary >= 81000 && jobSalary <= 90000) {
                                    found = true;
                                } else if (salary === '100000' && jobSalary >= 91000 && jobSalary <= 100000) {
                                    found = true;
                                } else if (salary === '100000' && jobSalary > 100000) {
                                    found = true;
                                }
                            }

                            if (found) {
                                card.style.display = '';
                            } else {
                                card.style.display = 'none';
                            }
                        });
                    }
                </script>
            </div>
            <div class="col-md-7">
                <div class="card-deck">
                    <?php
                    $sql = "SELECT * FROM job_listing ORDER BY created_at DESC";
                    $result = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $sql2 = "SELECT jinindustry_name FROM jinindustry WHERE jinindustry_id = " . $row['jinindustry_id'];
                            $result2 = mysqli_query($conn, $sql2);
                            $row2 = mysqli_fetch_assoc($result2);
                    ?>
                            <div class="card" data-title="<?php echo htmlspecialchars($row['job_title']); ?>" data-type="<?php echo htmlspecialchars($row['job_type']); ?>" data-industry="<?php echo htmlspecialchars($row2['jinindustry_name']); ?>" data-salary="<?php echo htmlspecialchars($row['job_salary']); ?>" data-date="<?php echo date('F d, Y', strtotime($row['created_at'])); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $row['job_title']; ?></h5>
                                    <p class="card-text">
                                        <strong>Job Type:</strong> <?php echo $row['job_type']; ?><br>
                                        <strong>Job Industry:</strong> <?php echo $row2['jinindustry_name']; ?><br>
                                        <strong>Job Salary:</strong> ₱<?php echo number_format($row['job_salary']); ?><br>
                                        <strong>Date Posted:</strong> <?php echo date('F d, Y', strtotime($row['created_at'])); ?>
                                    </p>
                                    <div class="btn-group" role="group">
                                        <a href="job_details.php?job_id=<?php echo $row['id']; ?>" class="btn btn-secondary">View</a>&nbsp;
                                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] == "applicant") : ?>
                                            <a href="job_applications.php?job_id=<?php echo $row['id']; ?>" class="btn btn-primary">Apply</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div><br>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>