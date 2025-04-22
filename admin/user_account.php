<?php
include './header.php';
include '../config/connect.php';

// Initialize filter variables
$search = isset($_POST['search']) ? $_POST['search'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'All Status';

// Build the query with optional filters
$query = "SELECT id, first_name, last_name, username, access_type, user_status, date_registered 
    FROM tusers 
    WHERE (first_name LIKE ? OR last_name LIKE ? OR username LIKE ?)";

if ($status !== 'All Status') {
    $query .= ' AND user_status = ?';
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
                        <h5 class="card-title">Manage User Accounts</h5>
                        <form method="POST" class="d-flex gap-2">
                            <div class="col-md-7">
                                <input type="text" class="form-control" name="search" id="inputText"
                                    value="<?= htmlspecialchars($search) ?>"
                                    placeholder="Enter first name, last name, or username">
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
                                    <th scope="col">Username</th>
                                    <th scope="col">Access Type</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Date Registered</th>
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
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td><?= htmlspecialchars($row['access_type']) ?></td>
                                    <td>
                                        <span class="badge <?php
                                        switch ($row['user_status']) {
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
                                            <?= htmlspecialchars($row['user_status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('Y-m-d', strtotime($row['date_registered'])) ?></td>
                                    <td>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#userModal<?= urlencode($row['id']) ?>">
                                            View
                                        </button>
                                    </td>
                                </tr>

                                <!-- User Details Modal -->
                                <div class="modal fade" id="userModal<?= urlencode($row['id']) ?>" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">User Details</h5>
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
                                                    <div class="col-md-4 fw-bold">Last Name:</div>
                                                    <div class="col-md-8"><?= htmlspecialchars($row['last_name']) ?>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-4 fw-bold">Username:</div>
                                                    <div class="col-md-8"><?= htmlspecialchars($row['username']) ?>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-4 fw-bold">Access Type:</div>
                                                    <div class="col-md-8"><?= htmlspecialchars($row['access_type']) ?>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-4 fw-bold">Status:</div>
                                                    <div class="col-md-8">
                                                        <span class="badge <?php
                                                        switch ($row['user_status']) {
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
                                                            <?= htmlspecialchars($row['user_status']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-4 fw-bold">Date Registered:</div>
                                                    <div class="col-md-8">
                                                        <?= date('Y-m-d', strtotime($row['date_registered'])) ?></div>
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
                                    <td colspan="8" class="text-center">No users found.</td>
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

<footer id="footer" class="footer fixed-bottom border-top py-3">
    <div class="container">
        <div class="copyright text-center">
            &copy; 2025 <strong><span>PricelBadz</span></strong>. All Rights Reserved
        </div>
    </div>
</footer>

<?php
mysqli_close($conn);
?>