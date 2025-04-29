<?php
include './config/connect.php';
include './controllers/CustomerOrderController.php';
include './controllers/RiderRegistrationController.php';
include './controllers/OrderController.php';

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
        $service_type = sanitizeInput($_POST['service_type']);

        switch ($service_type) {
            case 'PABILI/PASUYO':
                $orderData = [
                    'customer_name' => sanitizeInput($_POST['name']),
                    'contact_number' => sanitizeInput($_POST['contact_number']),
                    'store_name' => sanitizeInput($_POST['store_name']),
                    'order_description' => sanitizeInput($_POST['order_description']),
                    'quantity' => (int) $_POST['quantity'],
                    'estimated_price' => (float) $_POST['estimated_price'],
                    'store_address' => sanitizeInput($_POST['pickup_address']),
                    'pickup_note' => sanitizeInput($_POST['pickup_note']),
                    'delivery_address' => sanitizeInput($_POST['dropoff_address']),
                    'delivery_note' => sanitizeInput($_POST['dropoff_note']),
                    'order_status' => 'Pending',
                    'service_fee' => 0.0,
                    'commission' => 0.0,
                ];
                createPabiliOrder($conn, $orderData['customer_name'], $orderData['contact_number'], $orderData['store_name'], $orderData['order_description'], $orderData['store_address'], $orderData['pickup_note'], $orderData['delivery_address'], $orderData['delivery_note'], null, $orderData['order_status'], $orderData['service_fee'], $orderData['commission']);
                $_SESSION['form_submitted'] = true;
                $_SESSION['form_type'] = 'PABILI/PASUYO Order';
                break;

            case 'PAHATID/PASUNDO':
                $orderData = [
                    'customer_name' => sanitizeInput($_POST['paangkas_name']),
                    'contact_number' => sanitizeInput($_POST['paangkas_contact_number']),
                    'pickup_address' => sanitizeInput($_POST['paangkas_pickup_address']),
                    'vehicle_type' => sanitizeInput($_POST['paangkas_vehicle_type']),
                    'pickup_note' => sanitizeInput($_POST['paangkas_pickup_note']),
                    'dropoff_address' => sanitizeInput($_POST['paangkas_dropoff_address']),
                    'dropoff_note' => sanitizeInput($_POST['paangkas_dropoff_note']),
                    'assigned_rider' => !empty($_POST['paangkas_assigned_rider']) ? $_POST['paangkas_assigned_rider'] : null,
                    'order_status' => 'Pending',
                    'service_fee' => 0.0,
                    'commission' => 0.0,
                ];
                createPaangkasOrder($conn, ...array_values($orderData));
                $_SESSION['form_submitted'] = true;
                $_SESSION['form_type'] = 'PAHATID/PASUNDO Order';
                break;

            case 'PADALA':
                $orderData = [
                    'customer_name' => sanitizeInput($_POST['padala_name']),
                    'contact_number' => sanitizeInput($_POST['padala_contact_number']),
                    'pickup_location' => sanitizeInput($_POST['padala_pickup_location']),
                    'order_description' => sanitizeInput($_POST['padala_order_description']),
                    'pickup_note' => sanitizeInput($_POST['padala_pickup_note']),
                    'dropoff_address' => sanitizeInput($_POST['padala_dropoff_address']),
                    'dropoff_note' => sanitizeInput($_POST['padala_dropoff_note']),
                    'assigned_rider' => !empty($_POST['padala_assigned_rider']) ? $_POST['padala_assigned_rider'] : null,
                    'order_status' => 'Pending',
                    'service_fee' => 0.0,
                    'commission' => 0.0,
                ];
                createPadalaOrder($conn, ...array_values($orderData));
                $_SESSION['form_submitted'] = true;
                $_SESSION['form_type'] = 'PADALA Order';
                break;
        }
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
        $_SESSION['form_submitted'] = true;
        $_SESSION['form_type'] = 'Rider Registration';
    }

    header('Location: home.php');
    exit();
}
?>

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>PricelBadz</title>
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

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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

    .pabili-fields,
    .paangkas-fields,
    .padala-fields {
        display: none !important;
        width: 100%;
    }

    .pabili-fields.show,
    .paangkas-fields.show,
    .padala-fields.show {
        display: flex !important;
    }

    /* Adjusted spacing styles */
    .container-fluid {
        padding: 0.5rem;
    }

    .row.mt-5 {
        margin-top: 1rem !important;
    }

    .my-8 {
        margin-top: 1rem !important;
        margin-bottom: 1rem !important;
    }

    .py-4 {
        padding-top: 0.5rem !important;
        padding-bottom: 0.5rem !important;
    }

    .mb-5 {
        margin-bottom: 1rem !important;
    }

    .mt-5 {
        margin-top: 1rem !important;
    }

    .mt-4 {
        margin-top: 0.5rem !important;
    }

    .my-4 {
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }

    .my-5 {
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }

    .gap-5 {
        gap: 1rem !important;
    }

    .gap-2 {
        gap: 0.5rem !important;
    }

    .p-4 {
        padding: 0.75rem !important;
    }

    .px-3 {
        padding-left: 0.5rem !important;
        padding-right: 0.5rem !important;
    }

    .pb-5 {
        padding-bottom: 1rem !important;
    }

    .w-50 {
        width: 40% !important;
    }

    .w-25 {
        width: 20% !important;
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
                <!-- PABILI FORM -->
                <div class="col-md-6 mb-5">
                    <div class="card p-4" style="background-color: #0E76BC;">
                        <div class="card-body p-0">
                            <h5 class="card-title fw-bold text-white my-4 text-center fs-4" id="formTitle">
                                <i class='bx bxs-notepad me-2'></i>Customer Order Form
                            </h5>
                            <form method="POST" class="row g-4 px-3">
                                <div class="col-12 px-0">
                                    <div class="form-floating">
                                        <select class="form-control" id="service_type" name="service_type" required>
                                            <option value="" disabled selected>Select Service Type</option>
                                            <option value="PAHATID/PASUNDO">Pahatid / Pasundo</option>
                                            <option value="PADALA">PADALA</option>
                                            <option value="PABILI/PASUYO">Pabili / Pasuyo</option>
                                        </select>
                                        <label for="service_type">Service Type</label>
                                    </div>
                                </div>

                                <!-- PABILI Fields -->
                                <div class="pabili-fields d-flex flex-wrap justify-content-center p-0 gap-2">
                                    <div class="col-12 d-flex flex-wrap justify-content-center gap-2">
                                        <div class="d-flex gap-2 col-12">
                                            <div class="form-floating flex-fill">
                                                <input type="text" class="form-control" id="name"
                                                    name="name" placeholder="Customer Name">
                                                <label for="name">Customer Name</label>
                                            </div>
                                            <div class="form-floating flex-fill">
                                                <input type="text" class="form-control" id="contact_number"
                                                    name="contact_number" placeholder="Contact Number">
                                                <label for="contact_number">Contact Number</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="store_name"
                                                name="store_name" placeholder="Merchant / Store Name">
                                            <label for="store_name">Merchant / Store Name</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="order_description" name="order_description" placeholder="Item Description"
                                                style="height: 100px;"></textarea>
                                            <label for="order_description">Order (Description, Quantity, Estimated
                                                Price)</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="pickup_address"
                                                name="pickup_address" placeholder="Store Address">
                                            <label for="pickup_address">Store Address</label>
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
                                                name="dropoff_address" placeholder="Delivery Address">
                                            <label for="dropoff_address">Delivery Address</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="dropoff_note" name="dropoff_note" placeholder="Dropoff Note"
                                                style="height: 80px;"></textarea>
                                            <label for="dropoff_note">Note to Rider (Delivery)</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- PAANGKAS Fields -->
                                <div class="paangkas-fields d-flex flex-wrap justify-content-center p-0 gap-2">
                                    <div class="col-12 d-flex flex-wrap justify-content-center gap-2">
                                        <div class="d-flex gap-2 col-12">
                                            <div class="form-floating flex-fill">
                                                <input type="text" class="form-control" id="paangkas_name"
                                                    name="paangkas_name" placeholder="Customer Name">
                                                <label for="paangkas_name">Customer Name</label>
                                            </div>
                                            <div class="form-floating flex-fill">
                                                <input type="text" class="form-control"
                                                    id="paangkas_contact_number" name="paangkas_contact_number"
                                                    placeholder="Contact Number">
                                                <label for="paangkas_contact_number">Contact Number</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="paangkas_pickup_address"
                                                name="paangkas_pickup_address" placeholder="Pickup Address">
                                            <label for="paangkas_pickup_address">Pickup Address</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <select class="form-control" id="paangkas_vehicle_type"
                                                name="paangkas_vehicle_type">
                                                <option value="" disabled selected>Select Vehicle Type</option>
                                                <option value="Motorcycle (1 seat)">Motorcycle (1 seat)</option>
                                                <option value="Tricycle (2-4 seats)">Tricycle (2-4 seats)</option>
                                                <option value="Car (3-4 seats)">Car (3-4 seats)</option>
                                                <option value="Car (5-7 seats)">Car (5-7 seats)</option>
                                                <option value="Van (10-14 seats)">Van (10-14 seats)</option>
                                            </select>
                                            <label for="paangkas_vehicle_type">Vehicle Type</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="paangkas_pickup_note" name="paangkas_pickup_note" placeholder="Pickup Note"
                                                style="height: 80px;"></textarea>
                                            <label for="paangkas_pickup_note">Note to Rider (Pickup)</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="paangkas_dropoff_address"
                                                name="paangkas_dropoff_address" placeholder="Drop-off Address">
                                            <label for="paangkas_dropoff_address">Drop-off Address</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="paangkas_dropoff_note" name="paangkas_dropoff_note" placeholder="Drop-off Note"
                                                style="height: 80px;"></textarea>
                                            <label for="paangkas_dropoff_note">Note to Rider (Drop-off)</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- PADALA Fields -->
                                <div class="padala-fields d-flex flex-wrap justify-content-center p-0 gap-2">
                                    <div class="col-12 d-flex flex-wrap justify-content-center gap-2">
                                        <div class="d-flex gap-2 col-12">
                                            <div class="form-floating flex-fill">
                                                <input type="text" class="form-control" id="padala_name"
                                                    name="padala_name" placeholder="Customer Name">
                                                <label for="padala_name">Customer Name</label>
                                            </div>
                                            <div class="form-floating flex-fill">
                                                <input type="text" class="form-control" id="padala_contact_number"
                                                    name="padala_contact_number" placeholder="Contact Number">
                                                <label for="padala_contact_number">Contact Number</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="padala_pickup_location"
                                                name="padala_pickup_location" placeholder="Pick-up Address">
                                            <label for="padala_pickup_location">Pick-up Address</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="padala_order_description" name="padala_order_description"
                                                placeholder="Item Description" style="height: 100px;"></textarea>
                                            <label for="padala_order_description">Item Description</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="padala_pickup_note" name="padala_pickup_note" placeholder="Pickup Note"
                                                style="height: 80px;"></textarea>
                                            <label for="padala_pickup_note">Note to Rider (Pickup)</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <input type="text" class="form-control" id="padala_dropoff_address"
                                                name="padala_dropoff_address" placeholder="Drop-off Address">
                                            <label for="padala_dropoff_address">Drop-off Address</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="padala_dropoff_note" name="padala_dropoff_note" placeholder="Drop-off Note"
                                                style="height: 80px;"></textarea>
                                            <label for="padala_dropoff_note">Note to Rider (Drop-off)</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" name="place_order"
                                        onclick="alert('Form submitted successfully!');"
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
                                            <option value="Motorcycle (1 seat)">Motorcycle (1 seat)</option>
                                            <option value="Tricycle (2-4 seats)">Tricycle (2-4 seats)</option>
                                            <option value="Car (3-4 seats)">Car (3-4 seats)</option>
                                            <option value="Car (5-7 seats)">Car (5-7 seats)</option>
                                            <option value="Van (10-14 seats)">Van (10-14 seats)</option>
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
                                        onclick="alert('Form submitted successfully!');"
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
                    <li class="breadcrumb-item"><a href="#" class="text-black" data-bs-toggle="modal"
                            data-bs-target="#termsModal">Terms and Condition</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-black" data-bs-toggle="modal"
                            data-bs-target="#privacyModal">Privacy Statement</a></li>
                </ol>
            </nav>
            <footer>
                <div class="copyright">
                    &copy; 2025 <strong><span>PricelBadz</span></strong>
                </div>
                <div class="social-links">
                    <a href="https://www.facebook.com/profile.php?id=61562797785885" target="_blank"><i
                            class='bx bxl-facebook-circle fs-3'></i></a>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Initially hide all service-specific fields
            $('.pabili-fields, .paangkas-fields, .padala-fields').removeClass('show');

            // Handle service type change
            $('#service_type').change(function() {
                const selectedService = $(this).val();

                // Hide all service-specific fields first
                $('.pabili-fields, .paangkas-fields, .padala-fields').removeClass('show');

                // Show fields based on selected service
                if (selectedService === 'PABILI/PASUYO') {
                    $('.pabili-fields').addClass('show');
                    $('#formTitle').html(
                        '<i class="bx bxs-notepad me-2"></i>Pabili / Pasuyo<br>Customer Order Form'
                    );
                } else if (selectedService === 'PAHATID/PASUNDO') {
                    $('.paangkas-fields').addClass('show');
                    $('#formTitle').html(
                        '<i class="bx bxs-notepad me-2"></i>Pahatid / Pasundo<br>Customer Order Form'
                    );
                } else if (selectedService === 'PADALA') {
                    $('.padala-fields').addClass('show');
                    $('#formTitle').html(
                        '<i class="bx bxs-notepad me-2"></i>Padala<br>Customer Order Form'
                    );
                } else {
                    $('#formTitle').html('<i class="bx bxs-notepad me-2"></i>Customer Order Form');
                }
            });

            // Trigger change event on page load to handle any pre-selected value
            $('#service_type').trigger('change');
        });

        // Existing marquee speed control code
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

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="terms-content">
                        <p>PricelBadz governs the use of its website and services through these Terms and
                            Conditions. Accessing or using the website constitutes agreement to these terms. Users who
                            do not agree should refrain from using the website.</p>

                        <h6 class="mt-4">1. Definitions</h6>
                        <p>1.1. "Company" refers to PricelBadz.</p>
                        <p>1.2. "Website" refers to https://www.pricelbadz.com/.</p>
                        <p>1.3. "User" or "You" refers to individuals accessing or using the website.</p>
                        <p>1.4. "Services" refers to software solutions, consulting, and business offerings provided by
                            PricelBadz.</p>

                        <h6 class="mt-4">2. Use of Website</h6>
                        <p>2.1. Users must be at least 18 years old to access services.</p>
                        <p>2.2. Users must utilize the website lawfully and refrain from fraudulent, abusive, or
                            malicious activities.</p>
                        <p>2.3. Unauthorized use, modification, or exploitation of the website is prohibited.</p>

                        <h6 class="mt-4">3. Intellectual Property Rights</h6>
                        <p>3.1. PricelBadz or its licensors own all website content, including text,
                            graphics, logos, and software.</p>
                        <p>3.2. Reproduction, distribution, or modification of website content without written consent
                            is prohibited.</p>

                        <h6 class="mt-4">4. Service Disclaimer</h6>
                        <p>4.1. Services are provided "as is" without warranties.</p>
                        <p>4.2. The website's availability, security, or error-free status is not guaranteed.</p>
                        <p>4.3. PricelBadz disclaims liability for loss or damage arising from the use of
                            its services.</p>

                        <h6 class="mt-4">5. User Accounts</h6>
                        <p>5.1. Accessing certain services requires account creation.</p>
                        <p>5.2. Users must maintain the confidentiality of account credentials.</p>
                        <p>5.3. PricelBadz reserves the right to suspend or terminate accounts violating
                            these terms.</p>

                        <h6 class="mt-4">6. Payment and Billing</h6>
                        <p>6.1. Users purchasing paid services must provide accurate billing information.</p>
                        <p>6.2. Payments are non-refundable unless otherwise specified.</p>
                        <p>6.3. PricelBadz reserves the right to modify pricing and payment terms.</p>

                        <h6 class="mt-4">7. Limitation of Liability</h6>
                        <p>7.1. PricelBadz is not liable for direct, indirect, incidental, or consequential
                            damages resulting from service use.</p>
                        <p>7.2. Users dissatisfied with services may discontinue use as the sole remedy.</p>

                        <h6 class="mt-4">8. Third-Party Links</h6>
                        <p>8.1. The website may contain third-party links, for which PricelBadz holds no
                            responsibility.</p>
                        <p>8.2. Users visit third-party sites at their own risk.</p>

                        <h6 class="mt-4">9. Termination</h6>
                        <p>9.1. PricelBadz reserves the right to terminate or suspend access to services
                            without notice.</p>
                        <p>9.2. Termination does not affect pre-existing rights or obligations.</p>

                        <h6 class="mt-4">10. Changes to Terms</h6>
                        <p>10.1. PricelBadz may update these Terms and Conditions at any time. Changes will
                            be posted on this page.</p>
                        <p>10.2. Continued website use after changes constitutes acceptance of revised terms.</p>

                        <h6 class="mt-4">11. Governing Law</h6>
                        <p>11.1. These terms are governed by and interpreted in accordance with the laws of the
                            Philippines.</p>

                        <h6 class="mt-4">12. Contact Information</h6>
                        <p>For inquiries regarding these Terms and Conditions, contact:</p>
                        <p>PricelBadz</p>
                        <p>Email: pricelbadz@gmail.com</p>

                        <p class="mt-4">By using the website, users acknowledge having read, understood, and agreed
                            to these Terms and Conditions.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Privacy Statement Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="privacyModalLabel">Privacy Statement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="terms-content">
                        <p>PricelBadz is committed to protecting the privacy of users accessing its website
                            and services. This Privacy Statement outlines how we collect, use, and safeguard personal
                            information. By using our website, users agree to the practices described in this statement.
                        </p>

                        <h6 class="mt-4">1. Information We Collect</h6>
                        <p><strong>Personal Information:</strong> We collect user-provided details such as name, email
                            address, phone number, and billing information.</p>
                        <p><strong>Non-Personal Information:</strong> We collect data such as IP address, browser type,
                            and device information to improve user experience.</p>
                        <p><strong>Cookies and Tracking Technologies:</strong> We use cookies to enhance website
                            functionality and gather analytical data.</p>

                        <h6 class="mt-4">2. How We Use Information</h6>
                        <ul>
                            <li>To provide and improve our services.</li>
                            <li>To communicate with users regarding inquiries, transactions, and updates.</li>
                            <li>To process payments and manage accounts.</li>
                            <li>To analyze website usage and enhance user experience.</li>
                            <li>To comply with legal obligations and protect against fraud or security threats.</li>
                        </ul>

                        <h6 class="mt-4">3. Information Sharing</h6>
                        <p>We do not sell, rent, or trade personal information.</p>
                        <p>Information may be shared with third-party service providers for payment processing, website
                            analytics, or customer support.</p>
                        <p>We may disclose information if required by law or to protect our rights and users' safety.
                        </p>

                        <h6 class="mt-4">4. Data Security</h6>
                        <p>We implement security measures to protect user data from unauthorized access or breaches.</p>
                        <p>While we strive for security, no system is entirely foolproof. Users should take precautions
                            to safeguard their credentials.</p>

                        <h6 class="mt-4">5. User Rights and Choices</h6>
                        <p>Users may access, update, or delete their personal information by contacting us.</p>
                        <p>Users can opt out of marketing communications at any time.</p>
                        <p>Cookie settings can be managed through browser preferences.</p>

                        <h6 class="mt-4">6. Third-Party Links</h6>
                        <p>Our website may contain links to external websites. We are not responsible for their privacy
                            practices.</p>
                        <p>Users should review third-party privacy policies before providing personal data.</p>

                        <h6 class="mt-4">7. Changes to this Privacy Statement</h6>
                        <p>We may update this Privacy Statement periodically. Changes will be posted on this page.</p>
                        <p>Continued website use after changes signifies acceptance of the revised statement.</p>

                        <h6 class="mt-4">8. Contact Information</h6>
                        <p>For inquiries regarding these Terms and Conditions, contact:</p>
                        <p>PricelBadz</p>
                        <p>Email: pricelbadz@gmail.com</p>

                        <p class="mt-4">By using this website, users acknowledge having read, understood, and agreed
                            to this Privacy Statement.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Support Icon -->
    <a href="https://www.facebook.com/messages/t/651650764687119" class="chat-support-icon" title="Chat Support"
        target="_blank">
        <i class='bx bxl-messenger'></i>
    </a>

    <style>
        .chat-support-icon {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #0E76BC;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .chat-support-icon i {
            font-size: 30px;
        }

        .chat-support-icon:hover {
            transform: scale(1.1);
            background-color: #0a5a94;
        }

        /* Terms and Conditions Modal Styles */
        .terms-content {
            max-height: 70vh;
            overflow-y: auto;
            padding-right: 10px;
        }

        .terms-content h6 {
            color: #0E76BC;
            font-weight: 600;
        }

        .terms-content p {
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }

        .terms-content::-webkit-scrollbar {
            width: 8px;
        }

        .terms-content::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .terms-content::-webkit-scrollbar-thumb {
            background: #0E76BC;
            border-radius: 10px;
        }

        .terms-content::-webkit-scrollbar-thumb:hover {
            background: #0a5a94;
        }
    </style>

    <?php if (isset($_SESSION['form_submitted']) && $_SESSION['form_submitted']): ?>
    <script>
        alert('Form submitted successfully!');
    </script>
    <?php
    // Clear the session variables after displaying the alert
    unset($_SESSION['form_submitted']);
    unset($_SESSION['form_type']);
    ?>
    <?php endif; ?>
</body>

</html>
