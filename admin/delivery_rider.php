<?php
include './header.php';
include '../config/connect.php';

// Initialize filter variables
$search = isset($_POST['search']) ? $_POST['search'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'All Status';

// Build the query with optional filters
$query = "SELECT id, first_name, middle_name, last_name, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) AS fullname, 
    license_number, vehicle_type, vehicle_plate_number, rider_status 
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
                        <form method="POST" class="d-flex gap-2">
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
                                    <th scope="col">First Name</th>
                                    <th scope="col">Last Name</th>
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
                                    <td><?= htmlspecialchars($row['first_name']) ?></td>
                                    <td><?= htmlspecialchars($row['last_name']) ?></td>
                                    <td><?= htmlspecialchars($row['fullname']) ?></td>
                                    <td><?= htmlspecialchars($row['license_number']) ?></td>
                                    <td><?= htmlspecialchars($row['vehicle_type']) ?></td>
                                    <td><?= htmlspecialchars($row['vehicle_plate_number']) ?></td>
                                    <td><?= htmlspecialchars($row['rider_status']) ?></td>
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
                                                    <div class="col-md-8"><?= htmlspecialchars($row['first_name']) ?>
                                                    </div>
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
                                                    <div class="col-md-8"><?= htmlspecialchars($row['vehicle_type']) ?>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-4 fw-bold">Vehicle Plate Number:</div>
                                                    <div class="col-md-8">
                                                        <?= htmlspecialchars($row['vehicle_plate_number']) ?></div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-4 fw-bold">Status:</div>
                                                    <div class="col-md-8"><?= htmlspecialchars($row['rider_status']) ?>
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
                                    <td colspan="9" class="text-center">No riders found.</td>
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
