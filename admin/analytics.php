<?php
include './header.php';
include '../config/connect.php';

// Get total number of orders
$orderQuery = 'SELECT COUNT(*) as total_orders FROM tcustomer_order';
$orderResult = $conn->query($orderQuery);
$totalOrders = $orderResult->fetch_assoc()['total_orders'];

// Get total number of riders
$riderQuery = 'SELECT COUNT(*) as total_riders FROM triders';
$riderResult = $conn->query($riderQuery);
$totalRiders = $riderResult->fetch_assoc()['total_riders'];

// Get total number of users
$userQuery = 'SELECT COUNT(*) as total_users FROM tusers';
$userResult = $conn->query($userQuery);
$totalUsers = $userResult->fetch_assoc()['total_users'];

// Get monthly orders data for the last 6 months
$monthlyQuery = "SELECT 
    DATE_FORMAT(date_ordered, '%Y-%m') as month,
    COUNT(*) as order_count
FROM tcustomer_order 
WHERE date_ordered >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(date_ordered, '%Y-%m')
ORDER BY month ASC";
$monthlyResult = $conn->query($monthlyQuery);

$months = [];
$orderCounts = [];
while ($row = $monthlyResult->fetch_assoc()) {
    $months[] = date('M Y', strtotime($row['month']));
    $orderCounts[] = $row['order_count'];
}

// Get order status distribution
$statusQuery = "SELECT 
    order_status,
    COUNT(*) as status_count
FROM tcustomer_order 
GROUP BY order_status";
$statusResult = $conn->query($statusQuery);

$statusLabels = [];
$statusCounts = [];
while ($row = $statusResult->fetch_assoc()) {
    $statusLabels[] = $row['order_status'];
    $statusCounts[] = $row['status_count'];
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="analytics.php">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
        <div class="row">
            <!-- Left side columns -->
            <div class="col-lg-8">
                <div class="row">
                    <!-- Orders Card -->
                    <div class="col-xxl-4 col-md-6">
                        <div class="card info-card sales-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Orders</h5>
                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-cart"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?= $totalOrders ?></h6>
                                        <span class="text-success small pt-1 fw-bold">All Time</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Riders Card -->
                    <div class="col-xxl-4 col-md-6">
                        <div class="card info-card revenue-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Riders</h5>
                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-bicycle"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?= $totalRiders ?></h6>
                                        <span class="text-success small pt-1 fw-bold">Active Riders</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Users Card -->
                    <div class="col-xxl-4 col-md-6">
                        <div class="card info-card customers-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Users</h5>
                                <div class="d-flex align-items-center">
                                    <div
                                        class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi bi-people"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?= $totalUsers ?></h6>
                                        <span class="text-success small pt-1 fw-bold">Registered Users</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Orders Chart -->
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Monthly Orders</h5>
                                <div id="monthlyOrdersChart"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right side columns -->
            <div class="col-lg-4">
                <!-- Order Status Distribution -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Order Status Distribution</h5>
                        <div id="orderStatusChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<footer id="footer" class="footer fixed-bottom border-top py-3">
    <div class="container">
        <div class="copyright text-center">
            &copy; 2025 <strong><span>PricelBadz</span></strong>. All Rights Reserved
        </div>
    </div>
</footer>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        // Monthly Orders Chart
        new ApexCharts(document.querySelector("#monthlyOrdersChart"), {
            series: [{
                name: 'Orders',
                data: <?= json_encode($orderCounts) ?>
            }],
            chart: {
                height: 350,
                type: 'area',
                toolbar: {
                    show: false
                },
            },
            markers: {
                size: 4
            },
            colors: ['#4154f1'],
            fill: {
                type: "gradient",
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.4,
                    stops: [0, 90, 100]
                }
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            xaxis: {
                categories: <?= json_encode($months) ?>,
                labels: {
                    style: {
                        colors: '#666'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#666'
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " orders"
                    }
                }
            }
        }).render();

        // Order Status Distribution Chart
        new ApexCharts(document.querySelector("#orderStatusChart"), {
            series: <?= json_encode($statusCounts) ?>,
            chart: {
                type: 'donut',
                height: 350
            },
            labels: <?= json_encode($statusLabels) ?>,
            colors: ['#ffc107', '#0dcaf0', '#198754', '#6c757d'],
            legend: {
                position: 'bottom'
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%'
                    }
                }
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) {
                    return val.toFixed(1) + '%'
                }
            }
        }).render();
    });
</script>

<?php
mysqli_close($conn);
?>
