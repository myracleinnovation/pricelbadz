<?php
include './header.php';
include '../config/connect.php';

// Initialize filter variables
$search = isset($_POST['search']) ? $_POST['search'] : '';

// Build the query with optional filters
$query = "SELECT id, merchant_name, merchant_description, merchant_logo 
    FROM tmerchants 
    WHERE merchant_name LIKE ?";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$search_term = '%' . $search . '%';
$stmt->bind_param('s', $search_term);
$stmt->execute();
$result = $stmt->get_result();
?>

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title">Manage Merchants</h5>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addMerchantModal">
                                <i class="bi bi-plus-circle me-1"></i> Add Merchant
                            </button>
                        </div>
                        <form method="POST" class="row g-3">
                            <div class="col-md-11">
                                <input type="text" class="form-control" name="search" id="inputText"
                                    value="<?= htmlspecialchars($search) ?>" placeholder="Enter merchant name">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary w-100">Search</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card shadow-sm mt-3">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Logo</th>
                                        <th scope="col">Merchant Name</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $count = 1;
                                        if ($result->num_rows > 0):
                                            while ($row = $result->fetch_assoc()):
                                                // Get additional images for this merchant
                                                $images_query = "SELECT * FROM tmerchant_images WHERE merchant_id = ? ORDER BY display_order";
                                                $images_stmt = $conn->prepare($images_query);
                                                $images_stmt->bind_param('i', $row['id']);
                                                $images_stmt->execute();
                                                $additional_images = $images_stmt->get_result();
                                                $has_additional_images = $additional_images->num_rows > 0;
                                    ?>
                                    <tr>
                                        <th scope="row"><?= $count++ ?></th>
                                        <td>
                                            <img src="../public/img/<?= htmlspecialchars($row['merchant_logo']) ?>"
                                                alt="<?= htmlspecialchars($row['merchant_name']) ?>"
                                                class="img-thumbnail" style="height: 50px; width: auto;">
                                        </td>
                                        <td><?= htmlspecialchars($row['merchant_name']) ?></td>
                                        <td><?= htmlspecialchars($row['merchant_description']) ?></td>
                                        <td>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#merchantModal<?= urlencode($row['id']) ?>">
                                                View
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Merchant Details Modal -->
                                    <div class="modal fade" id="merchantModal<?= urlencode($row['id']) ?>"
                                        tabindex="-1" aria-labelledby="merchantModalLabel<?= urlencode($row['id']) ?>"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold"
                                                        id="merchantModalLabel<?= urlencode($row['id']) ?>">Merchant
                                                        Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Merchant ID:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['id']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Merchant Name:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['merchant_name']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Description:</div>
                                                        <div class="col-md-8">
                                                            <?= htmlspecialchars($row['merchant_description']) ?>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4 fw-bold">Logo:</div>
                                                        <div class="col-md-8">
                                                            <img src="../public/img/<?= htmlspecialchars($row['merchant_logo']) ?>"
                                                                alt="<?= htmlspecialchars($row['merchant_name']) ?>"
                                                                class="img-thumbnail"
                                                                style="height: 100px; width: auto;">
                                                        </div>
                                                    </div>

                                                    <?php if ($has_additional_images): ?>
                                                    <div class="row mb-3">
                                                        <div class="col-12">
                                                            <h6 class="fw-bold">Additional Images</h6>
                                                            <div class="row g-3">
                                                                <?php 
                                                                while ($img = $additional_images->fetch_assoc()): 
                                                                ?>
                                                                <div class="col-md-4">
                                                                    <div class="card h-100">
                                                                        <img src="../public/img/<?= htmlspecialchars($img['image_path']) ?>"
                                                                            class="card-img-top"
                                                                            alt="<?= htmlspecialchars($img['image_description']) ?>"
                                                                            style="height: 200px; object-fit: cover;">
                                                                        <div class="card-body p-2">
                                                                            <p class="card-text small mb-0">
                                                                                <?= htmlspecialchars($img['image_description']) ?>
                                                                            </p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <?php endwhile; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editMerchantModal<?= urlencode($row['id']) ?>">
                                                        Edit Merchant
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Edit Merchant Modal -->
                                    <div class="modal fade" id="editMerchantModal<?= urlencode($row['id']) ?>"
                                        tabindex="-1"
                                        aria-labelledby="editMerchantModalLabel<?= urlencode($row['id']) ?>"
                                        aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title fw-bold"
                                                        id="editMerchantModalLabel<?= urlencode($row['id']) ?>">Edit
                                                        Merchant</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form action="update_merchant.php" method="POST"
                                                    enctype="multipart/form-data">
                                                    <div class="modal-body">
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Merchant ID:</div>
                                                            <div class="col-md-8">
                                                                <?= htmlspecialchars($row['id']) ?>
                                                                <input type="hidden" name="merchant_id"
                                                                    value="<?= htmlspecialchars($row['id']) ?>">
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Merchant Name:</div>
                                                            <div class="col-md-8">
                                                                <input type="text" class="form-control"
                                                                    name="merchant_name"
                                                                    value="<?= htmlspecialchars($row['merchant_name']) ?>"
                                                                    required>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Description:</div>
                                                            <div class="col-md-8">
                                                                <textarea class="form-control" name="merchant_description" rows="3" required><?= htmlspecialchars($row['merchant_description']) ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">Current Logo:</div>
                                                            <div class="col-md-8">
                                                                <img src="../public/img/<?= htmlspecialchars($row['merchant_logo']) ?>"
                                                                    alt="<?= htmlspecialchars($row['merchant_name']) ?>"
                                                                    class="img-thumbnail"
                                                                    style="height: 100px; width: auto;">
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-md-4 fw-bold">New Logo:</div>
                                                            <div class="col-md-8">
                                                                <input type="file" class="form-control"
                                                                    name="merchant_logo" accept="image/*">
                                                                <small class="text-muted">Leave empty to keep current
                                                                    logo</small>
                                                            </div>
                                                        </div>

                                                        <?php if ($has_additional_images): ?>
                                                        <div class="row mb-3">
                                                            <div class="col-12">
                                                                <h6 class="fw-bold">Additional Images</h6>
                                                                <div class="row">
                                                                    <?php 
                                                                    $additional_images->data_seek(0); // Reset pointer
                                                                    while ($img = $additional_images->fetch_assoc()): 
                                                                    ?>
                                                                    <div class="col-md-4 mb-2">
                                                                        <div class="card">
                                                                            <img src="../public/img/<?= htmlspecialchars($img['image_path']) ?>"
                                                                                class="card-img-top"
                                                                                alt="<?= htmlspecialchars($img['image_description']) ?>"
                                                                                style="height: 100px; object-fit: cover;">
                                                                            <div class="card-body p-2">
                                                                                <p class="card-text small">
                                                                                    <?= htmlspecialchars($img['image_description']) ?>
                                                                                </p>
                                                                                <div class="form-check">
                                                                                    <input class="form-check-input"
                                                                                        type="checkbox"
                                                                                        name="delete_images[]"
                                                                                        value="<?= $img['id'] ?>"
                                                                                        id="deleteImage<?= $img['id'] ?>">
                                                                                    <label
                                                                                        class="form-check-label small"
                                                                                        for="deleteImage<?= $img['id'] ?>">
                                                                                        Delete this image
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <?php endwhile; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row mb-3">
                                                            <div class="col-12">
                                                                <h6 class="fw-bold">Add New Images</h6>
                                                                <input type="file" class="form-control"
                                                                    name="additional_images[]" accept="image/*"
                                                                    multiple>
                                                                <small class="text-muted">You can select multiple
                                                                    images</small>
                                                            </div>
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Update
                                                            Merchant</button>
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
                                        <td colspan="5" class="text-center">No merchants found.</td>
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

<?php
include './footer.php';
mysqli_close($conn);
?>

<!-- Add Merchant Modal -->
<div class="modal fade" id="addMerchantModal" tabindex="-1" aria-labelledby="addMerchantModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="addMerchantModalLabel">Add New Merchant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="add_merchant.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Merchant Name:</div>
                        <div class="col-md-8">
                            <input type="text" class="form-control" name="merchant_name" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Description:</div>
                        <div class="col-md-8">
                            <textarea class="form-control" name="merchant_description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Logo:</div>
                        <div class="col-md-8">
                            <input type="file" class="form-control" name="merchant_logo" accept="image/*"
                                required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="fw-bold">Additional Images</h6>
                            <input type="file" class="form-control" name="additional_images[]" accept="image/*"
                                multiple>
                            <small class="text-muted">You can select multiple images</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Merchant</button>
                </div>
            </form>
        </div>
    </div>
</div>