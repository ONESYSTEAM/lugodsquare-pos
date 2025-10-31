<?php
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($_ENV['APP_DESCRIPTION'] ?? '') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($_ENV['APP_KEYWORDS'] ?? '') ?>">
    <meta name="author" content="<?= htmlspecialchars($_ENV['APP_AUTHOR'] ?? '') ?>">

    <link rel="shortcut icon" href="<?= htmlspecialchars($_ENV['APP_ICON'] ?? '') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="<?= assets('vendors/mdi/css/materialdesignicons.min.css') ?>">
    <link rel="stylesheet" href="<?= assets('vendors/ti-icons/css/themify-icons.css') ?>">
    <link rel="stylesheet" href="<?= assets('vendors/css/vendor.bundle.base.css') ?>">
    <link rel="stylesheet" href="<?= assets('vendors/font-awesome/css/font-awesome.min.css') ?>">
    <link rel="stylesheet" href="<?= assets('vendors/font-awesome/css/font-awesome.min.css') ?>">
    <link rel="stylesheet" href="<?= assets('vendors/bootstrap-datepicker/bootstrap-datepicker.min.css') ?>">
    <link rel="stylesheet" href="<?= assets('css/style.css') ?>">

    <title><?= isset($title) && !empty($title) ? $this->e($title) : htmlspecialchars($_ENV['APP_NAME'] ?? '') ?></title>
</head>

