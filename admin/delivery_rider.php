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
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert" id="successAlert">
                    <i class="bi bi-check-circle me-1"></i>
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); endif; ?>

                <?php if (isset($_SESSION['error_message'])): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert" id="errorAlert">
                    <i class="bi bi-exclamation-octagon me-1"></i>
                    <?= $_SESSION['error_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error_message']); endif; ?>

                <!-- Search and Filter Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Search and Filter</h5>
                        <form method="POST" class="row g-3">
                            <div class="col-md-7">
                                <div class="input-group shadow-sm">
                                    <input type="text" class="form-control" name="search" id="inputText"
                                        value="<?= htmlspecialchars($search) ?>"
                                        placeholder="Enter rider name or vehicle plate number">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="input-group shadow-sm">
                                    <select name="status" id="inputState" class="form-select">
                                        <option value="All Status" <?= $status === 'All Status' ? 'selected' : '' ?>>All
                                            Status</option>
                                        <option value="Active" <?= $status === 'Active' ? 'selected' : '' ?>>Active
                                        </option>
                                        <option value="Inactive" <?= $status === 'Inactive' ? 'selected' : '' ?>>
                                            Inactive
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100 shadow-sm">Search</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Riders List Card -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center">
                            Delivery Riders List
                            <span class="badge bg-primary text-white rounded-pill ms-2"><?= $result->num_rows ?>
                                riders</span>
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="text-center">#</th>
                                        <th scope="col">Full Name</th>
                                        <th scope="col">License</th>
                                        <th scope="col">Vehicle Info</th>
                                        <th scope="col" class="text-center">Balance</th>
                                        <th scope="col" class="text-center">Status</th>
                                        <th scope="col" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    if ($result->num_rows > 0):
                                        while ($row = $result->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <th scope="row" class="text-center"><?= $count++ ?></th>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="../assets/img/profile-img.jpg" alt="Profile"
                                                    class="rounded-circle me-2 shadow-sm" width="40">
                                                <div>
                                                    <span
                                                        class="fw-medium"><?= htmlspecialchars($row['fullname']) ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?= htmlspecialchars($row['license_number']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span
                                                    class="fw-medium"><?= htmlspecialchars($row['vehicle_type']) ?></span>
                                                <small class="text-muted">Plate:
                                                    <?= htmlspecialchars($row['vehicle_plate_number']) ?></small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge bg-success fs-6 shadow-sm">₱<?= number_format($row['topup_balance'], 2) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge <?= $row['rider_status'] === 'Active' ? 'bg-success' : 'bg-danger' ?> shadow-sm">
                                                <?= htmlspecialchars($row['rider_status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-primary shadow-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#riderModal<?= urlencode($row['id']) ?>">
                                                View
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Rider Details Modal -->
                                    <div class="modal fade" id="riderModal<?= urlencode($row['id']) ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content shadow">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">Rider Details</h5>
                                                    <button type="button" class="btn-close btn-close-white"
                                                        data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="row">
                                                        <!-- Left Column - Personal Info -->
                                                        <div class="col-md-6">
                                                            <div class="text-center mb-4">
                                                                <img src="../assets/img/profile-img.jpg"
                                                                    alt="Profile" class="rounded-circle shadow"
                                                                    width="120">
                                                                <h4 class="mt-3 mb-1 fw-bold">
                                                                    <?= htmlspecialchars($row['fullname']) ?></h4>
                                                                <span
                                                                    class="badge <?= $row['rider_status'] === 'Active' ? 'bg-success' : 'bg-danger' ?> mt-1 shadow-sm">
                                                                    <?= htmlspecialchars($row['rider_status']) ?>
                                                                </span>
                                                            </div>
                                                            <div class="rider-info bg-light p-3 rounded shadow-sm">
                                                                <h6 class="text-primary fw-bold mb-3">Personal
                                                                    Information</h6>
                                                                <div class="row g-3">
                                                                    <div class="col-6">
                                                                        <label class="fw-bold text-muted">First
                                                                            Name:</label>
                                                                        <p class="mb-0 fw-medium">
                                                                            <?= htmlspecialchars($row['first_name']) ?>
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="fw-bold text-muted">Middle
                                                                            Name:</label>
                                                                        <p class="mb-0 fw-medium">
                                                                            <?= htmlspecialchars($row['middle_name'] ?? 'N/A') ?>
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="fw-bold text-muted">Last
                                                                            Name:</label>
                                                                        <p class="mb-0 fw-medium">
                                                                            <?= htmlspecialchars($row['last_name']) ?>
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="fw-bold text-muted">License
                                                                            Number:</label>
                                                                        <p class="mb-0 fw-medium">
                                                                            <?= htmlspecialchars($row['license_number']) ?>
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label
                                                                            class="fw-bold text-muted">Status:</label>
                                                                        <p class="mb-0">
                                                                            <span
                                                                                class="badge <?= $row['rider_status'] === 'Active' ? 'bg-success' : 'bg-danger' ?> shadow-sm">
                                                                                <?= htmlspecialchars($row['rider_status']) ?>
                                                                            </span>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Right Column - Vehicle Info & Balance -->
                                                        <div class="col-md-6">
                                                            <div
                                                                class="vehicle-info mb-4 bg-light p-3 rounded shadow-sm">
                                                                <h6 class="text-primary fw-bold mb-3">Vehicle
                                                                    Information</h6>
                                                                <div class="row g-3">
                                                                    <div class="col-6">
                                                                        <label class="fw-bold text-muted">Vehicle
                                                                            Type:</label>
                                                                        <p class="mb-0 fw-medium">
                                                                            <?= htmlspecialchars($row['vehicle_type']) ?>
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="fw-bold text-muted">Vehicle
                                                                            COR:</label>
                                                                        <p class="mb-0 fw-medium">
                                                                            <?= htmlspecialchars($row['vehicle_cor']) ?>
                                                                        </p>
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <label class="fw-bold text-muted">Plate
                                                                            Number:</label>
                                                                        <p class="mb-0 fw-medium">
                                                                            <?= htmlspecialchars($row['vehicle_plate_number']) ?>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="balance-info bg-light p-3 rounded shadow-sm">
                                                                <h6 class="text-primary fw-bold mb-3">Balance
                                                                    Information</h6>
                                                                <div class="text-center">
                                                                    <h5 class="text-muted mb-2">Current Balance</h5>
                                                                    <h2 class="text-success mb-0 fw-bold">
                                                                        ₱<?= number_format($row['topup_balance'], 2) ?>
                                                                    </h2>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Action Buttons -->
                                                    <div class="text-center my-4">
                                                        <button type="button" class="btn btn-success shadow-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editTopupModal<?= urlencode($row['id']) ?>">
                                                            Manage Top-up
                                                        </button>
                                                        <button type="button"
                                                            class="btn btn-info text-white shadow-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editDetailsModal<?= urlencode($row['id']) ?>">
                                                            Edit Details
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Include other modals (Edit Top-up, Edit Details) here -->
                                    <?php include 'modals/rider_edit_topup_modal.php'; ?>
                                    <?php include 'modals/rider_edit_details_modal.php'; ?>

                                    <?php
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No riders found.</td>
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

<footer id="footer" class="footer">
    <div class="copyright">
        &copy; <?= date('Y') ?> <strong><span>PricelBadz</span></strong>. All Rights Reserved
    </div>
</footer>

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