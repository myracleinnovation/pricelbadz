<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header('Location: ../login.php');
    exit();
}

// Get the current page filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>PricelBadz</title>
    <meta name="robots" content="noindex, nofollow">
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="../public/assets/img/logo.png" rel="icon">
    <link href="../public/assets/img/logo.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Vendor CSS Files -->
    <link href="../public/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="../public/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="../public/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="../public/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="../public/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="../public/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="../public/assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="../public/assets/css/style.css" rel="stylesheet">
</head>

<body>
    <header id="header" class="header fixed-top d-flex align-items-center">
        <div class="d-flex align-items-center justify-content-between">
            <a href="dashboard.php" class="logo d-flex align-items-center">
                <span class="d-none d-lg-block"><img src="../public/img/logo.png" alt="PricelBadz Image"
                        style="max-height: 50px; width: auto;"></span>
            </a>
            <i class="bi bi-list toggle-sidebar-btn"></i>
        </div>
        <nav class="header-nav ms-auto">
            <ul class="d-flex align-items-center">
                <li class="nav-item dropdown pe-3">
                    <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#"
                        data-bs-toggle="dropdown">
                        <img src="../public/img/bg.jpg" alt="Profile" class="rounded-circle"
                            style="width: 36px; height: 36px; object-fit: cover;">
                        <span
                            class="d-none d-md-block dropdown-toggle ps-2"><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header">
                            <h6><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></h6>
                            <span><?= htmlspecialchars($_SESSION['access_type']) ?></span>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <?php
                        if (isset($_POST['logout'])) {
                            session_destroy();
                            header('Location: ../home.php');
                            exit();
                        }
                        ?>
                        <li>
                            <form action="header.php" method="POST">
                                <button type="submit" class="dropdown-item d-flex align-items-center" name="logout">
                                    <i class="bi bi-box-arrow-right"></i>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>
    <aside id="sidebar" class="sidebar">
        <ul class="sidebar-nav" id="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'dashboard.php' ? '' : 'collapsed' ?>" href="dashboard.php">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'customer_orders.php' ? '' : 'collapsed' ?>"
                    href="customer_orders.php">
                    <i class="bi bi-person"></i>
                    <span>Customer Orders</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'delivery_rider.php' ? '' : 'collapsed' ?>"
                    href="delivery_rider.php">
                    <i class="bi bi-grid"></i>
                    <span>Delivery Riders</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'user_account.php' ? '' : 'collapsed' ?>"
                    href="user_account.php">
                    <i class="bi bi-grid"></i>
                    <span>User Accounts</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $current_page === 'merchant.php' ? '' : 'collapsed' ?>" href="merchant.php">
                    <i class="bi bi-grid"></i>
                    <span>Merchant</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Vendor JS Files -->
    <script src="../public/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="../public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../public/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="../public/assets/vendor/echarts/echarts.min.js"></script>
    <script src="../public/assets/vendor/quill/quill.js"></script>
    <script src="../public/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="../public/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="../public/assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="../public/assets/js/main.js"></script>

    <script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015"
        integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ=="
        data-cf-beacon='{"rayId":"93419b31590b08d9","serverTiming":{"name":{"cfExtPri":true,"cfL4":true,"cfSpeedBrain":true,"cfCacheStatus":true}},"version":"2025.4.0-1-g37f21b1","token":"68c5ca450bae485a842ff76066d69420"}'
        crossorigin="anonymous"></script>
</body>

</html>
