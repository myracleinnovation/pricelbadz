<?php
include './header.php';
include '../config/connect.php';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new user
    if (isset($_POST['add_user'])) {
        $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
        $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $user_status = mysqli_real_escape_string($conn, $_POST['user_status']);

        // Check if username already exists
        $check_query = "SELECT id FROM tusers WHERE username = '$username'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $error_message = 'Username already exists. Please choose a different username.';
        } else {
            // Insert new user
            $insert_query = "INSERT INTO tusers (first_name, last_name, username, password, role, user_status, created_at) 
                            VALUES ('$first_name', '$last_name', '$username', '$password', '$role', '$user_status', NOW())";

            if (mysqli_query($conn, $insert_query)) {
                $success_message = 'User added successfully!';
            } else {
                $error_message = 'Error adding user: ' . mysqli_error($conn);
            }
        }
    }

    // Update user
    if (isset($_POST['update_user'])) {
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
        $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $user_status = mysqli_real_escape_string($conn, $_POST['user_status']);

        // Check if username already exists for other users
        $check_query = "SELECT id FROM tusers WHERE username = '$username' AND id != '$user_id'";
        $check_result = mysqli_query($conn, $check_query);

        if (mysqli_num_rows($check_result) > 0) {
            $error_message = 'Username already exists. Please choose a different username.';
        } else {
            // Update user
            $update_query = "UPDATE tusers SET 
                            first_name = '$first_name', 
                            last_name = '$last_name', 
                            username = '$username', 
                            role = '$role', 
                            user_status = '$user_status' 
                            WHERE id = '$user_id'";

            if (mysqli_query($conn, $update_query)) {
                $success_message = 'User updated successfully!';
            } else {
                $error_message = 'Error updating user: ' . mysqli_error($conn);
            }
        }
    }

    // Update password
    if (isset($_POST['update_password'])) {
        $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
        $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        // Update password
        $update_query = "UPDATE tusers SET password = '$new_password' WHERE id = '$user_id'";

        if (mysqli_query($conn, $update_query)) {
            $success_message = 'Password updated successfully!';
        } else {
            $error_message = 'Error updating password: ' . mysqli_error($conn);
        }
    }
}

// Initialize filter variables
$search = isset($_POST['search']) ? $_POST['search'] : '';
$status = isset($_POST['status']) ? $_POST['status'] : 'All Status';

