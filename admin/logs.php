<?php
include './header.php';
include '../config/connect.php';

// Initialize filter variables
$search = isset($_POST['search']) ? $_POST['search'] : '';
$rider_id = isset($_GET['rider_id']) ? $_GET['rider_id'] : null;

// Build the query with optional filters
$query = "SELECT t.*, 
          CONCAT(r.first_name, ' ', COALESCE(r.middle_name, ''), ' ', r.last_name) as fullname 
          FROM trider_topup_ledger t 
          JOIN triders r ON t.rider_id = r.id 
          WHERE 1=1";

if ($rider_id) {
    $query .= ' AND t.rider_id = ?';
}

if ($search) {
    $query .= ' AND (CONCAT(r.first_name, " ", COALESCE(r.middle_name, ""), " ", r.last_name) LIKE ? OR t.transaction_type LIKE ? OR t.order_number LIKE ?)';
}

$query .= ' ORDER BY t.created_at DESC';

// Prepare and execute the query
$stmt = $conn->prepare($query);
if ($rider_id && $search) {
    $search_term = '%' . $search . '%';
    $stmt->bind_param('isss', $rider_id, $search_term, $search_term, $search_term);
} elseif ($rider_id) {
    $stmt->bind_param('i', $rider_id);
} elseif ($search) {
    $search_term = '%' . $search . '%';
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
                        <h5 class="card-title">Transaction History</h5>
                        <form method="POST" class="row g-3">
                            <div class="col-md-10">
                                <input type="text" class="form-control" name="search" id="inputText"
                                    value="<?= htmlspecialchars($search) ?>"
                                    placeholder="Search by rider name, transaction type, or order number">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
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
                                        <th>#</th>
                                        <th>Rider</th>
                                        <th>Transaction</th>
                                        <th>Amount</th>
                                        <th>Processed By</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $count = 1;
                                    if ($result->num_rows > 0):
                                        while ($row = $result->fetch_assoc()):
                                            $amount_class = $row['transaction_type'] === 'Add Top-up' ? 'text-success' : 'text-danger';
                                            $amount_prefix = $row['transaction_type'] === 'Add Top-up' ? '+' : '-';
                                            $transaction_type = $row['transaction_type'];
                                            
                                            if ($row['order_number']) {
                                                $transaction_type .= ' (#' . $row['order_number'] . ')';
                                            }
                                    ?>
                                    <tr>
                                        <td><?= $count++ ?></td>
                                        <td><?= htmlspecialchars($row['fullname']) ?></td>
                                        <td><?= htmlspecialchars($transaction_type) ?></td>
                                        <td class="<?= $amount_class ?>">
                                            <?= $amount_prefix ?>₱<?= number_format($row['amount'], 2) ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['processed_by']) ?></td>
                                        <td><?= date('M d, Y h:i A', strtotime($row['created_at'])) ?></td>
                                    </tr>
                                    <?php
                                        endwhile;
                                    else:
                                    ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No transaction history found</td>
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