<body>

    <div class="container-scroller">
        <nav class="navbar default-layout-navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
                <a class="navbar-brand brand-logo" href="/"><img src="<?= htmlspecialchars($_ENV['APP_LOGO'] ?? '') ?>" alt="logo" /></a>
                <a class="navbar-brand brand-logo-mini" href="/"><img src="<?= htmlspecialchars($_ENV['APP_LOGO_MINI'] ?? '') ?>" alt="logo" /></a>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-stretch">
                <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                    <span class="mdi mdi-menu"></span>
                </button>
                <div class="search-field d-none d-md-block">
                    <form class="d-flex align-items-center h-100" action="#">
                        <div class="input-group">
                            <div class="input-group-prepend bg-transparent">
                                <i class="input-group-text border-0 mdi mdi-magnify"></i>
                            </div>
                            <input type="text" class="form-control bg-transparent border-0" placeholder="Search projects">
                        </div>
                    </form>
                </div>
                <ul class="navbar-nav navbar-nav-right">
                    <li class="nav-item nav-profile dropdown">
                        <a class="nav-link dropdown-toggle" id="profileDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="nav-profile-img">
                                <img src="https://ui-avatars.com/api/?name=<?= $_SESSION['first_name'] . '+' . $_SESSION['last_name'] ?>" alt="profile" />
                            </div>
                            <div class="nav-profile-text">
                                <p class="mb-1 text-black"><?= $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] ?></p>
                            </div>
                        </a>
                        <div class="dropdown-menu navbar-dropdown" aria-labelledby="profileDropdown">
                            <a class="dropdown-item" href="#"><i class="mdi mdi-cached me-2 text-success"></i>Activity Log</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/logout"><i class="mdi mdi-logout me-2 text-primary"></i>Logout</a>
                        </div>
                    </li>
                    <li class="nav-item d-none d-lg-block full-screen-link">
                        <a class="nav-link">
                            <i class="mdi mdi-fullscreen" id="fullscreen-button"></i>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
                            <i class="mdi mdi-bell-outline"></i>
                            <span class="count-symbol bg-danger"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
                            <h6 class="p-3 mb-0">Notifications</h6>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item preview-item">
                                <div class="preview-thumbnail">
                                    <div class="preview-icon bg-success">
                                        <i class="mdi mdi-calendar"></i>
                                    </div>
                                </div>
                                <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                                    <h6 class="preview-subject font-weight-normal mb-1">Event today</h6>
                                    <p class="text-gray ellipsis mb-0"> Just a reminder that you have an event today </p>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item preview-item">
                                <div class="preview-thumbnail">
                                    <div class="preview-icon bg-warning">
                                        <i class="mdi mdi-cog"></i>
                                    </div>
                                </div>
                                <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                                    <h6 class="preview-subject font-weight-normal mb-1">Settings</h6>
                                    <p class="text-gray ellipsis mb-0"> Update dashboard </p>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item preview-item">
                                <div class="preview-thumbnail">
                                    <div class="preview-icon bg-info">
                                        <i class="mdi mdi-link-variant"></i>
                                    </div>
                                </div>
                                <div class="preview-item-content d-flex align-items-start flex-column justify-content-center">
                                    <h6 class="preview-subject font-weight-normal mb-1">Launch Admin</h6>
                                    <p class="text-gray ellipsis mb-0"> New admin wow! </p>
                                </div>
                            </a>
                            <div class="dropdown-divider"></div>
                            <h6 class="p-3 mb-0 text-center">See all notifications</h6>
                        </div>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                    <span class="mdi mdi-menu"></span>
                </button>
            </div>
        </nav>
        <div class="container-fluid page-body-wrapper">
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item nav-profile">
                        <a href="#" class="nav-link">
                            <div class="nav-profile-image">
                                <img src="https://ui-avatars.com/api/?name=<?= $_SESSION['first_name'] . '+' . $_SESSION['last_name'] ?>" alt="profile" />
                                <span class="login-status online"></span>
                            </div>
                            <div class="nav-profile-text d-flex flex-column">
                                <span class="font-weight-bold mb-2"><?= $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] ?></span>
                                <span class="text-secondary text-small">
                                    <?php
                                    if ($_SESSION['user_type'] == 1) {
                                        echo 'Administrator';
                                    } elseif ($_SESSION['user_type'] == 2) {
                                        echo 'Cashier';
                                    } else {
                                        echo 'User';
                                    }
                                    ?>
                                </span>
                            </div>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/">
                            <span class="menu-title">Dashboard</span>
                            <i class="mdi mdi-home menu-icon"></i>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#users" aria-expanded="false" aria-controls="users">
                            <span class="menu-title">Accounts</span>
                            <i class="menu-arrow"></i>
                            <i class="fa fa-address-book menu-icon fs-6"></i>
                        </a>
                        <div class="collapse" id="users">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item">
                                    <a class="nav-link" href="/users">Users</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/members">Members</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#booking" aria-expanded="false" aria-controls="booking">
                            <span class="menu-title">Booking</span>
                            <i class="menu-arrow"></i>
                            <i class="fa fa-calendar menu-icon fs-6 pb-1"></i>
                        </a>
                        <div class="collapse" id="booking">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item">
                                    <a class="nav-link" href="/courts">Courts</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="/schedules">Schedules</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="collapse" href="#pos" aria-expanded="false" aria-controls="pos">
                            <span class="menu-title">POS</span>
                            <i class="menu-arrow"></i>
                            <i class="fa fa-desktop menu-icon fs-6 pb-1"></i>
                        </a>
                        <div class="collapse" id="pos">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item">
                                    <a class="nav-link" href="#">Products</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">Inventory</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#">Sales</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </nav>
            <div class="main-panel">
                <div class="content-wrapper">
                    <?= $this->section('mainContent') ?>
                </div>
                <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">© <?= date('Y') ?> <a href="#" class="text-decoration-none text-danger">Lugod Square</a>. All rights reserved.</span>
                        <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">Powered by: <a href="https://onesysteam.com/" class="text-decoration-none text-danger" target="_blank">OneSysteam</a></span>
                    </div>
                </footer>
            </div>
        </div>
    </div>

    <script src="<?= assets("vendors/js/vendor.bundle.base.js") ?>"></script>
    <script src="<?= assets("vendors/chart.js/chart.umd.js") ?>"></script>
    <script src="<?= assets("vendors/bootstrap-datepicker/bootstrap-datepicker.min.js") ?>"></script>
    <script src="<?= assets("js/off-canvas.js") ?>"></script>
    <script src="<?= assets("js/misc.js") ?>"></script>
    <script src="<?= assets("js/settings.js") ?>"></script>
    <script src="<?= assets("js/todolist.js") ?>"></script>
    <script src="<?= assets("js/jquery.cookie.js") ?>"></script>
    <script src="<?= assets("js/dashboard.js") ?>"></script>
    <script src="<?= assets("js/Toasts.js") ?>"></script>

</body>

</html>