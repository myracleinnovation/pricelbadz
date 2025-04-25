<?php
include './header.php';
include '../config/connect.php';

// Initialize filter variables
$search = isset($_POST['search']) ? $_POST['search'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'All Status';

// Build the query with optional filters
$query = "SELECT * FROM (
    SELECT 
        order_number, 
        customer_name, 
        store_name, 
        store_address as pickup_location, 
        delivery_address as dropoff_address, 
        assigned_rider, 
        order_status,
        'PABILI' as order_type,
        created_at
    FROM tpabili_orders
    UNION ALL
    SELECT 
        order_number, 
        customer_name, 
        NULL as store_name, 
        pickup_address as pickup_location, 
        dropoff_address, 
        assigned_rider, 
        order_status,
        'PAANGKAS' as order_type,
        created_at
    FROM tpaangkas_orders
    UNION ALL
    SELECT 
        order_number, 
        customer_name, 
        NULL as store_name, 
        pickup_location, 
        dropoff_address, 
        assigned_rider, 
        order_status,
        'PADALA' as order_type,
        created_at
    FROM tpadala_orders
) AS all_orders
WHERE (order_number LIKE ? OR customer_name LIKE ? OR assigned_rider LIKE ?)";

if ($status !== 'All Status') {
    $query .= ' AND order_status = ?';
}

$query .= ' ORDER BY created_at DESC';

