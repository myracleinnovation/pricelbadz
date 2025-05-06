<?php
include './header.php';
include '../config/connect.php';

// Initialize filter variables
$search = isset($_POST['search']) ? $_POST['search'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'All Status';

// Build the query with optional filters
$query = "SELECT * FROM (
    SELECT 
        o.order_number, 
        o.customer_name, 
        o.contact_number,
        o.merchant_store_name, 
        o.store_address as pickup_location, 
        o.pickup_note,
        o.delivery_address as dropoff_address, 
        o.delivery_note,
        o.assigned_rider,
        CASE 
            WHEN r.id IS NOT NULL THEN CONCAT(r.first_name, ' ', COALESCE(r.middle_name, ''), ' ', r.last_name)
            ELSE 'Not assigned'
        END as assigned_rider_name,
        r.vehicle_type as rider_vehicle_type,
        r.topup_balance as rider_balance,
        r.contact_number as rider_contact_number,
        o.order_status,
        o.order_description,
        o.vehicle_type,
        'PABILI/PASUYO' as order_type,
        o.created_at,
        o.service_fee,
        o.commission,
        o.rider_assigned_by,
        o.rider_assigned_at,
        o.created_at as registration_date
    FROM tpabili_orders o
    LEFT JOIN triders r ON o.assigned_rider = r.id
    UNION ALL
    SELECT 
        o.order_number, 
        o.customer_name, 
        o.contact_number,
        NULL as merchant_store_name, 
        o.pickup_address as pickup_location, 
        o.pickup_note,
        o.dropoff_address, 
        o.dropoff_note as delivery_note,
        o.assigned_rider,
        CASE 
            WHEN r.id IS NOT NULL THEN CONCAT(r.first_name, ' ', COALESCE(r.middle_name, ''), ' ', r.last_name)
            ELSE 'Not assigned'
        END as assigned_rider_name,
        r.vehicle_type as rider_vehicle_type,
        r.topup_balance as rider_balance,
        r.contact_number as rider_contact_number,
        o.order_status,
        NULL as order_description,
        o.vehicle_type,
        'PAHATID/PASUNDO' as order_type,
        o.created_at,
        o.service_fee,
        o.commission,
        o.rider_assigned_by,
        o.rider_assigned_at,
        o.created_at as registration_date
    FROM tpaangkas_orders o
    LEFT JOIN triders r ON o.assigned_rider = r.id
    UNION ALL
    SELECT 
        o.order_number, 
        o.customer_name, 
        o.contact_number,
        NULL as merchant_store_name, 
        o.pickup_address as pickup_location, 
        o.pickup_note,
        o.dropoff_address, 
        o.dropoff_note as delivery_note,
        o.assigned_rider,
        CASE 
            WHEN r.id IS NOT NULL THEN CONCAT(r.first_name, ' ', COALESCE(r.middle_name, ''), ' ', r.last_name)
            ELSE 'Not assigned'
        END as assigned_rider_name,
        r.vehicle_type as rider_vehicle_type,
        r.topup_balance as rider_balance,
        r.contact_number as rider_contact_number,
        o.order_status,
        o.order_description,
        o.vehicle_type,
        'PADALA' as order_type,
        o.created_at,
        o.service_fee,
        o.commission,
        o.rider_assigned_by,
        o.rider_assigned_at,
        o.created_at as registration_date
    FROM tpadala_orders o
    LEFT JOIN triders r ON o.assigned_rider = r.id
) AS all_orders
WHERE (order_number LIKE ? OR customer_name LIKE ? OR assigned_rider_name LIKE ?)";

if ($status !== 'All Status') {
    $query .= ' AND order_status = ?';
}