// Build the query with optional filters
$query = "SELECT id, first_name, last_name, username, role, user_status, created_at 
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
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title p-0 m-0">Manage User Accounts</h5>
                            <div class="text-end mt-3">
                                <button type="button" class="btn btn-success" data-bs-toggle="modal"
                                    data-bs-target="#addUserModal">
                                    <i class="bi bi-plus-circle"></i> Add User
                                </button>
                            </div>
                        </div>

                        <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                        <?php endif; ?>

                        <form method="POST" class="row g-3">
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
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card" style="margin-top: 3rem;">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Full Name</th>
                                        <th scope="col">Username</th>
                                        <th scope="col">Role</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Created At</th>
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
                                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                        <td><?= htmlspecialchars($row['username']) ?></td>
                                        <td><?= htmlspecialchars($row['role']) ?></td>
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
                                        <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                                        <td>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#userModal<?= urlencode($row['id']) ?>">
                                                View
                                            </button>
                                            <button type="button" class="btn btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#updateUserModal<?= urlencode($row['id']) ?>">
                                                Edit
                                            </button>
                                            <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                                data-bs-target="#updatePasswordModal<?= urlencode($row['id']) ?>">
                                                Change Password
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
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['first_name']) ?>
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
                                                        <div class="col-md-4 fw-bold">Role:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['role']) ?>
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
                                                        <div class="col-md-4 fw-bold">Created At:</div>
                                                        <div class="col-md-8">
                                                            <?= date('Y-m-d', strtotime($row['created_at'])) ?>
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

                                    <!-- Update User Modal -->
                                    <div class="modal fade" id="updateUserModal<?= urlencode($row['id']) ?>"
                                        tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Update User</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="user_id"
                                                            value="<?= $row['id'] ?>">
                                                        <div class="mb-3">
                                                            <label for="first_name<?= $row['id'] ?>"
                                                                class="form-label">First Name</label>
                                                            <input type="text" class="form-control"
                                                                id="first_name<?= $row['id'] ?>" name="first_name"
                                                                value="<?= htmlspecialchars($row['first_name']) ?>"
                                                                required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="last_name<?= $row['id'] ?>"
                                                                class="form-label">Last Name</label>
                                                            <input type="text" class="form-control"
                                                                id="last_name<?= $row['id'] ?>" name="last_name"
                                                                value="<?= htmlspecialchars($row['last_name']) ?>"
                                                                required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="username<?= $row['id'] ?>"
                                                                class="form-label">Username</label>
                                                            <input type="text" class="form-control"
                                                                id="username<?= $row['id'] ?>" name="username"
                                                                value="<?= htmlspecialchars($row['username']) ?>"
                                                                required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="role<?= $row['id'] ?>"
                                                                class="form-label">Role</label>
                                                            <select class="form-select" id="role<?= $row['id'] ?>"
                                                                name="role" required>
                                                                <option value="Admin"
                                                                    <?= $row['role'] === 'Admin' ? 'selected' : '' ?>>
                                                                    Admin</option>
                                                                <option value="Staff"
                                                                    <?= $row['role'] === 'Staff' ? 'selected' : '' ?>>
                                                                    Staff</option>
                                                                <option value="Rider"
                                                                    <?= $row['role'] === 'Rider' ? 'selected' : '' ?>>
                                                                    Rider</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="user_status<?= $row['id'] ?>"
                                                                class="form-label">Status</label>
                                                            <select class="form-select"
                                                                id="user_status<?= $row['id'] ?>" name="user_status"
                                                                required>
                                                                <option value="Active"
                                                                    <?= $row['user_status'] === 'Active' ? 'selected' : '' ?>>
                                                                    Active</option>
                                                                <option value="Inactive"
                                                                    <?= $row['user_status'] === 'Inactive' ? 'selected' : '' ?>>
                                                                    Inactive</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_user"
                                                            class="btn btn-primary">Update User</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Update Password Modal -->
                                    <div class="modal fade" id="updatePasswordModal<?= urlencode($row['id']) ?>"
                                        tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold">Change Password</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="user_id"
                                                            value="<?= $row['id'] ?>">
                                                        <div class="mb-3">
                                                            <label for="new_password<?= $row['id'] ?>"
                                                                class="form-label">New Password</label>
                                                            <input type="password" class="form-control"
                                                                id="new_password<?= $row['id'] ?>" name="new_password"
                                                                required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="confirm_password<?= $row['id'] ?>"
                                                                class="form-label">Confirm Password</label>
                                                            <input type="password" class="form-control"
                                                                id="confirm_password<?= $row['id'] ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_password"
                                                            class="btn btn-primary">Update Password</button>
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
                                        <td colspan="7" class="text-center">No users found.</td>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="Admin">Admin</option>
                            <option value="Staff">Staff</option>
                            <option value="Rider">Rider</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="user_status" class="form-label">Status</label>
                        <select class="form-select" id="user_status" name="user_status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_user" class="btn btn-success">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Password confirmation validation
    document.addEventListener('DOMContentLoaded', function() {
        const passwordModals = document.querySelectorAll('[id^="updatePasswordModal"]');

        passwordModals.forEach(modal => {
            const form = modal.querySelector('form');
            const newPasswordInput = modal.querySelector('input[name="new_password"]');
            const confirmPasswordInput = modal.querySelector('input[id^="confirm_password"]');

            form.addEventListener('submit', function(event) {
                if (newPasswordInput.value !== confirmPasswordInput.value) {
                    event.preventDefault();
                    alert('Passwords do not match!');
                }
            });
        });
    });
</script>

<?php
mysqli_close($conn);
?>