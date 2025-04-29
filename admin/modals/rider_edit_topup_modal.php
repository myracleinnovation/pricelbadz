<?php
// Rider Edit Topup Modal
?>
<div class="modal fade" id="editTopupModal<?= urlencode($row['id']) ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-wallet2 me-1"></i>
                    Manage Rider Balance
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="process_rider_topup.php" method="POST" id="topUpForm<?= $row['id'] ?>">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Choose whether to add or withdraw from the rider's current balance.
                    </div>

                    <input type="hidden" name="rider_id" value="<?= htmlspecialchars($row['id']) ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Rider Information:</label>
                        <div class="card bg-light">
                            <div class="card-body">
                                <p class="mb-1"><strong>ID:</strong> <?= htmlspecialchars($row['id']) ?></p>
                                <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($row['fullname']) ?></p>
                                <p class="mb-0"><strong>Current Balance:</strong> 
                                    <span class="text-success">₱<?= number_format($row['topup_balance'], 2) ?></span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Transaction Type:</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="transaction_type" id="topup<?= $row['id'] ?>" value="TOP_UP" checked>
                            <label class="form-check-label" for="topup<?= $row['id'] ?>">
                                Top Up (Add to balance)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="transaction_type" id="withdrawal<?= $row['id'] ?>" value="WITHDRAWAL">
                            <label class="form-check-label" for="withdrawal<?= $row['id'] ?>">
                                Withdrawal (Deduct from balance)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="topup_amount" class="form-label fw-bold">Amount (₱):</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number" 
                                   class="form-control" 
                                   id="topup_amount<?= $row['id'] ?>" 
                                   name="topup_amount" 
                                   min="1" 
                                   step="0.01" 
                                   required 
                                   placeholder="Enter amount">
                        </div>
                        <div class="form-text">Enter a valid amount greater than 0.</div>
                    </div>

                    <div class="mb-3">
                        <label for="topup_notes" class="form-label fw-bold">Notes:</label>
                        <textarea class="form-control" 
                                  id="topup_notes<?= $row['id'] ?>" 
                                  name="topup_notes" 
                                  rows="2" 
                                  placeholder="Enter any additional notes (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle me-1"></i>
                        Confirm Transaction
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('topUpForm<?= $row['id'] ?>').addEventListener('submit', function(e) {
    e.preventDefault();
    const amount = document.getElementById('topup_amount<?= $row['id'] ?>').value;
    const transactionType = document.querySelector('input[name="transaction_type"]:checked').value;
    const action = transactionType === 'TOP_UP' ? 'add to' : 'withdraw from';
    
    if (parseFloat(amount) <= 0) {
        alert('Please enter a valid amount greater than 0');
        return;
    }

    if (confirm('Are you sure you want to ' + action + ' this rider\'s balance ₱' + amount + '?')) {
        this.submit();
    }
});
</script> 