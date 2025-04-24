<?php
include './config/connect.php';
include './controllers/CustomerOrderController.php';
include './controllers/RiderRegistrationController.php';

function sanitizeInput($data)
{
    return htmlspecialchars(trim($data));
}

// Fetch all merchants from the database
$merchants_query = 'SELECT id, merchant_name, merchant_logo, merchant_description FROM tmerchants ORDER BY merchant_name';
$merchants_result = $conn->query($merchants_query);
$merchants = [];
if ($merchants_result && $merchants_result->num_rows > 0) {
    while ($row = $merchants_result->fetch_assoc()) {
        $merchants[] = $row;

        // Fetch additional images for each merchant
        $images_query = 'SELECT * FROM tmerchant_images WHERE merchant_id = ? ORDER BY display_order';
        $images_stmt = $conn->prepare($images_query);
        $images_stmt->bind_param('i', $row['id']);
        $images_stmt->execute();
        $additional_images = $images_stmt->get_result();
        $row['additional_images'] = [];

        if ($additional_images && $additional_images->num_rows > 0) {
            while ($img = $additional_images->fetch_assoc()) {
                $row['additional_images'][] = $img;
            }
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['place_order'])) {
        $orderData = [
            'customer_name' => sanitizeInput($_POST['name']),
            'contact_number' => sanitizeInput($_POST['contact_number']),
            'merchant_name' => sanitizeInput($_POST['merchant_name']),
            'pickup_address' => sanitizeInput($_POST['pickup_address']),
            'pickup_note' => sanitizeInput($_POST['pickup_note']),
            'order_description' => sanitizeInput($_POST['order_description']),
            'quantity' => (int) $_POST['quantity'],
            'estimated_price' => (float) $_POST['estimated_price'],
            'dropoff_address' => sanitizeInput($_POST['dropoff_address']),
            'dropoff_note' => sanitizeInput($_POST['dropoff_note']),
            'assigned_rider' => !empty($_POST['assigned_rider']) ? $_POST['assigned_rider'] : null,
            'order_status' => $_POST['order_status'] ?? 'Pending',
        ];

        createCustomerOrder($conn, ...array_values($orderData));
    }

    if (isset($_POST['submit'])) {
        $riderData = [
            'first_name' => sanitizeInput($_POST['first_name']),
            'middle_name' => sanitizeInput($_POST['middle_name']),
            'last_name' => sanitizeInput($_POST['last_name']),
            'license_number' => sanitizeInput($_POST['license_number']),
            'vehicle_type' => sanitizeInput($_POST['vehicle_type']),
            'vehicle_cor' => sanitizeInput($_POST['vehicle_cor']),
            'vehicle_plate' => sanitizeInput($_POST['vehicle_plate_number']),
            'topup_balance' => (float) ($_POST['topup_balance'] ?? 0.0),
            'rider_status' => $_POST['rider_status'] ?? 'Active',
        ];

        createRider($conn, ...array_values($riderData));
    }

    header('Location: home.php');
    exit();
}
?>

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>Dashboard - NiceAdmin Bootstrap Template</title>
    <meta name="robots" content="noindex, nofollow">
    <meta content="" name="description">
    <meta content="" name="keywords">

    <!-- Favicons -->
    <link href="./public/img/logo.png" rel="icon">
    <link href="./public/img/logo.png" rel="apple-touch-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">

    <!-- Vendor CSS Files -->
    <link href="./public/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="./public/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="./public/assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="./public/assets/vendor/quill/quill.snow.css" rel="stylesheet">
    <link href="./public/assets/vendor/quill/quill.bubble.css" rel="stylesheet">
    <link href="./public/assets/vendor/remixicon/remixicon.css" rel="stylesheet">
    <link href="./public/assets/vendor/simple-datatables/style.css" rel="stylesheet">

    <!-- Template Main CSS File -->
    <link href="./public/assets/css/style.css" rel="stylesheet">
</head>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        background-image: url('./public/img/bg.jpg');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
    }

    .marquee-container {
        position: relative;
        overflow: hidden;
        width: 100%;
    }

    .marquee-content {
        display: flex;
        animation: marquee 20s linear infinite;
        width: fit-content;
    }

    .marquee-content:hover {
        animation-play-state: paused;
    }

    @keyframes marquee {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(-50%);
        }
    }

    .marquee-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background-color: rgba(0, 0, 0, 0.5);
        color: white;
        border: none;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 10;
        transition: background-color 0.3s;
    }

    .marquee-btn:hover {
        background-color: rgba(0, 0, 0, 0.7);
    }

    .marquee-btn-left {
        left: 10px;
    }

    .marquee-btn-right {
        right: 10px;
    }

    .marquee-item {
        flex-shrink: 0;
    }

    .marquee-img {
        transition: transform 0.3s;
    }

    .marquee-img:hover {
        transform: scale(1.1);
    }
