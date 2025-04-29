<?php
// Edit Status Modal
?>
<div class="modal fade" id="editStatusModal<?= urlencode($row['id']) ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-toggle-on me-1"></i>
                    Edit Rider Status
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="update_rider_status.php" method="POST">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Changing rider status will affect their ability to receive new orders.
                    </div>
                    
                    <input type="hidden" name="rider_id" value="<?= htmlspecialchars($row['id']) ?>">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Rider ID:</label>
                        <p class="form-control-static"><?= htmlspecialchars($row['id']) ?></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Rider Name:</label>
                        <p class="form-control-static"><?= htmlspecialchars($row['fullname']) ?></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Current Status:</label>
                        <div>
                            <span class="badge <?= $row['rider_status'] === 'Active' ? 'bg-success' : 'bg-danger' ?> fs-6">
                                <i class="bi <?= $row['rider_status'] === 'Active' ? 'bi-check-circle' : 'bi-x-circle' ?> me-1"></i>
                                <?= htmlspecialchars($row['rider_status']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="new_status" class="form-label fw-bold">New Status:</label>
                        <select name="new_status" id="new_status" class="form-select" required>
                            <option value="">Select Status</option>
                            <option value="Active" <?= $row['rider_status'] === 'Active' ? 'selected' : '' ?>>
                                <i class="bi bi-check-circle"></i> Active
                            </option>
                            <option value="Inactive" <?= $row['rider_status'] === 'Inactive' ? 'selected' : '' ?>>
                                <i class="bi bi-x-circle"></i> Inactive
                            </option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 