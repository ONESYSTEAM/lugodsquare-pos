<?php
// Access control
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: /');
    exit;
}

// Generate transaction details
$transaction_no = 'TXN-' . strtoupper(uniqid());
$date = date('F d, Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'Restaurant POS' ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            background-color: #f7f7f7;
            font-family: 'Poppins', sans-serif;
            margin: 0;
            height: 100vh;
            overflow: hidden;
        }

        /* HEADER */
        .pos-header {
            background: #dc3545;
            color: #fff;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .pos-header h5 {
            margin: 0;
        }

        /* MAIN CONTAINER */
        .pos-container {
            display: flex;
            height: calc(100vh - 70px);
            overflow: hidden;
        }

        /* PRODUCTS */
        .pos-products {
            flex: 3;
            padding: 15px;
            background-color: #f7f7f7;
            overflow-y: auto;
        }

        .product-card {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: 0.2s;
            cursor: pointer;
            height: 100%;
        }

        .product-card:hover {
            background: #f0f0f0;
            transform: scale(1.03);
        }

        .product-img {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 6px;
        }

        /* SIDEBAR */
        .pos-sidebar {
            flex: 1;
            background: #fff;
            border-left: 2px solid #e9ecef;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 70px);
            position: relative;
        }

        .order-list {
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 5px;
            /* Space for button */
        }

        .order-list::-webkit-scrollbar {
            width: 6px;
        }

        .order-list::-webkit-scrollbar-thumb {
            background-color: #ccc;
            border-radius: 10px;
        }

        .transaction-info {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 15px;
            position: sticky;
            top: 0;
            z-index: 5;
        }

        /* COLLAPSIBLE SIDEBAR (MOBILE) */
        @media (max-width: 991px) {
            .pos-container {
                flex-direction: row;
                position: relative;
            }

            .pos-products {
                flex: 1;
                overflow-y: auto;
                width: 100%;
                transition: margin-right 0.3s ease;
            }

            .pos-sidebar {
                position: fixed;
                right: -100%;
                width: 80%;
                max-width: 320px;
                height: calc(100vh - 70px);
                box-shadow: -2px 0 10px rgba(0, 0, 0, 0.2);
                transition: right 0.3s ease;
                z-index: 999;
            }

            .pos-sidebar.active {
                right: 0;
            }

            .toggle-sidebar-btn {
                background: #fff;
                color: #dc3545;
                border: none;
                font-weight: bold;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 14px;
            }

            .pos-header {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
            }
        }

        @media (max-width: 576px) {
            .pos-sidebar {
                width: 100%;
            }

            .product-img {
                height: 100px;
            }

            .product-card h6 {
                font-size: 13px;
            }

            .product-card p {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="pos-header d-flex align-items-center h-auto">
        <div class="d-flex align-items-center gap-2">
            <button class="toggle-sidebar-btn d-lg-none" id="toggleSidebar">
                <i class="mdi mdi-menu"></i>
            </button>
            <div>
                <h5 class="mb-0">Lugod Square POS</h5>
                <small><?= htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) ?></small>
            </div>
        </div>
        <div>
            <a href="/logout" class="btn btn-light btn-sm text-danger fw-bold">
                <i class="mdi mdi-logout"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main POS Layout -->
    <div class="pos-container">
        <!-- Products Section -->
        <div class="pos-products">
            <div class="transaction-info">
                <p class="mb-1"><strong>Transaction No:</strong> <?= $transaction_no ?></p>
                <p class="mb-1"><strong>Date:</strong> <?= $date ?></p>
                <p class="mb-0"><strong>Time:</strong> <span id="live-time"></span></p>
            </div>

            <div class="row g-3">
                <?php
                for ($i = 1; $i <= 40; $i++):
                    $name = "Product $i";
                    $price = rand(50, 200);
                    $img = 'https://via.placeholder.com/150';
                ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="product-card">
                            <img src="<?= $img ?>" class="product-img" alt="<?= $name ?>">
                            <h6 class="mt-2 mb-1"><?= $name ?></h6>
                            <p class="text-danger mb-0">₱<?= number_format($price, 2) ?></p>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Sidebar / Order Summary -->
        <div class="pos-sidebar" id="sidebar">
            <h6 class="fw-bold text-danger mb-1 p-3">Order Summary</h6>
            <div class="order-list">
                <div class="p-3 py-0">
                    <?php for ($i = 0; $i < 40; $i++): ?>
                        <p>1x Sample Item <?= $i + 1 ?> — ₱<?= rand(50, 200) ?>.00</p>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="bg-danger p-3">
                <p class="text-light"><small>Sub-Total: ₱200.00</small> <br> <small>Discount: ₱20.00</small> <br> <span class="fw-bold">Total: ₱200.00</span></p>
                <button class="btn btn-outline-light w-100 payment-btn">Proceed to Payment</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-update live time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('live-time').textContent = timeString;
        }
        updateTime();
        setInterval(updateTime, 1000);

        // Sidebar toggle
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    </script>
</body>

</html>