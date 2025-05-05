<?php
include './header.php';
include '../config/connect.php';

// Initialize filter variables
$search = isset($_POST['search']) ? $_POST['search'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'All Status';

// Build the query with optional filters
$query = "SELECT id, first_name, middle_name, last_name, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS fullname, 
    license_number, vehicle_type, vehicle_plate_number, rider_status, topup_balance, vehicle_cor, contact_number 
    FROM triders 
    WHERE (first_name LIKE ? OR last_name LIKE ? OR vehicle_plate_number LIKE ?)";

if ($status !== 'All Status') {
    $query .= ' AND rider_status = ?';
}

$query .= ' ORDER BY rider_status ASC, last_name ASC, first_name ASC';

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
                <!-- Alerts -->
                <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); endif; ?>

                <!-- Search and Filter Card -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Manage Delivery Riders</h5>
                        <form method="POST" class="row g-3">
                            <div class="col-md-7">
                                <input type="text" class="form-control form-control-sm" name="search" id="inputText"
                                    value="<?= htmlspecialchars($search) ?>"
                                    placeholder="Enter rider name or vehicle plate number">
                            </div>
                            <div class="col-md-4">
                                <select name="status" id="inputState" class="form-select form-select-sm">
                                    <option value="All Status" <?= $status === 'All Status' ? 'selected' : '' ?>>All
                                        Status</option>
                                    <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active
                                    </option>
                                    <option value="Inactive" <?= $status === 'Inactive' ? 'selected' : '' ?>>Inactive
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Riders List Card -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>License</th>
                                        <th>Vehicle</th>
                                        <th>Balance</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    if ($result->num_rows > 0):
                                        while ($row = $result->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?= $count++ ?></td>
                                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                                        <td><?= htmlspecialchars($row['license_number']) ?></td>
                                        <td>
                                            <?= htmlspecialchars($row['vehicle_type']) ?>
                                            <br>
                                            <small
                                                class="text-muted"><?= htmlspecialchars($row['vehicle_plate_number']) ?></small>
                                        </td>
                                        <td>₱<?= number_format($row['topup_balance'], 2) ?></td>
                                        <td>
                                            <span
                                                class="badge <?= $row['rider_status'] === 'Active' ? 'bg-success' : 'bg-danger' ?>">
                                                <?= htmlspecialchars($row['rider_status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#riderModal<?= urlencode($row['id']) ?>">
                                                    View
                                                </button>
                                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                                    data-bs-target="#editTopupModal<?= urlencode($row['id']) ?>">
                                                    Top-up
                                                </button>
                                                <a href="logs.php?rider_id=<?= urlencode($row['id']) ?>"
                                                    class="btn btn-info">
                                                    History
                                                </a>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Rider Details Modal -->
                                    <div class="modal fade" id="riderModal<?= urlencode($row['id']) ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Rider Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-2">
                                                        <div class="col-4 fw-bold">Name:</div>
                                                        <div class="col-8"><?= htmlspecialchars($row['fullname']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-4 fw-bold">License:</div>
                                                        <div class="col-8">
                                                            <?= htmlspecialchars($row['license_number']) ?></div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-4 fw-bold">Status:</div>
                                                        <div class="col-8">
                                                            <span
                                                                class="badge <?= $row['rider_status'] === 'Active' ? 'bg-success' : 'bg-danger' ?>">
                                                                <?= htmlspecialchars($row['rider_status']) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-4 fw-bold">Balance:</div>
                                                        <div class="col-8 text-success">
                                                            ₱<?= number_format($row['topup_balance'], 2) ?></div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-4 fw-bold">Vehicle Type:</div>
                                                        <div class="col-8">
                                                            <?= htmlspecialchars($row['vehicle_type']) ?></div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-4 fw-bold">Plate Number:</div>
                                                        <div class="col-8">
                                                            <?= htmlspecialchars($row['vehicle_plate_number']) ?></div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-4 fw-bold">Vehicle COR:</div>
                                                        <div class="col-8"><?= htmlspecialchars($row['vehicle_cor']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-2">
                                                        <div class="col-4 fw-bold">Contact Number:</div>
                                                        <div class="col-8">
                                                            <?= htmlspecialchars($row['contact_number']) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editRiderModal<?= urlencode($row['id']) ?>">
                                                        Edit Details
                                                    </button>
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Rider Modal -->
                                    <div class="modal fade" id="editRiderModal<?= urlencode($row['id']) ?>"
                                        tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Rider Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form action="update_rider_details.php" method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="rider_id"
                                                            value="<?= urlencode($row['id']) ?>">

                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label for="first_name" class="form-label">First
                                                                        Name</label>
                                                                    <input type="text" class="form-control"
                                                                        id="first_name" name="first_name"
                                                                        value="<?= htmlspecialchars($row['first_name']) ?>"
                                                                        required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="middle_name" class="form-label">Middle
                                                                        Name</label>
                                                                    <input type="text" class="form-control"
                                                                        id="middle_name" name="middle_name"
                                                                        value="<?= htmlspecialchars($row['middle_name']) ?>">
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="last_name" class="form-label">Last
                                                                        Name</label>
                                                                    <input type="text" class="form-control"
                                                                        id="last_name" name="last_name"
                                                                        value="<?= htmlspecialchars($row['last_name']) ?>"
                                                                        required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="license_number"
                                                                        class="form-label">License
                                                                        Number</label>
                                                                    <input type="text" class="form-control"
                                                                        id="license_number" name="license_number"
                                                                        value="<?= htmlspecialchars($row['license_number']) ?>"
                                                                        required>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label for="vehicle_type"
                                                                        class="form-label">Vehicle
                                                                        Type</label>
                                                                    <input type="text" class="form-control"
                                                                        id="vehicle_type" name="vehicle_type"
                                                                        value="<?= htmlspecialchars($row['vehicle_type']) ?>"
                                                                        required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="vehicle_plate_number"
                                                                        class="form-label">Vehicle Plate Number</label>
                                                                    <input type="text" class="form-control"
                                                                        id="vehicle_plate_number"
                                                                        name="vehicle_plate_number"
                                                                        value="<?= htmlspecialchars($row['vehicle_plate_number']) ?>"
                                                                        required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="vehicle_cor"
                                                                        class="form-label">Vehicle
                                                                        COR</label>
                                                                    <input type="text" class="form-control"
                                                                        id="vehicle_cor" name="vehicle_cor"
                                                                        value="<?= htmlspecialchars($row['vehicle_cor']) ?>"
                                                                        required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="contact_number"
                                                                        class="form-label">Contact Number</label>
                                                                    <input type="text" class="form-control"
                                                                        id="contact_number" name="contact_number"
                                                                        value="<?= htmlspecialchars($row['contact_number']) ?>"
                                                                        required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label for="rider_status"
                                                                        class="form-label">Status</label>
                                                                    <select class="form-select" id="rider_status"
                                                                        name="rider_status" required>
                                                                        <option value="Active"
                                                                            <?= $row['rider_status'] === 'Active' ? 'selected' : '' ?>>
                                                                            Active</option>
                                                                        <option value="Inactive"
                                                                            <?= $row['rider_status'] === 'Inactive' ? 'selected' : '' ?>>
                                                                            Inactive</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Save
                                                            Changes</button>
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Top-up Modal -->
                                    <div class="modal fade" id="editTopupModal<?= urlencode($row['id']) ?>"
                                        tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Manage Top-up</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form action="update_rider_topup.php" method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="rider_id"
                                                            value="<?= urlencode($row['id']) ?>">
                                                        <div class="row mb-2">
                                                            <div class="col-4 fw-bold">Current Balance:</div>
                                                            <div class="col-8 text-success">
                                                                ₱<?= number_format($row['topup_balance'], 2) ?></div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="topup_amount" class="form-label">Top-up
                                                                Amount</label>
                                                            <input type="number" class="form-control form-control-sm"
                                                                id="topup_amount" name="amount" step="0.01"
                                                                min="0">
                                                            <input type="hidden" name="transaction_type"
                                                                value="add">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="withdraw_amount" class="form-label">Withdraw
                                                                Amount</label>
                                                            <input type="number" class="form-control form-control-sm"
                                                                id="withdraw_amount" name="withdraw_amount"
                                                                step="0.01" min="0"
                                                                max="<?= $row['topup_balance'] ?>"
                                                                <?= $row['topup_balance'] <= 0 ? 'disabled' : '' ?>>
                                                            <small class="text-muted">Maximum:
                                                                ₱<?= number_format($row['topup_balance'], 2) ?></small>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="notes" class="form-label">Notes</label>
                                                            <input type="text" class="form-control form-control-sm"
                                                                id="notes" name="notes">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-primary">Submit</button>
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
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
                                        <td colspan="7" class="text-center">No riders found.</td>
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

<script>
    // Function to hide alerts after 3 seconds
    function hideAlerts() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 3000);
        });
    }

    // Call the function when the page loads
    document.addEventListener('DOMContentLoaded', hideAlerts);
</script>

<?php
mysqli_close($conn);
?>
