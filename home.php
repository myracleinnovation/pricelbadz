<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Poppins', sans-serif;
    }

    body {
        background-image: url('assets/img/bg.jpg');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
    }

    .swiper {
        width: 100%;
        height: 100%;
    }

    .swiper-slide {
        text-align: center;
        font-size: 18px;
        background: #fff;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .swiper-slide img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

<body>
    <div class="container text-center mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <img src="assets/img/logo.png" class="w-25 mt-3" alt="PricalBadz Image">
            <li class="nav-item dropdown pe-3">
                <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
                    <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">
                    <span class="d-none d-md-block dropdown-toggle ps-2">K. Anderson</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                    <li class="dropdown-header">
                        <h6>Kevin Anderson</h6>
                        <span>Web Designer</span>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                            <i class="bi bi-person"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                            <i class="bi bi-gear"></i>
                            <span>Account Settings</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="pages-faq.html">
                            <i class="bi bi-question-circle"></i>
                            <span>Need Help?</span>
                        </a>
                    </li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Sign Out</span>
                        </a>
                    </li>
                </ul>
            </li>
        </div>
        <div class="card mt-4">
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
                            <img src="assets/img/bg.jpg" class="d-block w-100" alt="...">
                        </div>
                        <div class="carousel-item">
                            <img src="assets/img/bg.jpg" class="d-block w-100" alt="...">
                        </div>
                        <div class="carousel-item">
                            <img src="assets/img/bg.jpg" class="d-block w-100" alt="...">
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
                        data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
                        data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-center gap-4 mt-4 mb-4">
            <div class="card p-4 m-4" style="background-color: #0E76BC;">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-white my-4"><i class='bx bxs-notepad me-2'></i>Customer Order
                        Form
                    </h5>
                    <form class="row g-3">
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="floatingName" placeholder="Your Name">
                                <label for="floatingName">Name:</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="floatingContactNumber"
                                    placeholder="Contact Number">
                                <label for="floatingContactNumber">Contact Number:</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="floatingPickUp"
                                    placeholder="Pick Up Address">
                                <label for="floatingPickUp">Pick Up Address:</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="floatingDropOff"
                                    placeholder="Drop Off">
                                <label for="floatingDropOff">Drop off Address:</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="floatingDropOffContactPerson"
                                    placeholder="Drop Off Contact Person">
                                <label for="floatingDropOffContactPerson">Drop off Contact Person:</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="floatingDropOffContactNumber"
                                    placeholder="Drop Off Contact Number">
                                <label for="floatingDropOffContactNumber">Drop off Contact Number:</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" placeholder="Remarks" id="floatingRemarks" style="height: 100px;"></textarea>
                                <label for="floatingRemarks">Remarks:</label>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit"
                                class="btn btn-primary bg-white text-black py-2 px-4 rounded-pill border-none">Place
                                Order</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card p-4 m-4" style="background-color: #F26522;">
                <div class="card-body">
                    <h5 class="card-title fw-bold text-white my-4"><i class='bx bxs-car me-2'></i>Rider Registration
                        Form</h5>
                    <form class="row g-3">
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="floatingFirstName"
                                    placeholder="First Name">
                                <label for="floatingFirstName">First Name:</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="floatingMiddleName"
                                    placeholder="Middle Name">
                                <label for="floatingMiddleName">Middle Name:</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="floatingSurname"
                                    placeholder="Surname">
                                <label for="floatingSurname">Surname:</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="floatingLicenseNumber"
                                    placeholder="License Number">
                                <label for="floatingLicenseNumber">License Number:</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <input type="number" class="form-control" id="floatingVehiclePlateNumber"
                                    placeholder="Vehicle Plate Number">
                                <label for="floatingVehiclePlateNumber">Vehicle Plate Number:</label>
                            </div>
                        </div>
                        <div class="text-center">
                            <button type="submit"
                                class="btn btn-primary bg-white text-black py-2 px-4 rounded-pill border-none">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="pb-3">
            <img src="assets/img/logo.png" class="w-25 mt-3" alt="PricalBadz Image">
            <nav style="--bs-breadcrumb-divider: '|';">
                <ol class="breadcrumb d-flex justify-content-center my-2">
                    <li class="breadcrumb-item"><a href="index.html">Terms and Condition</a></li>
                    <li class="breadcrumb-item"><a href="#">Privacy Statement</a></li>
                </ol>
            </nav>
            <footer id="footer" class="footer">
                <div class="copyright">
                    &copy; 2025 <strong><span>PricelBadz</span></strong>
                </div>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        var swiper = new Swiper(".mySwiper", {
            pagination: {
                el: ".swiper-pagination",
            },
        });
    </script>
</body>

</html>
