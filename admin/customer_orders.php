<?php
include './header.php';
include '../config/connect.php';

// Initialize filter variables
$search = isset($_POST['search']) ? $_POST['search'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'All Status';

// Build the query with optional filters
$query = "SELECT order_number, customer_name, merchant_name, pickup_address, dropoff_address, assigned_rider, order_status 
        FROM tcustomer_order 
        WHERE (order_number LIKE ? OR customer_name LIKE ? OR assigned_rider LIKE ?)";

if ($status !== 'All Status') {
    $query .= ' AND order_status = ?';
}

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
                                    <option value="Assigned" <?= $status === 'Assigned' ? 'selected' : '' ?>>Assigned
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
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Order Number</th>
                                        <th scope="col">Customer Name</th>
                                        <th scope="col">Merchant</th>
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
                                        <td><?= htmlspecialchars($row['merchant_name']) ?></td>
                                        <td><?= htmlspecialchars($row['assigned_rider']) ?></td>
                                        <td>
                                            <span class="badge <?php
                                            switch ($row['order_status']) {
                                                case 'Pending':
                                                    echo 'bg-warning';
                                                    break;
                                                case 'Assigned':
                                                    echo 'bg-info';
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
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#orderModal<?= urlencode($row['order_number']) ?>">
                                                View
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Order Details Modal -->
                                    <div class="modal fade" id="orderModal<?= urlencode($row['order_number']) ?>"
                                        tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Order Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Order Number:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['order_number']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Customer Name:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['customer_name']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Merchant:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['merchant_name']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Pickup Address:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['pickup_address']) ?></div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Dropoff Address:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['dropoff_address']) ?></div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Assigned Rider:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['assigned_rider'] ?? 'Not assigned') ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Order Status:</div>
                                                        <div class="col-md-8">
                                                            <span class="badge <?php
                                                            switch ($row['order_status']) {
                                                                case 'Pending':
                                                                    echo 'bg-warning';
                                                                    break;
                                                                case 'Assigned':
                                                                    echo 'bg-info';
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
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editStatusModal<?= urlencode($row['order_number']) ?>">
                                                        Edit Status
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Status Modal -->
                                    <div class="modal fade" id="editStatusModal<?= urlencode($row['order_number']) ?>"
                                        tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Order Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form action="update_order_status.php" method="POST">
                                                    <div class="modal-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Order Number:</div>
                                                            <div class="col-md-8">
                                                                <?= htmlspecialchars($row['order_number']) ?>
                                                                <input type="hidden" name="order_number"
                                                                    value="<?= htmlspecialchars($row['order_number']) ?>">
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Current Status:</div>
                                                            <div class="col-md-8">
                                                                <span class="badge <?php
                                                                switch ($row['order_status']) {
                                                                    case 'Pending':
                                                                        echo 'bg-warning';
                                                                        break;
                                                                    case 'Assigned':
                                                                        echo 'bg-info';
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
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">New Status:</div>
                                                            <div class="col-md-8">
                                                                <select name="new_status" class="form-select"
                                                                    required>
                                                                    <option value="">Select Status</option>
                                                                    <option value="Pending"
                                                                        <?= $row['order_status'] === 'Pending' ? 'selected' : '' ?>>
                                                                        Pending</option>
                                                                    <option value="Assigned"
                                                                        <?= $row['order_status'] === 'Assigned' ? 'selected' : '' ?>>
                                                                        Assigned</option>
                                                                    <option value="Completed"
                                                                        <?= $row['order_status'] === 'Completed' ? 'selected' : '' ?>>
                                                                        Completed</option>
                                                                    <option value="Cancelled"
                                                                        <?= $row['order_status'] === 'Cancelled' ? 'selected' : '' ?>>
                                                                        Cancelled</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Update
                                                            Status</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                            endwhile;
                                        else:
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No orders found.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