</style>

<body>
    <div class="container container-fluid">
        <div class="row mt-5 justify-content-center my-8">
            <img src="./public/img/logo.png" class="w-50 py-4 px-0" alt="PricelBadz Logo">
            <div class="col-12">
                <div class="card mb-5">
                    <div class="card-body p-0">
                        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel"
                            data-bs-interval="3000">
                            <div class="carousel-indicators">
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0"
                                    class="active" aria-current="true" aria-label="Slide 1"></button>
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                                    aria-label="Slide 2"></button>
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                                    aria-label="Slide 3"></button>
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="3"
                                    aria-label="Slide 4"></button>
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="4"
                                    aria-label="Slide 5"></button>
                            </div>
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="./public/img/PRICELBADZ_BANNER_1.png" class="d-block w-100"
                                        alt="...">
                                </div>
                                <div class="carousel-item">
                                    <img src="./public/img/PRICELBADZ_BANNER_2.png" class="d-block w-100"
                                        alt="...">
                                </div>
                                <div class="carousel-item">
                                    <img src="./public/img/PRICELBADZ_BANNER_3.png" class="d-block w-100"
                                        alt="...">
                                </div>
                                <div class="carousel-item">
                                    <img src="./public/img/PRICELBADZ_BANNER_4.png" class="d-block w-100"
                                        alt="...">
                                </div>
                                <div class="carousel-item">
                                    <img src="./public/img/PRICELBADZ_BANNER_5.png" class="d-block w-100"
                                        alt="...">
                                </div>
                            </div>
                            <button class="carousel-control-prev" type="button"
                                data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button"
                                data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Marquee Section -->
        <div class="row mb-5 mt-5">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="marquee-container bg-light py-3 position-relative">
                            <button class="marquee-btn marquee-btn-left" id="speedDown">
                                <i class="bx bx-chevron-left"></i>
                            </button>
                            <div class="marquee-content d-flex align-items-center" id="marqueeContent">
                                <!-- First set of images -->
                                <?php foreach ($merchants as $merchant): ?>
                                <div class="marquee-item px-4">
                                    <img src="./public/img/<?= htmlspecialchars($merchant['merchant_logo']) ?>"
                                        alt="<?= htmlspecialchars($merchant['merchant_name']) ?>" class="marquee-img"
                                        style="height: 100px; width: auto; border-radius: 8px; cursor: pointer;"
                                        data-bs-toggle="modal" data-bs-target="#merchantModal<?= $merchant['id'] ?>">
                                </div>
                                <?php endforeach; ?>

                                <!-- Duplicated set of images for seamless scrolling -->
                                <?php foreach ($merchants as $merchant): ?>
                                <div class="marquee-item px-4">
                                    <img src="./public/img/<?= htmlspecialchars($merchant['merchant_logo']) ?>"
                                        alt="<?= htmlspecialchars($merchant['merchant_name']) ?>" class="marquee-img"
                                        style="height: 100px; width: auto; border-radius: 8px; cursor: pointer;"
                                        data-bs-toggle="modal" data-bs-target="#merchantModal<?= $merchant['id'] ?>">
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="marquee-btn marquee-btn-right" id="speedUp">
                                <i class="bx bx-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap justify-content-center gap-5 mt-5 mb-5">
            <div class="row w-100 justify-content-center">
                <!-- CUSTOMER ORDER FORM -->
                <div class="col-md-6 mb-5">
                    <div class="card p-4" style="background-color: #0E76BC;">
                        <div class="card-body p-0">
                            <h5 class="card-title fw-bold text-white my-4 text-center fs-4">
                                <i class='bx bxs-notepad me-2'></i>Customer Order Form
                            </h5>
                            <form method="POST" class="row g-4 px-3">
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="name" name="name"
                                            placeholder="Customer Name">
                                        <label for="name">Customer Name</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="contact_number"
                                            name="contact_number" placeholder="Contact Number">
                                        <label for="contact_number">Contact Number</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <select class="form-control" id="merchant_name" name="merchant_name">
                                            <option value="PABILI">PABILI (Food Delivery / Item Delivery)</option>
                                            <option value="PAANGKAS">PAANGKAS (Pahatid / Pasundo)</option>
                                            <option value="PADALA">PADALA</option>
                                        </select>
                                        <label for="merchant_name">Merchant Name / Pick-up Location</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="order_description" name="order_description" placeholder="Order Description"
                                            style="height: 100px;"></textarea>
                                        <label for="order_description">Order Description</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" id="quantity" name="quantity"
                                            placeholder="Quantity">
                                        <label for="quantity">Quantity</label>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="form-floating">
                                        <input type="number" class="form-control" id="estimated_price"
                                            name="estimated_price" step="0.01" placeholder="Estimated Price">
                                        <label for="estimated_price">Estimated Price (₱)</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="pickup_address"
                                            name="pickup_address" placeholder="Pickup Address">
                                        <label for="pickup_address">Pick-up Address</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="pickup_note" name="pickup_note" placeholder="Pickup Note" style="height: 80px;"></textarea>
                                        <label for="pickup_note">Note to Rider (Pickup)</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="dropoff_address"
                                            name="dropoff_address" placeholder="Dropoff Address">
                                        <label for="dropoff_address">Drop-off Address</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea class="form-control" id="dropoff_note" name="dropoff_note" placeholder="Dropoff Note"
                                            style="height: 80px;"></textarea>
                                        <label for="dropoff_note">Note to Rider (Drop-off)</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <select class="form-control" id="assigned_rider" name="assigned_rider">
                                            <option value="">Select Rider</option>
                                            <?php
                                            // Get riders with positive balance
                                            $rider_query = "SELECT id, CONCAT(first_name, ' ', last_name) as rider_name, topup_balance 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          FROM triders 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          WHERE rider_status = 'Active' AND topup_balance > 0 
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          ORDER BY rider_name";
                                            $rider_result = $conn->query($rider_query);
                                            
                                            if ($rider_result && $rider_result->num_rows > 0) {
                                                while ($rider = $rider_result->fetch_assoc()) {
                                                    echo "<option value='" . $rider['rider_name'] . "'>" . $rider['rider_name'] . '</option>';
                                                }
                                            } else {
                                                echo "<option value='' disabled>No available riders</option>";
                                            }
                                            ?>
                                        </select>
                                        <label for="assigned_rider">Assigned Rider</label>
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" name="place_order"
                                        class="btn btn-light text-black py-3 px-5 rounded-pill border-none fs-5">
                                        Place Order
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- RIDER REGISTRATION FORM -->
                <div class="col-md-6 mb-5">
                    <div class="card p-4" style="background-color: #F26522;">
                        <div class="card-body p-0">
                            <h5 class="card-title fw-bold text-white my-4 text-center fs-4">
                                <i class='bx bxs-car me-2'></i>Rider Registration Form
                            </h5>
                            <form method="POST" class="row g-4 px-3">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="first_name" name="first_name"
                                            placeholder="First Name">
                                        <label for="first_name">First Name</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="middle_name"
                                            name="middle_name" placeholder="Middle Name">
                                        <label for="middle_name">Middle Name</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="last_name" name="last_name"
                                            placeholder="Last Name">
                                        <label for="last_name">Last Name</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="license_number"
                                            name="license_number" placeholder="License Number">
                                        <label for="license_number">License Number</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-control" id="vehicle_type" name="vehicle_type">
                                            <option value="" disabled selected>Select Vehicle Type</option>
                                            <option value="Motorcycle">Motorcycle</option>
                                            <option value="Tricycle">Tricycle</option>
                                            <option value="Car">Car</option>
                                        </select>
                                        <label for="vehicle_type">Vehicle Type</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="vehicle_cor"
                                            name="vehicle_cor" placeholder="Vehicle COR">
                                        <label for="vehicle_cor">Vehicle COR</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="vehicle_plate_number"
                                            name="vehicle_plate_number" placeholder="Vehicle Plate Number">
                                        <label for="vehicle_plate_number">Vehicle Plate Number</label>
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="submit" name="submit"
                                        class="btn btn-light text-black py-3 px-5 rounded-pill border-none fs-5">
                                        Submit
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="pb-5 text-center mt-5">
            <img src="./public/img/logo.png" class="w-25 mt-5" alt="PricelBadz Image">
            <nav style="--bs-breadcrumb-divider: '|';">
                <ol class="breadcrumb d-flex justify-content-center my-5">
                    <li class="breadcrumb-item"><a href="index.html" class="text-black">Terms and Condition</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-black">Privacy Statement</a></li>
                </ol>
            </nav>
            <footer>
                <div class="copyright">
                    &copy; 2025 <strong><span>PricelBadz</span></strong>
                </div>
            </footer>
        </div>
    </div>

    <!-- Vendor JS Files -->
    <script src="./public/assets/vendor/apexcharts/apexcharts.min.js"></script>
    <script src="./public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./public/assets/vendor/chart.js/chart.umd.js"></script>
    <script src="./public/assets/vendor/echarts/echarts.min.js"></script>
    <script src="./public/assets/vendor/quill/quill.js"></script>
    <script src="./public/assets/vendor/simple-datatables/simple-datatables.js"></script>
    <script src="./public/assets/vendor/tinymce/tinymce.min.js"></script>
    <script src="./public/assets/vendor/php-email-form/validate.js"></script>

    <!-- Template Main JS File -->
    <script src="./public/assets/js/main.js"></script>

    <script defer src="https://static.cloudflareinsights.com/beacon.min.js/vcd15cbe7772f49c399c6a5babf22c1241717689176015"
        integrity="sha512-ZpsOmlRQV6y907TI0dKBHq9Md29nnaEIPlkf84rnaERnq6zvWvPUqr2ft8M1aS28oN72PdrCzSjY4U6VaAw1EQ=="
        data-cf-beacon='{"rayId":"93419b31590b08d9","serverTiming":{"name":{"cfExtPri":true,"cfL4":true,"cfSpeedBrain":true,"cfCacheStatus":true}},"version":"2025.4.0-1-g37f21b1","token":"68c5ca450bae485a842ff76066d69420"}'
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Marquee speed control
        document.addEventListener('DOMContentLoaded', function() {
            const marqueeContent = document.getElementById('marqueeContent');
            const speedUpBtn = document.getElementById('speedUp');
            const speedDownBtn = document.getElementById('speedDown');

            // Get current animation duration
            let currentDuration = 20; // Default duration in seconds (faster)

            // Speed up button (decrease duration)
            speedUpBtn.addEventListener('click', function() {
                if (currentDuration > 5) {
                    currentDuration -= 5;
                    marqueeContent.style.animationDuration = currentDuration + 's';
                }
            });

            // Slow down button (increase duration)
            speedDownBtn.addEventListener('click', function() {
                if (currentDuration < 60) {
                    currentDuration += 5;
                    marqueeContent.style.animationDuration = currentDuration + 's';
                }
            });
        });
    </script>

    <!-- Merchant Modals -->
    <?php foreach ($merchants as $merchant): ?>
    <!-- <?= htmlspecialchars($merchant['merchant_name']) ?> Modal -->
    <div class="modal fade" id="merchantModal<?= $merchant['id'] ?>" tabindex="-1"
        aria-labelledby="merchantModalLabel<?= $merchant['id'] ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="merchantModalLabel<?= $merchant['id'] ?>">
                        <?= htmlspecialchars($merchant['merchant_name']) ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <p class="mb-0"><?= htmlspecialchars($merchant['merchant_description']) ?></p>
                        </div>
                    </div>
                    <div class="row g-3">
                        <!-- Main logo image -->
                        <div class="col-md-4">
                            <div class="card h-100">
                                <img src="./public/img/<?= htmlspecialchars($merchant['merchant_logo']) ?>"
                                    class="card-img-top" alt="<?= htmlspecialchars($merchant['merchant_name']) ?>"
                                    style="height: 200px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <p class="card-text small mb-0">Main Logo</p>
                                </div>
                            </div>
                        </div>

                        <!-- Additional images from tmerchant_images table -->
                        <?php 
                        // Fetch additional images for this merchant
                        $images_query = "SELECT * FROM tmerchant_images WHERE merchant_id = ? ORDER BY display_order";
                        $images_stmt = $conn->prepare($images_query);
                        $images_stmt->bind_param('i', $merchant['id']);
                        $images_stmt->execute();
                        $additional_images = $images_stmt->get_result();
                        
                        if ($additional_images && $additional_images->num_rows > 0):
                            while ($img = $additional_images->fetch_assoc()):
                        ?>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <img src="./public/img/<?= htmlspecialchars($img['image_path']) ?>"
                                    class="card-img-top" alt="<?= htmlspecialchars($img['image_description']) ?>"
                                    style="height: 200px; object-fit: cover;">
                                <div class="card-body p-2">
                                    <p class="card-text small mb-0"><?= htmlspecialchars($img['image_description']) ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <?php 
                            endwhile;
                        endif;
                        ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</body>

</html>
