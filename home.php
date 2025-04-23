<?php
include './config/connect.php';
include './controllers/CustomerOrderController.php';
include './controllers/RiderRegistrationController.php';

function sanitizeInput($data)
{
    return htmlspecialchars(trim($data));
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
</style>

<body>
    <div class="container container-fluid">
        <div class="row mt-5 justify-content-center my-8">
            <img src="./public/img/logo.png" class="w-50 py-4 px-0" alt="PricelBadz Logo">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-indicators">
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0"
                                    class="active" aria-current="true" aria-label="Slide 1"></button>
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"
                                    aria-label="Slide 2"></button>
                                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                                    aria-label="Slide 3"></button>
                            </div>
                            <div class="carousel-inner">
                                <div class="carousel-item active">
                                    <img src="./public/img/bg.jpg" class="d-block w-100" alt="...">
                                </div>
                                <div class="carousel-item">
                                    <img src="./public/img/bg.jpg" class="d-block w-100" alt="...">
                                </div>
                                <div class="carousel-item">
                                    <img src="./public/img/bg.jpg" class="d-block w-100" alt="...">
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
        <div class="d-flex flex-wrap justify-content-center gap-4 mt-4 mb-4">
            <div class="row w-100 justify-content-center">
                <!-- CUSTOMER ORDER FORM -->
                <div class="col-md-6">
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
                <div class="col-md-6">
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
        <div class="pb-3 text-center">
            <img src="./public/img/logo.png" class="w-25 mt-3" alt="PricalBadz Image">
            <nav style="--bs-breadcrumb-divider: '|';">
                <ol class="breadcrumb d-flex justify-content-center my-2">
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
</body>

</html>