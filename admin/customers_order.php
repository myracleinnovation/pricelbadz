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
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Manage Customer Orders</h5>
                        <form method="POST" class="d-flex gap-2">
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
                                    <option value="In Progress" <?= $status === 'In Progress' ? 'selected' : '' ?>>In
                                        Progress</option>
                                    <option value="Completed" <?= $status === 'Completed' ? 'selected' : '' ?>>Completed
                                    </option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <table class="table table-striped">
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
                                    <td><?= htmlspecialchars($row['order_status']) ?></td>
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
                                                    <div class="col-md-8"><?= htmlspecialchars($row['order_number']) ?>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-4 fw-bold">Customer Name:</div>
                                                    <div class="col-md-8"><?= htmlspecialchars($row['customer_name']) ?>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-4 fw-bold">Merchant:</div>
                                                    <div class="col-md-8"><?= htmlspecialchars($row['merchant_name']) ?>
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
                                                    <div class="col-md-8"><?= htmlspecialchars($row['order_status']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Close</button>
                                            </div>
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
    </section>
</main>

<?php
mysqli_close($conn);
?>
