<?php
include './header.php';
include '../config/connect.php';

// Get total number of orders (sum of all order types)
$orderQuery = 'SELECT 
    (SELECT COUNT(*) FROM tpabili_orders) + 
    (SELECT COUNT(*) FROM tpaangkas_orders) + 
    (SELECT COUNT(*) FROM tpadala_orders) as total_orders';
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
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as order_count
FROM (
    SELECT created_at FROM tpabili_orders
    UNION ALL
    SELECT created_at FROM tpaangkas_orders
    UNION ALL
    SELECT created_at FROM tpadala_orders
) AS all_orders
WHERE created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(created_at, '%Y-%m')
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
    CASE 
        WHEN order_status = 'Pending' THEN 'Pending'
        WHEN order_status = 'On-Going' THEN 'On-Going'
        WHEN order_status = 'Completed' THEN 'Completed'
        WHEN order_status = 'Cancelled' THEN 'Cancelled'
        ELSE 'Other'
    END as order_status,
    COUNT(*) as status_count
FROM (
    SELECT order_status FROM tpabili_orders
    UNION ALL
    SELECT order_status FROM tpaangkas_orders
    UNION ALL
    SELECT order_status FROM tpadala_orders
) AS all_orders
GROUP BY 
    CASE 
        WHEN order_status = 'Pending' THEN 'Pending'
        WHEN order_status = 'On-Going' THEN 'On-Going'
        WHEN order_status = 'Completed' THEN 'Completed'
        WHEN order_status = 'Cancelled' THEN 'Cancelled'
        ELSE 'Other'
    END";
$statusResult = $conn->query($statusQuery);

$statusLabels = [];
$statusCounts = [];
$totalOrders = 0;

// First, get all status counts and calculate total
while ($row = $statusResult->fetch_assoc()) {
    $statusLabels[] = $row['order_status'];
    $statusCounts[] = $row['status_count'];
    $totalOrders += $row['status_count'];
}

// Ensure all statuses are included even if they have zero count
$allStatuses = ['Pending', 'On-Going', 'Completed', 'Cancelled'];
$statusMap = array_combine($statusLabels, $statusCounts);

$statusLabels = [];
$statusCounts = [];
$statusPercentages = [];

foreach ($allStatuses as $status) {
    $statusLabels[] = $status;
    $count = isset($statusMap[$status]) ? $statusMap[$status] : 0;
    $statusCounts[] = $count;
    $statusPercentages[] = $totalOrders > 0 ? round(($count / $totalOrders) * 100, 1) : 0;
}
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
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
                    <div class="filter">
                        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <li class="dropdown-header text-start">
                                <h6>Filter</h6>
                            </li>
                            <li><a class="dropdown-item" href="#">Today</a></li>
                            <li><a class="dropdown-item" href="#">This Week</a></li>
                            <li><a class="dropdown-item" href="#">This Month</a></li>
                            <li><a class="dropdown-item" href="#">All Time</a></li>
                        </ul>
                    </div>

                    <div class="card-body pb-0">
                        <h5 class="card-title">Order Status Distribution <span>| All Time</span></h5>
                        <div id="orderStatusChart" style="min-height: 400px;" class="echart"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<footer id="footer" class="footer border-top py-3">
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

        // Order Status Distribution Chart using ECharts
        echarts.init(document.querySelector("#orderStatusChart")).setOption({
            tooltip: {
                trigger: 'item',
                formatter: '{a} <br/>{b}: {c} ({d}%)'
            },
            legend: {
                top: '5%',
                left: 'center'
            },
            series: [{
                name: 'Order Status',
                type: 'pie',
                radius: ['40%', '70%'],
                avoidLabelOverlap: false,
                itemStyle: {
                    borderRadius: 10,
                    borderColor: '#fff',
                    borderWidth: 2
                },
                label: {
                    show: false,
                    position: 'center'
                },
                emphasis: {
                    label: {
                        show: true,
                        fontSize: '18',
                        fontWeight: 'bold'
                    }
                },
                labelLine: {
                    show: false
                },
                data: [{
                        value: <?= isset($statusMap['Pending']) ? $statusMap['Pending'] : 0 ?>,
                        name: 'Pending',
                        itemStyle: {
                            color: '#ffc107'
                        }
                    },
                    {
                        value: <?= isset($statusMap['On-Going']) ? $statusMap['On-Going'] : 0 ?>,
                        name: 'On-Going',
                        itemStyle: {
                            color: '#0dcaf0'
                        }
                    },
                    {
                        value: <?= isset($statusMap['Completed']) ? $statusMap['Completed'] : 0 ?>,
                        name: 'Completed',
                        itemStyle: {
                            color: '#198754'
                        }
                    },
                    {
                        value: <?= isset($statusMap['Cancelled']) ? $statusMap['Cancelled'] : 0 ?>,
                        name: 'Cancelled',
                        itemStyle: {
                            color: '#dc3545'
                        }
                    }
                ]
            }]
        });
    });
</script>

<?php
mysqli_close($conn);
?>