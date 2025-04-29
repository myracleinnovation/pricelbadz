<?php
// Get transaction history for the rider
$transaction_query = "SELECT rtl.*, u.username as admin_name 
                     FROM trider_topup_ledger rtl
                     LEFT JOIN tusers u ON rtl.processed_by = u.username
                     WHERE rtl.rider_id = ?
                     ORDER BY rtl.created_at DESC
                     LIMIT 10";

$transaction_stmt = $conn->prepare($transaction_query);
$transaction_stmt->bind_param('i', $row['id']);
$transaction_stmt->execute();
$transactions = $transaction_stmt->get_result();
?>

<!-- Transaction History Section -->
<div class="transaction-history mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h6 class="text-primary fw-bold mb-0">Transaction History</h6>
        </div>
        <div class="card-body p-0">
            <?php if ($transactions->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Previous Balance</th>
                            <th>New Balance</th>
                            <th>Notes</th>
                            <th>Processed By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($transaction = $transactions->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('M d, Y h:i A', strtotime($transaction['created_at'])) ?></td>
                            <td>
                                <span
                                    class="badge <?= strpos($transaction['transaction_type'], 'Add') !== false ? 'bg-success' : 'bg-danger' ?>">
                                    <?= $transaction['transaction_type'] ?>
                                </span>
                            </td>
                            <td
                                class="<?= strpos($transaction['transaction_type'], 'Add') !== false ? 'text-success' : 'text-danger' ?>">
                                <?= strpos($transaction['transaction_type'], 'Add') !== false ? '+' : '-' ?>₱<?= number_format($transaction['amount'], 2) ?>
                            </td>
                            <td>₱<?= number_format($transaction['previous_balance'], 2) ?></td>
                            <td>₱<?= number_format($transaction['current_balance'], 2) ?></td>
                            <td><?= htmlspecialchars($transaction['notes'] ?? '') ?></td>
                            <td><?= htmlspecialchars($transaction['processed_by']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="alert alert-info m-3">
                <i class="bi bi-info-circle me-1"></i>
                No transaction history found.
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$transaction_stmt->close();
?>