$query .= ' ORDER BY registration_date DESC, created_at DESC';

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
                <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="errorAlert">
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
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body pt-4">
                        <!-- Order Type Tabs -->
                        <ul class="nav nav-tabs" id="orderTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="all-tab" data-bs-toggle="tab"
                                    data-bs-target="#all" type="button" role="tab" aria-controls="all"
                                    aria-selected="true">All Orders</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pabili-tab" data-bs-toggle="tab" data-bs-target="#pabili"
                                    type="button" role="tab" aria-controls="pabili"
                                    aria-selected="false">PABILI/PASUYO
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="paangkas-tab" data-bs-toggle="tab"
                                    data-bs-target="#paangkas" type="button" role="tab" aria-controls="paangkas"
                                    aria-selected="false">PAHATID/PASUNDO</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="padala-tab" data-bs-toggle="tab" data-bs-target="#padala"
                                    type="button" role="tab" aria-controls="padala" aria-selected="false">PADALA
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content pt-2" id="orderTabContent">
                            <!-- All Orders Tab -->
                            <div class="tab-pane fade show active" id="all" role="tabpanel"
                                aria-labelledby="all-tab">
                                <div class="table-responsive pt-3">
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
                                                <td><?= htmlspecialchars($row['merchant_store_name'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['assigned_rider_name']) ?></td>
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
                                <div class="table-responsive pt-3">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Order Number</th>
                                                <th scope="col">Customer Name</th>
                                                <th scope="col">Merchant Store</th>
                                                <th scope="col">Order Description</th>
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
                                                if ($row['order_type'] === 'PABILI/PASUYO'):
                                            ?>
                                            <tr>
                                                <th scope="row"><?= $count++ ?></th>
                                                <td><?= htmlspecialchars($row['order_number']) ?></td>
                                                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                                <td><?= htmlspecialchars($row['merchant_store_name']) ?></td>
                                                <td><?= htmlspecialchars($row['order_description'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['assigned_rider_name']) ?></td>
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
                                                <td colspan="8" class="text-center">No PABILI/PASUYO orders found.
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- PAANGKAS Orders Tab -->
                            <div class="tab-pane fade" id="paangkas" role="tabpanel"
                                aria-labelledby="paangkas-tab">
                                <div class="table-responsive pt-3">
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
                                                if ($row['order_type'] === 'PAHATID/PASUNDO'):
                                            ?>
                                            <tr>
                                                <th scope="row"><?= $count++ ?></th>
                                                <td><?= htmlspecialchars($row['order_number']) ?></td>
                                                <td><?= htmlspecialchars($row['customer_name']) ?></td>
                                                <td><?= htmlspecialchars($row['vehicle_type'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($row['pickup_location']) ?></td>
                                                <td><?= htmlspecialchars($row['dropoff_address']) ?></td>
                                                <td><?= htmlspecialchars($row['assigned_rider_name']) ?></td>
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
                                                <td colspan="9" class="text-center">No PAHATID/PASUNDO orders
                                                    found.</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- PADALA Orders Tab -->
                            <div class="tab-pane fade" id="padala" role="tabpanel" aria-labelledby="padala-tab">
                                <div class="table-responsive pt-3">
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
                                                <td><?= htmlspecialchars($row['assigned_rider_name']) ?></td>
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
// Reset the result pointer for modals
mysqli_data_seek($result, 0);
while ($row = $result->fetch_assoc()):
?>
<!-- Modal for each order -->
<div class="modal fade" id="orderModal<?= urlencode($row['order_number']) ?>" tabindex="-1"
    aria-labelledby="orderModalLabel<?= urlencode($row['order_number']) ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Order Number:</div>
                    <div class="col-md-8"><?= htmlspecialchars($row['order_number']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Customer Name:</div>
                    <div class="col-md-8"><?= htmlspecialchars($row['customer_name']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Contact Number:</div>
                    <div class="col-md-8"><?= htmlspecialchars($row['contact_number'] ?? 'N/A') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Order Type:</div>
                    <div class="col-md-8">
                        <span class="badge bg-info"><?= htmlspecialchars($row['order_type']) ?></span>
                    </div>
                </div>

                <?php if ($row['order_type'] === 'PABILI/PASUYO'): ?>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Merchant Store:</div>
                    <div class="col-md-8"><?= htmlspecialchars($row['merchant_store_name'] ?? 'N/A') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Order Description:</div>
                    <div class="col-md-8"><?= htmlspecialchars($row['order_description'] ?? 'N/A') ?></div>
                </div>
                <?php endif; ?>

                <?php if ($row['order_type'] === 'PADALA'): ?>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Item Description:</div>
                    <div class="col-md-8"><?= htmlspecialchars($row['order_description'] ?? 'N/A') ?></div>
                </div>
                <?php endif; ?>

                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Vehicle Type:</div>
                    <div class="col-md-8"><?= htmlspecialchars($row['vehicle_type'] ?? 'N/A') ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Pickup Location:</div>
                    <div class="col-md-8"><?= htmlspecialchars($row['pickup_location']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Pickup Note:</div>
                    <div class="col-md-8"><?= htmlspecialchars($row['pickup_note'] ?? 'N/A') ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Delivery Location:</div>
                    <div class="col-md-8"><?= htmlspecialchars($row['dropoff_address']) ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Delivery Note:</div>
                    <div class="col-md-8"><?= htmlspecialchars($row['delivery_note'] ?? 'N/A') ?></div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Service Fee:</div>
                    <div class="col-md-8">
                        <span class="fw-bold text-primary">₱<?= number_format($row['service_fee'] ?? 0.0, 2) ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">PricelBadz Commission:</div>
                    <div class="col-md-8">
                        <span class="fw-bold text-success">₱<?= number_format($row['commission'] ?? 0.0, 2) ?></span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Assigned Rider:</div>
                    <div class="col-md-8">
                        <form method="POST" action="update_assigned_rider.php">
                            <input type="hidden" name="order_number"
                                value="<?= htmlspecialchars($row['order_number']) ?>">
                            <input type="hidden" name="order_type"
                                value="<?= htmlspecialchars($row['order_type']) ?>">
                            <select name="assigned_rider" class="form-select form-select" style="max-width: 250px;"
                                onchange="this.form.submit()">
                                <option value="">Select a rider</option>
                                <?php if ($row['assigned_rider']): ?>
                                <option value="<?= htmlspecialchars($row['assigned_rider']) ?>" selected>
                                    <?= htmlspecialchars($row['assigned_rider_name']) ?>
                                    (<?= htmlspecialchars($row['rider_vehicle_type']) ?>) -
                                    Contact: <?= htmlspecialchars($row['rider_contact_number']) ?> -
                                    Balance: ₱<?= number_format($row['rider_balance'], 2) ?>
                                </option>
                                <?php endif; ?>
                                <?php
                                // Get all available riders
                                $rider_query = 'SELECT id, first_name, middle_name, last_name, vehicle_type, topup_balance 
                                                                                                                                                                              FROM triders 
                                                                                                                                                                              WHERE NOT EXISTS (
                                                                                                                                                                                  SELECT 1 FROM (
                                                                                                                                                                                      SELECT assigned_rider FROM tpabili_orders WHERE order_status = "On-Going" AND assigned_rider IS NOT NULL
                                                                                                                                                                                      UNION ALL
                                                                                                                                                                                      SELECT assigned_rider FROM tpaangkas_orders WHERE order_status = "On-Going" AND assigned_rider IS NOT NULL
                                                                                                                                                                                      UNION ALL
                                                                                                                                                                                      SELECT assigned_rider FROM tpadala_orders WHERE order_status = "On-Going" AND assigned_rider IS NOT NULL
                                                                                                                                                                                  ) AS ongoing_orders 
                                                                                                                                                                                  WHERE ongoing_orders.assigned_rider = triders.id
                                                                                                                                                                              )
                                                                                                                                                                              AND (id != ? OR ? IS NULL)
                                                                                                                                                                              ORDER BY last_name, first_name';
                                
                                $rider_stmt = $conn->prepare($rider_query);
                                $rider_stmt->bind_param('ii', $row['assigned_rider'], $row['assigned_rider']);
                                $rider_stmt->execute();
                                $rider_result = $rider_stmt->get_result();
                                
                                if ($rider_result && $rider_result->num_rows > 0) {
                                    while ($rider = $rider_result->fetch_assoc()) {
                                        $full_name = $rider['first_name'];
                                        if (!empty($rider['middle_name'])) {
                                            $full_name .= ' ' . $rider['middle_name'];
                                        }
                                        $full_name .= ' ' . $rider['last_name'];
                                
                                        echo "<option value='" . htmlspecialchars($rider['id']) . "'>" . htmlspecialchars($full_name) . ' (' . htmlspecialchars($rider['vehicle_type']) . ')' . ' - Balance: ₱' . number_format($rider['topup_balance'], 2) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <?php if (!empty($row['rider_assigned_by']) && !empty($row['rider_assigned_at'])): ?>
                            <small class="text-muted d-block mt-1">
                                Last assigned by <?= htmlspecialchars($row['rider_assigned_by']) ?>
                                on <?= date('M d, Y h:i A', strtotime($row['rider_assigned_at'])) ?>
                            </small>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Status:</div>
                    <div class="col-md-8">
                        <form method="POST" action="update_order_status.php" class="d-inline">
                            <input type="hidden" name="order_number"
                                value="<?= htmlspecialchars($row['order_number']) ?>">
                            <input type="hidden" name="order_type"
                                value="<?= htmlspecialchars($row['order_type']) ?>">
                            <select name="order_status" class="form-select form-select d-inline-block w-auto"
                                onchange="this.form.submit()">
                                <option value="Pending" <?= $row['order_status'] === 'Pending' ? 'selected' : '' ?>>
                                    Pending</option>
                                <option value="On-Going" <?= $row['order_status'] === 'On-Going' ? 'selected' : '' ?>>
                                    On-Going</option>
                                <option value="Completed"
                                    <?= $row['order_status'] === 'Completed' ? 'selected' : '' ?>>
                                    Completed</option>
                                <option value="Cancelled"
                                    <?= $row['order_status'] === 'Cancelled' ? 'selected' : '' ?>>
                                    Cancelled</option>
                            </select>
                        </form>
                        <?php if (!empty($row['status_changed_at']) && !empty($row['status_changed_by'])): ?>
                        <small class="text-muted d-block mt-1">
                            Last changed by <?= htmlspecialchars($row['status_changed_by']) ?>
                            on <?= date('M d, Y h:i A', strtotime($row['status_changed_at'])) ?>
                        </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#paymentModal<?= urlencode($row['order_number']) ?>">
                    Update Payment
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Update Modal -->
<div class="modal fade" id="paymentModal<?= urlencode($row['order_number']) ?>" tabindex="-1"
    aria-labelledby="paymentModalLabel<?= urlencode($row['order_number']) ?>" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Update Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4 fw-bold">Order Number:</div>
                    <div class="col-md-8"><?= htmlspecialchars($row['order_number']) ?></div>
                </div>

                <form id="paymentForm<?= urlencode($row['order_number']) ?>" class="payment-form">
                    <input type="hidden" name="order_number" value="<?= htmlspecialchars($row['order_number']) ?>">
                    <input type="hidden" name="order_type" value="<?= htmlspecialchars($row['order_type']) ?>">

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Service Fee:</div>
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control service-fee-input"
                                    id="service_fee_<?= urlencode($row['order_number']) ?>" name="service_fee"
                                    step="0.01" min="0"
                                    value="<?= number_format($row['service_fee'] ?? 0.0, 2, '.', '') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">PricelBadz Commission:</div>
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control commission-input"
                                    id="commission_<?= urlencode($row['order_number']) ?>" name="commission"
                                    step="0.01" min="0"
                                    value="<?= number_format($row['commission'] ?? 0.0, 2, '.', '') ?>">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary update-fees-btn"
                    data-order-number="<?= htmlspecialchars($row['order_number']) ?>"
                    data-order-type="<?= htmlspecialchars($row['order_type']) ?>">
                    Update Payment
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<?php endwhile; ?>

<script>
    // Function to hide alerts after 3 seconds
    function hideAlerts() {
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');

        if (successAlert) {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(successAlert);
                bsAlert.close();
            }, 3000);
        }

        if (errorAlert) {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(errorAlert);
                bsAlert.close();
            }, 3000);
        }
    }

    // Call the function when the page loads
    document.addEventListener('DOMContentLoaded', hideAlerts);

    // Auto-calculate commission based on service fee
    document.addEventListener('DOMContentLoaded', function() {
        // Commission percentage (10%)
        const commissionPercentage = 0.10;

        // Add event listeners to all service fee inputs
        document.querySelectorAll('.service-fee-input').forEach(function(input) {
            input.addEventListener('input', function() {
                const serviceFee = parseFloat(this.value) || 0;
                const commission = serviceFee * commissionPercentage;

                // Get the commission input field from the same form
                const commissionInput = this.closest('form').querySelector('.commission-input');
                if (commissionInput) {
                    commissionInput.value = commission.toFixed(2);
                }
            });
        });

        // Add event listener to calculate commission buttons
        document.querySelectorAll('.calculate-commission').forEach(function(button) {
            button.addEventListener('click', function() {
                const form = this.closest('form');
                const serviceFeeInput = form.querySelector('.service-fee-input');
                const commissionInput = form.querySelector('.commission-input');

                const serviceFee = parseFloat(serviceFeeInput.value) || 0;
                const commission = serviceFee * commissionPercentage;
                commissionInput.value = commission.toFixed(2);
            });
        });

        // Add event listeners to update fees buttons
        document.querySelectorAll('.update-fees-btn').forEach(function(button) {
            button.addEventListener('click', function() {
                const orderNumber = this.getAttribute('data-order-number');
                const form = document.getElementById('paymentForm' + orderNumber);
                const formData = new FormData(form);

                // Send AJAX request
                fetch('update_order_fees.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.text();
                    })
                    .then(data => {
                        // Show success message
                        alert('Fees updated successfully!');
                        // Reload the page to show updated values
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error updating fees. Please try again.');
                    });
            });
        });
    });
</script>

<!-- Add jQuery for enhanced functionality -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Commission percentage (10%)
        const commissionPercentage = 0.10;

        // Function to calculate commission
        function calculateCommission(serviceFee) {
            return (parseFloat(serviceFee) * commissionPercentage).toFixed(2);
        }

        // Handle service fee input changes
        $('.service-fee-input').on('input', function() {
            const orderNumber = $(this).attr('id').split('_')[1];
            const serviceFee = $(this).val();
            const commission = calculateCommission(serviceFee);

            // Update the commission input
            $('#commission_' + orderNumber).val(commission);

            // Update the current value display
            $('#commission_' + orderNumber).closest('.col-md-8').find('.text-success').text(
                'Current value: ₱' + commission);
        });

        // Initialize commission values when payment modal is opened
        $('.btn-primary[data-bs-toggle="modal"]').on('click', function() {
            const targetModal = $(this).data('bs-target');
            const orderNumber = targetModal.split('_')[1];

            // Get the current service fee
            const serviceFee = $('#service_fee_' + orderNumber).val();

            // Calculate and set the commission
            const commission = calculateCommission(serviceFee);
            $('#commission_' + orderNumber).val(commission);
        });
    });
</script>

<?php
mysqli_close($conn);
?>