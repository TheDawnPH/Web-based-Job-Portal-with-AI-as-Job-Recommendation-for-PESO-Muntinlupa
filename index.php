<?php
session_start();
?>

<html>

<head>
    <title>PESO Muntinlupa - Job Portal</title>
    <link rel="stylesheet" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <style>
    .cover {
        /* use 1350x300px image */
        background-image: url('img/test_cover2.png');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        height: 300px;

    }

    @media (max-width: 1200px) {
        .cover {
            display: none;
            /* Hide the cover image on devices with resolution 1200px and below */
        }
    }
    </style>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="col-md-12">
        <div class="cover"></div>
    </div>
    <div class="container">
        <div class="row gx-5">
            <?php if(isset($_SESSION["user_type"]) && $_SESSION["user_type"] === applicant){
                echo '<div class="col-md-4">';
                echo '<h1>Available Jobs</h1>';
                echo '<hr>';
                echo '<!-- insert php code here that sorts job applications based on ai --><br>';
                echo '</div>';
            }
            ?>
            <div class="col-md-4">
                <h1>Latest Jobs</h1>
                <hr>
                <!-- insert php code here --><br>
            </div>
            <div class="col-md-4">
                <h1>Login or Register</h1>
                <hr>
                <div class="d-grid gap-2">
                    <a href="login.php" class="btn btn-primary btn-lg">Login</a>
                    <a href="register.php" class="btn btn-primary btn-lg">Register</a>
                </div><br>
            </div>
            <div class="col-md-4">
                <h1>Facebook Feed</h1>
                <hr>
                <iframe
                    src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2FPESOMuntinlupaOfficial%2F&tabs=timeline&small_header=true&adapt_container_width=true&hide_cover=true&show_facepile=false&appId"
                    width="350" height="500" style="border:none;overflow:hidden;" scrolling="no" frameborder="0"
                    allowfullscreen="true"
                    allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>
            </div>
        </div>
    </div>
</body>

</html>