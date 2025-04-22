<?php
// Handle permission updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_permissions'])) {
    require_once '../config/connect.php';

    $user_id = $_POST['user_id'];
    $permissions = $_POST['permissions'] ?? [];

    // Convert permissions array to JSON
    $permissions_json = json_encode($permissions);

    // Update user permissions in database
    $update_query = 'UPDATE tusers SET permissions = ? WHERE id = ?';
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('si', $permissions_json, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Permissions updated successfully!';
    } else {
        $_SESSION['error_message'] = 'Error updating permissions: ' . $conn->error;
    }

    mysqli_close($conn);
    header('Location: account_settings.php');
    exit();
}

include 'header.php';
require_once '../config/connect.php';

// Fetch all users including permissions
$query = 'SELECT id, first_name, last_name, username, access_type, user_status, permissions FROM tusers ORDER BY first_name, last_name';
$result = mysqli_query($conn, $query);
$users = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Account Settings</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="analytics.php">Home</a></li>
                <li class="breadcrumb-item">Users</li>
                <li class="breadcrumb-item active">Account Settings</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">User Permissions Management</h5>
                        <p>Manage user access permissions for different sections of the system.</p>

                        <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= $_SESSION['success_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= $_SESSION['error_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Access Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['id']) ?></td>
                                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                        <td><?= htmlspecialchars($user['username']) ?></td>
                                        <td><?= htmlspecialchars($user['access_type']) ?></td>
                                        <td>
                                            <span
                                                class="badge <?= $user['user_status'] === 'Active' ? 'bg-success' : 'bg-danger' ?>">
                                                <?= htmlspecialchars($user['user_status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                                data-bs-target="#permissionsModal<?= $user['id'] ?>">
                                                <i class="bi bi-gear"></i> Edit Permissions
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Permissions Modals -->
<?php foreach ($users as $user): 
    $user_permissions = json_decode($user['permissions'] ?? '{}', true);
    $modules = [
        'analytics' => 'Analytics',
        'customers_order' => 'Customers Order',
        'delivery_rider' => 'Delivery Riders',
        'user_account' => 'User Accounts'
    ];
    $actions = ['create', 'read', 'update', 'delete'];
?>
<div class="modal fade" id="permissionsModal<?= $user['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Permissions for
                    <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="account_settings.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th class="text-center">
                                        Create
                                        <div class="form-check d-flex justify-content-center mt-2">
                                            <input type="checkbox" class="form-check-input check-all"
                                                data-action="create" data-modal="<?= $user['id'] ?>">
                                        </div>
                                    </th>
                                    <th class="text-center">
                                        Read
                                        <div class="form-check d-flex justify-content-center mt-2">
                                            <input type="checkbox" class="form-check-input check-all" data-action="read"
                                                data-modal="<?= $user['id'] ?>">
                                        </div>
                                    </th>
                                    <th class="text-center">
                                        Update
                                        <div class="form-check d-flex justify-content-center mt-2">
                                            <input type="checkbox" class="form-check-input check-all"
                                                data-action="update" data-modal="<?= $user['id'] ?>">
                                        </div>
                                    </th>
                                    <th class="text-center">
                                        Delete
                                        <div class="form-check d-flex justify-content-center mt-2">
                                            <input type="checkbox" class="form-check-input check-all"
                                                data-action="delete" data-modal="<?= $user['id'] ?>">
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($modules as $module_key => $module_name): ?>
                                <tr>
                                    <td><?= htmlspecialchars($module_name) ?></td>
                                    <?php foreach ($actions as $action): ?>
                                    <td class="text-center">
                                        <input type="checkbox" name="permissions[<?= $module_key ?>][<?= $action ?>]"
                                            value="1" class="form-check-input permission-checkbox"
                                            data-action="<?= $action ?>" data-modal="<?= $user['id'] ?>"
                                            <?= isset($user_permissions[$module_key][$action]) && $user_permissions[$module_key][$action] == 1 ? 'checked' : '' ?>>
                                    </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_permissions" class="btn btn-primary">Save Permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle check all functionality
        document.querySelectorAll('.check-all').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const action = this.dataset.action;
                const modalId = this.dataset.modal;
                const isChecked = this.checked;

                // Get all checkboxes for this action in this modal
                const checkboxes = document.querySelectorAll(
                    `.permission-checkbox[data-action="${action}"][data-modal="${modalId}"]`
                );
                checkboxes.forEach(cb => cb.checked = isChecked);
            });
        });

        // Set initial state of check-all checkboxes
        document.querySelectorAll('.check-all').forEach(checkbox => {
            const action = checkbox.dataset.action;
            const modalId = checkbox.dataset.modal;
            const checkboxes = document.querySelectorAll(
                `.permission-checkbox[data-action="${action}"][data-modal="${modalId}"]`);
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkbox.checked = allChecked;
        });
    });
</script>

<?php
mysqli_close($conn);
?>