// Prepare and execute the query
$stmt = $conn->prepare($query);
$search_term = '%' . $search . '%';
if ($status !== 'All Status') {
    $stmt->bind_param('ssss', $search_term, $search_term, $search_term, $status);
} else {
    $stmt->bind_param('sss', $search_term, $search_term, $search_term);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Manage Customer Orders</h5>
                        <form method="POST" class="row g-3">
                            <div class="col-md-7">
                                <input type="text" class="form-control" name="search" id="inputText"
                                    value="<?= htmlspecialchars($search) ?>"
                                    placeholder="Enter order number, customer name, or assigned rider">
                            </div>
                            <div class="col-md-4">
                                <select name="status" id="inputState" class="form-select">
                                    <option value="All Status" <?= $status === 'All Status' ? 'selected' : '' ?>>All
                                        Status</option>
                                    <option value="Pending" <?= $status === 'Pending' ? 'selected' : '' ?>>Pending
                                    </option>
                                    <option value="Accepted" <?= $status === 'Accepted' ? 'selected' : '' ?>>Accepted
                                    </option>
                                    <option value="In Progress" <?= $status === 'In Progress' ? 'selected' : '' ?>>In
                                        Progress
                                    </option>
                                    <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed
                                    </option>
                                    <option value="Cancelled" <?= $status === 'Cancelled' ? 'selected' : '' ?>>Cancelled
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <!-- Order Type Tabs -->
                        <ul class="nav nav-tabs" id="orderTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="all-tab" data-bs-toggle="tab"
                                    data-bs-target="#all" type="button" role="tab" aria-controls="all"
                                    aria-selected="true">All Orders</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pabili-tab" data-bs-toggle="tab" data-bs-target="#pabili"
                                    type="button" role="tab" aria-controls="pabili" aria-selected="false">PABILI
                                    Orders</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="paangkas-tab" data-bs-toggle="tab"
                                    data-bs-target="#paangkas" type="button" role="tab" aria-controls="paangkas"
                                    aria-selected="false">PAANGKAS Orders</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="padala-tab" data-bs-toggle="tab" data-bs-target="#padala"
                                    type="button" role="tab" aria-controls="padala" aria-selected="false">PADALA
                                    Orders</button>
                            </li>
                        </ul>

                        <div class="tab-content pt-2" id="orderTabContent">
                            <!-- All Orders Tab -->
                            <div class="tab-pane fade show active" id="all" role="tabpanel"
                                aria-labelledby="all-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Order Number</th>
                                                <th scope="col">Customer Name</th>
                                                <th scope="col">Service Type</th>
                                                <th scope="col">Merchant/Store</th>
                                                <th scope="col">Assigned Rider</th>
                                                <th scope="col">Order Status</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $count = 1;
                                            if ($result->num_rows > 0):
                                                while ($row = $result->fetch_assoc()):
                                            ?>
                                            <tr>
                                                <th scope="row"><?= $count++ ?></th>
                                                <td><?= htmlspecialchars($row['order_number']) ?></td>
                                                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                                <td><?= htmlspecialchars($row['order_type']) ?></td>
                                                <td><?= htmlspecialchars($row['store_name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['assigned_rider'] ?? 'Not assigned') ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?php
                                                    switch ($row['order_status']) {
                                                        case 'Pending':
                                                            echo 'bg-warning';
                                                            break;
                                                        case 'Accepted':
                                                            echo 'bg-info';
                                                            break;
                                                        case 'In Progress':
                                                            echo 'bg-primary';
                                                            break;
                                                        case 'Completed':
                                                            echo 'bg-success';
                                                            break;
                                                        case 'Cancelled':
                                                            echo 'bg-danger';
                                                            break;
                                                        default:
                                                            echo 'bg-secondary';
                                                    }
                                                    ?>">
                                                        <?= htmlspecialchars($row['order_status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#orderModal<?= urlencode($row['order_number']) ?>">
                                                        View
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php
                                                endwhile;
                                            else:
                                            ?>
                                            <tr>
                                                <td colspan="8" class="text-center">No orders found.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- PABILI Orders Tab -->
                            <div class="tab-pane fade" id="pabili" role="tabpanel" aria-labelledby="pabili-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Order Number</th>
                                                <th scope="col">Customer Name</th>
                                                <th scope="col">Store Name</th>
                                                <th scope="col">Order Description</th>
                                                <th scope="col">Estimated Price</th>
                                                <th scope="col">Assigned Rider</th>
                                                <th scope="col">Order Status</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $count = 1;
                                            mysqli_data_seek($result, 0);
                                            while ($row = $result->fetch_assoc()):
                                                if ($row['order_type'] === 'PABILI'):
                                            ?>
                                            <tr>
                                                <th scope="row"><?= $count++ ?></th>
                                                <td><?= htmlspecialchars($row['order_number']) ?></td>
                                                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                                <td><?= htmlspecialchars($row['store_name']) ?></td>
                                                <td><?= htmlspecialchars($row['order_description'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['estimated_price'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['assigned_rider'] ?? 'Not assigned') ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?php
                                                    switch ($row['order_status']) {
                                                        case 'Pending':
                                                            echo 'bg-warning';
                                                            break;
                                                        case 'Accepted':
                                                            echo 'bg-info';
                                                            break;
                                                        case 'In Progress':
                                                            echo 'bg-primary';
                                                            break;
                                                        case 'Completed':
                                                            echo 'bg-success';
                                                            break;
                                                        case 'Cancelled':
                                                            echo 'bg-danger';
                                                            break;
                                                        default:
                                                            echo 'bg-secondary';
                                                    }
                                                    ?>">
                                                        <?= htmlspecialchars($row['order_status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#orderModal<?= urlencode($row['order_number']) ?>">
                                                        View
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php
                                                endif;
                                            endwhile;
                                            if ($count === 1):
                                            ?>
                                            <tr>
                                                <td colspan="9" class="text-center">No PABILI orders found.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- PAANGKAS Orders Tab -->
                            <div class="tab-pane fade" id="paangkas" role="tabpanel"
                                aria-labelledby="paangkas-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Order Number</th>
                                                <th scope="col">Customer Name</th>
                                                <th scope="col">Vehicle Type</th>
                                                <th scope="col">Pickup Address</th>
                                                <th scope="col">Dropoff Address</th>
                                                <th scope="col">Assigned Rider</th>
                                                <th scope="col">Order Status</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $count = 1;
                                            mysqli_data_seek($result, 0);
                                            while ($row = $result->fetch_assoc()):
                                                if ($row['order_type'] === 'PAANGKAS'):
                                            ?>
                                            <tr>
                                                <th scope="row"><?= $count++ ?></th>
                                                <td><?= htmlspecialchars($row['order_number']) ?></td>
                                                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                                <td><?= htmlspecialchars($row['vehicle_type'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['pickup_location']) ?></td>
                                                <td><?= htmlspecialchars($row['dropoff_address']) ?></td>
                                                <td><?= htmlspecialchars($row['assigned_rider'] ?? 'Not assigned') ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?php
                                                    switch ($row['order_status']) {
                                                        case 'Pending':
                                                            echo 'bg-warning';
                                                            break;
                                                        case 'Accepted':
                                                            echo 'bg-info';
                                                            break;
                                                        case 'In Progress':
                                                            echo 'bg-primary';
                                                            break;
                                                        case 'Completed':
                                                            echo 'bg-success';
                                                            break;
                                                        case 'Cancelled':
                                                            echo 'bg-danger';
                                                            break;
                                                        default:
                                                            echo 'bg-secondary';
                                                    }
                                                    ?>">
                                                        <?= htmlspecialchars($row['order_status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#orderModal<?= urlencode($row['order_number']) ?>">
                                                        View
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php
                                                endif;
                                            endwhile;
                                            if ($count === 1):
                                            ?>
                                            <tr>
                                                <td colspan="9" class="text-center">No PAANGKAS orders found.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- PADALA Orders Tab -->
                            <div class="tab-pane fade" id="padala" role="tabpanel" aria-labelledby="padala-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Order Number</th>
                                                <th scope="col">Customer Name</th>
                                                <th scope="col">Order Description</th>
                                                <th scope="col">Pickup Location</th>
                                                <th scope="col">Dropoff Address</th>
                                                <th scope="col">Assigned Rider</th>
                                                <th scope="col">Order Status</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $count = 1;
                                            mysqli_data_seek($result, 0);
                                            while ($row = $result->fetch_assoc()):
                                                if ($row['order_type'] === 'PADALA'):
                                            ?>
                                            <tr>
                                                <th scope="row"><?= $count++ ?></th>
                                                <td><?= htmlspecialchars($row['order_number']) ?></td>
                                                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                                <td><?= htmlspecialchars($row['order_description'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['pickup_location']) ?></td>
                                                <td><?= htmlspecialchars($row['dropoff_address']) ?></td>
                                                <td><?= htmlspecialchars($row['assigned_rider'] ?? 'Not assigned') ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?php
                                                    switch ($row['order_status']) {
                                                        case 'Pending':
                                                            echo 'bg-warning';
                                                            break;
                                                        case 'Accepted':
                                                            echo 'bg-info';
                                                            break;
                                                        case 'In Progress':
                                                            echo 'bg-primary';
                                                            break;
                                                        case 'Completed':
                                                            echo 'bg-success';
                                                            break;
                                                        case 'Cancelled':
                                                            echo 'bg-danger';
                                                            break;
                                                        default:
                                                            echo 'bg-secondary';
                                                    }
                                                    ?>">
                                                        <?= htmlspecialchars($row['order_status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#orderModal<?= urlencode($row['order_number']) ?>">
                                                        View
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php
                                                endif;
                                            endwhile;
                                            if ($count === 1):
                                            ?>
                                            <tr>
                                                <td colspan="9" class="text-center">No PADALA orders found.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    html,
    body {
        height: 100%;
        margin: 0;
    }

    body {
        display: flex;
        flex-direction: column;
    }

    #main {
        flex: 1 0 auto;
    }

    #footer {
        flex-shrink: 0;
    }
</style>

<footer id="footer" class="footer border-top py-3">
    <div class="container">
        <div class="copyright text-center">
            &copy; 2025 <strong><span>PricelBadz</span></strong>. All Rights Reserved
        </div>
    </div>
</footer>

<?php
mysqli_close($conn);
?>
