<?php
include './header.php';
include '../config/connect.php';

// Initialize filter variables
$search = isset($_POST['search']) ? $_POST['search'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'All Status';

// Build the query with optional filters
$query = "SELECT id, first_name, middle_name, last_name, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS fullname, 
    license_number, vehicle_type, vehicle_plate_number, rider_status, topup_balance, vehicle_cor 
    FROM triders 
    WHERE (first_name LIKE ? OR last_name LIKE ? OR vehicle_plate_number LIKE ?)";

if ($status !== 'All Status') {
    $query .= ' AND rider_status = ?';
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
                        <h5 class="card-title">Manage Delivery Riders</h5>
                        <form method="POST" class="row g-3">
                            <div class="col-md-7">
                                <input type="text" class="form-control" name="search" id="inputText"
                                    value="<?= htmlspecialchars($search) ?>"
                                    placeholder="Enter rider name or vehicle plate number">
                            </div>
                            <div class="col-md-4">
                                <select name="status" id="inputState" class="form-select">
                                    <option value="All Status" <?= $status === 'All Status' ? 'selected' : '' ?>>All
                                        Status</option>
                                    <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active
                                    </option>
                                    <option value="Inactive" <?= $status === 'Inactive' ? 'selected' : '' ?>>Inactive
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
                                        <th scope="col">Full Name</th>
                                        <th scope="col">License Number</th>
                                        <th scope="col">Vehicle Type</th>
                                        <th scope="col">Vehicle Plate Number</th>
                                        <th scope="col">Status</th>
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
                                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                                        <td><?= htmlspecialchars($row['license_number']) ?></td>
                                        <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
                                        <td><?= htmlspecialchars($row['vehicle_plate_number']) ?></td>
                                        <td>
                                            <span class="badge <?php
                                            switch ($row['rider_status']) {
                                                case 'Active':
                                                    echo 'bg-success';
                                                    break;
                                                case 'Inactive':
                                                    echo 'bg-danger';
                                                    break;
                                                default:
                                                    echo 'bg-secondary';
                                            }
                                            ?>">
                                                <?= htmlspecialchars($row['rider_status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#riderModal<?= urlencode($row['id']) ?>">
                                                View
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Rider Details Modal -->
                                    <div class="modal fade" id="riderModal<?= urlencode($row['id']) ?>" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Rider Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">First Name:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['first_name']) ?></div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Middle Name:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['middle_name'] ?? 'N/A') ?></div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Last Name:</div>
                                                        <div class="col-md-8"><?= htmlspecialchars($row['last_name']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Full Name:</div>
                                                        <div class="col-md-8"><?= htmlspecialchars($row['fullname']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">License Number:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['license_number']) ?></div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Vehicle Type:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['vehicle_type']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Vehicle COR:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['vehicle_cor'] ?? 'N/A') ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Vehicle Plate Number:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['vehicle_plate_number']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Top-up Balance:</div>
                                                        <div class="col-md-8">
                                                            ₱<?= number_format($row['topup_balance'], 2) ?></div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Status:</div>
                                                        <div class="col-md-8">
                                                            <span class="badge <?php
                                                            switch ($row['rider_status']) {
                                                                case 'Active':
                                                                    echo 'bg-success';
                                                                    break;
                                                                case 'Inactive':
                                                                    echo 'bg-danger';
                                                                    break;
                                                                default:
                                                                    echo 'bg-secondary';
                                                            }
                                                            ?>">
                                                                <?= htmlspecialchars($row['rider_status']) ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-primary btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editStatusModal<?= urlencode($row['id']) ?>">
                                                        Edit Status
                                                    </button>
                                                    <button type="button" class="btn btn-success btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editTopupModal<?= urlencode($row['id']) ?>">
                                                        Edit Top-up Balance
                                                    </button>
                                                    <button type="button" class="btn btn-info btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editDetailsModal<?= urlencode($row['id']) ?>">
                                                        Edit Details
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Status Modal -->
                                    <div class="modal fade" id="editStatusModal<?= urlencode($row['id']) ?>"
                                        tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Rider Status</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form action="update_rider_status.php" method="POST">
                                                    <div class="modal-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Rider ID:</div>
                                                            <div class="col-md-8">
                                                                <?= htmlspecialchars($row['id']) ?>
                                                                <input type="hidden" name="rider_id"
                                                                    value="<?= htmlspecialchars($row['id']) ?>">
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Rider Name:</div>
                                                            <div class="col-md-8">
                                                                <?= htmlspecialchars($row['fullname']) ?>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Current Status:</div>
                                                            <div class="col-md-8">
                                                                <span class="badge <?php
                                                                switch ($row['rider_status']) {
                                                                    case 'Active':
                                                                        echo 'bg-success';
                                                                        break;
                                                                    case 'Inactive':
                                                                        echo 'bg-danger';
                                                                        break;
                                                                    default:
                                                                        echo 'bg-secondary';
                                                                }
                                                                ?>">
                                                                    <?= htmlspecialchars($row['rider_status']) ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">New Status:</div>
                                                            <div class="col-md-8">
                                                                <select name="new_status" class="form-select"
                                                                    required>
                                                                    <option value="">Select Status</option>
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

                                    <!-- Edit Top-up Balance Modal -->
                                    <div class="modal fade" id="editTopupModal<?= urlencode($row['id']) ?>"
                                        tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Top-up Balance</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form action="update_rider_topup.php" method="POST">
                                                    <div class="modal-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Rider ID:</div>
                                                            <div class="col-md-8">
                                                                <?= htmlspecialchars($row['id']) ?>
                                                                <input type="hidden" name="rider_id"
                                                                    value="<?= htmlspecialchars($row['id']) ?>">
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Rider Name:</div>
                                                            <div class="col-md-8">
                                                                <?= htmlspecialchars($row['fullname']) ?>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Current Balance:</div>
                                                            <div class="col-md-8">
                                                                ₱<?= number_format($row['topup_balance'], 2) ?>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">New Balance:</div>
                                                            <div class="col-md-8">
                                                                <input type="number" name="new_balance"
                                                                    class="form-control"
                                                                    value="<?= $row['topup_balance'] ?>"
                                                                    step="0.01" min="0" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success">Update
                                                            Balance</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Details Modal -->
                                    <div class="modal fade" id="editDetailsModal<?= urlencode($row['id']) ?>"
                                        tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Edit Rider Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form action="update_rider_details.php" method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="rider_id"
                                                            value="<?= htmlspecialchars($row['id']) ?>">

                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">First Name:</div>
                                                            <div class="col-md-8">
                                                                <input type="text" name="first_name"
                                                                    class="form-control"
                                                                    value="<?= htmlspecialchars($row['first_name']) ?>"
                                                                    required>
                                                            </div>
                                                        </div>

                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Middle Name:</div>
                                                            <div class="col-md-8">
                                                                <input type="text" name="middle_name"
                                                                    class="form-control"
                                                                    value="<?= htmlspecialchars($row['middle_name']) ?>">
                                                            </div>
                                                        </div>

                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Last Name:</div>
                                                            <div class="col-md-8">
                                                                <input type="text" name="last_name"
                                                                    class="form-control"
                                                                    value="<?= htmlspecialchars($row['last_name']) ?>"
                                                                    required>
                                                            </div>
                                                        </div>

                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">License Number:</div>
                                                            <div class="col-md-8">
                                                                <input type="text" name="license_number"
                                                                    class="form-control"
                                                                    value="<?= htmlspecialchars($row['license_number']) ?>"
                                                                    required>
                                                            </div>
                                                        </div>

                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Vehicle Type:</div>
                                                            <div class="col-md-8">
                                                                <select name="vehicle_type" class="form-select"
                                                                    required>
                                                                    <option value="">Select Vehicle Type</option>
                                                                    <option value="Motorcycle"
                                                                        <?= $row['vehicle_type'] === 'Motorcycle' ? 'selected' : '' ?>>
                                                                        Motorcycle</option>
                                                                    <option value="Tricycle"
                                                                        <?= $row['vehicle_type'] === 'Tricycle' ? 'selected' : '' ?>>
                                                                        Tricycle</option>
                                                                    <option value="Car"
                                                                        <?= $row['vehicle_type'] === 'Car' ? 'selected' : '' ?>>
                                                                        Car</option>
                                                                    <option value="Van"
                                                                        <?= $row['vehicle_type'] === 'Van' ? 'selected' : '' ?>>
                                                                        Van</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Vehicle COR:</div>
                                                            <div class="col-md-8">
                                                                <input type="text" name="vehicle_cor"
                                                                    class="form-control"
                                                                    value="<?= htmlspecialchars($row['vehicle_cor']) ?>"
                                                                    required>
                                                            </div>
                                                        </div>

                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Vehicle Plate Number:</div>
                                                            <div class="col-md-8">
                                                                <input type="text" name="vehicle_plate_number"
                                                                    class="form-control"
                                                                    value="<?= htmlspecialchars($row['vehicle_plate_number']) ?>"
                                                                    required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Update
                                                            Details</button>
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

<footer id="footer" class="footer fixed-bottom border-top py-3">
    <div class="container">
        <div class="copyright text-center">
            &copy; 2025 <strong><span>PricelBadz</span></strong>. All Rights Reserved
        </div>
    </div>
</footer>

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
</script>

<?php
mysqli_close($conn);
?>
