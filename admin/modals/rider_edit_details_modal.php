<?php
// Rider Edit Details Modal
?>
<div class="modal fade" id="editDetailsModal<?= urlencode($row['id']) ?>" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square me-1"></i>
                    Edit Rider Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form action="update_rider_details.php" method="POST" id="editDetailsForm<?= $row['id'] ?>">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-1"></i>
                        Update the rider's personal and vehicle information.
                    </div>

                    <input type="hidden" name="rider_id" value="<?= htmlspecialchars($row['id']) ?>">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Personal Information</label>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name<?= $row['id'] ?>"
                                    name="first_name" value="<?= htmlspecialchars($row['first_name']) ?>" required>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control" id="middle_name<?= $row['id'] ?>"
                                    name="middle_name" value="<?= htmlspecialchars($row['middle_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name<?= $row['id'] ?>"
                                    name="last_name" value="<?= htmlspecialchars($row['last_name']) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Status Information</label>
                        <div class="mb-2">
                            <label for="rider_status" class="form-label">Rider Status</label>
                            <select class="form-select" id="rider_status<?= $row['id'] ?>" name="rider_status" required>
                                <option value="Active" <?= $row['rider_status'] === 'Active' ? 'selected' : '' ?>>
                                    Active</option>
                                <option value="Inactive" <?= $row['rider_status'] === 'Inactive' ? 'selected' : '' ?>>
                                    Inactive</option>
                            </select>
                            <div class="form-text">Changing rider status will affect their ability to receive new
                                orders.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">License Information</label>
                        <div class="mb-2">
                            <label for="license_number" class="form-label">License Number</label>
                            <input type="text" class="form-control" id="license_number<?= $row['id'] ?>"
                                name="license_number" value="<?= htmlspecialchars($row['license_number']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Vehicle Information</label>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="vehicle_type" class="form-label">Vehicle Type</label>
                                <select class="form-select" id="vehicle_type<?= $row['id'] ?>" name="vehicle_type"
                                    required>
                                    <option value="">Select Vehicle Type</option>
                                    <option value="Motorcycle (1 seat)"
                                        <?= $row['vehicle_type'] === 'Motorcycle (1 seat)' ? 'selected' : '' ?>>Motorcycle (1 seat)
                                    </option>
                                    <option value="Tricycle (2-4 seats)"
                                        <?= $row['vehicle_type'] === 'Tricycle (2-4 seats)' ? 'selected' : '' ?>>Tricycle (2-4 seats)</option>
                                    <option value="Car (3-4 seats)" <?= $row['vehicle_type'] === 'Car (3-4 seats)' ? 'selected' : '' ?>>
                                        Car (3-4 seats)</option>
                                    <option value="Car (5-7 seats)" <?= $row['vehicle_type'] === 'Car (5-7 seats)' ? 'selected' : '' ?>>
                                        Car (5-7 seats)</option>
                                    <option value="Van (10-14 seats)" <?= $row['vehicle_type'] === 'Van (10-14 seats)' ? 'selected' : '' ?>>
                                        Van (10-14 seats)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="vehicle_plate_number" class="form-label">Plate Number</label>
                                <input type="text" class="form-control" id="vehicle_plate_number<?= $row['id'] ?>"
                                    name="vehicle_plate_number"
                                    value="<?= htmlspecialchars($row['vehicle_plate_number']) ?>" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="vehicle_cor" class="form-label">Vehicle COR</label>
                            <input type="text" class="form-control" id="vehicle_cor<?= $row['id'] ?>"
                                name="vehicle_cor" value="<?= htmlspecialchars($row['vehicle_cor']) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-info text-white">
                        <i class="bi bi-check-circle me-1"></i>
                        Update Details
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('editDetailsForm<?= $row['id'] ?>').addEventListener('submit', function(e) {
        e.preventDefault();

        // Basic validation
        const firstName = document.getElementById('first_name<?= $row['id'] ?>').value.trim();
        const lastName = document.getElementById('last_name<?= $row['id'] ?>').value.trim();
        const licenseNumber = document.getElementById('license_number<?= $row['id'] ?>').value.trim();
        const vehicleType = document.getElementById('vehicle_type<?= $row['id'] ?>').value;
        const plateNumber = document.getElementById('vehicle_plate_number<?= $row['id'] ?>').value.trim();
        const vehicleCor = document.getElementById('vehicle_cor<?= $row['id'] ?>').value.trim();
        const riderStatus = document.getElementById('rider_status<?= $row['id'] ?>').value;

        if (!firstName || !lastName || !licenseNumber || !vehicleType || !plateNumber || !vehicleCor || !
            riderStatus) {
            alert('Please fill in all required fields');
            return;
        }

        if (confirm('Are you sure you want to update this rider\'s details?')) {
            this.submit();
        }
    });
</script>