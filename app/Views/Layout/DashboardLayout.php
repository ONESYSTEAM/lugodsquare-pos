<?php

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
    <!-- Bootstrap Icons CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="/css/custom.css">

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
            <button class="btn btn-light btn-sm text-danger fw-bold" id="transactionBtn"><i class="bi bi-clock-history"></i> Transaction History</button>
            <button class="btn btn-light btn-sm text-danger fw-bold d-none" id="orderSumBtn"><i class="bi bi-receipt"></i> Order Summary</button>
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
                <p class="mb-1"><strong>Transaction No:</strong> <span id="transaction-number"><?= $transaction_no ?></span></p>
                <p class="mb-1"><strong>Date:</strong> <?= $date ?></p>
                <p class="mb-0"><strong>Time:</strong> <span id="live-time"></span></p>
            </div>
            <div class="d-flex">
                <div class="btn-group mb-2">
                    <button class="btn btn-outline-danger btn-sm" id="food">Foods</button>
                    <button class="btn btn-outline-danger btn-sm" id="merch">Merch</button>
                </div>

            </div>


            <div class="row g-3 d-none" id="food-products">
                <?php foreach ($products as $product): ?>
                    <?php if ($product['product_category'] === 'Foods'): ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="product-card">
                                <img src="https://placehold.co/150" class="product-img" alt="<?= $product['product_name'] ?>">
                                <h6 class="mt-2 mb-1"><?= $product['product_name'] ?></h6>
                                <p class="text-danger mb-0">₱<?= number_format($product['price'], 2) ?></p>
                                <small>Qty: <?= $product['qty'] ?></small>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <div class="row g-3 d-none" id="merch-products">
                <?php foreach ($products as $product): ?>
                    <?php if ($product['product_category'] === 'Merch'): ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="product-card">
                                <img src="https://placehold.co/150" class="product-img" alt="<?= $product['product_name'] ?>">
                                <h6 class="mt-2 mb-1"><?= $product['product_name'] ?></h6>
                                <p class="text-danger mb-0">₱<?= number_format($product['price'], 2) ?></p>
                                <small>Qty: <?= $product['qty'] ?></small>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Sidebar / Order Summary -->
        <div class="pos-sidebar" id="sidebar">
            <h6 class="fw-bold text-danger mb-1 p-3">Order Summary</h6>
            <div class="order-list">
                <div class="p-3 py-0">
                </div>

            </div>
            <div class="p-3 border-top bg-light d-flex gap-2 justify-content-end">
                <button class="btn btn-sm btn-outline-danger" id="editItemBtn" disabled>Edit Item</button>
                <button class="btn btn-sm btn-outline-secondary" id="removeItemBtn" disabled>Remove Item</button>
            </div>
            <div class="bg-danger p-3">
                <div class="mb-3 d-none" id="discountContainer">
                    <div class="fw-bold text-light mb-2 ">Membership Card Discount:</div>
                    <div class="" id="membershipCard-con">
                        <input type="text" id="membershipCard" class="form-control" placeholder="Scan or enter card number">
                    </div>
                    <div class="mt-2 d-none" id="undoBtn-con">
                        <button class="btn btn-outline-light btn-sm" id="undoBtn">Undo Discount</button>
                    </div>
                    <hr>
                </div>
                <p class="text-light"><span id="subTotal" class="d-none"><small>Sub-Total: ₱0.00</small></span><br class="br d-none"><small id="discount-span" class="d-none">Discount: ₱0.00</small> <br class="br d-none"> <span class="fw-bold">Total: ₱0.00</span></p>

                <button class="btn btn-outline-light w-100 payment-btn" id="paymentBtn">Proceed to Payment</button>
            </div>
        </div>
        <!-- SideBar / Transaction History -->
        <div class="pos-sidebar d-none" id="transactionSideBar">
            <h6 class="fw-bold text-danger mb-1 p-3 mb-3 border-bottom">Transaction History</h6>

            <div class="transaction-list">
                <?php if (!empty($transactions)): ?>
                    <?php foreach ($transactions as $data): ?>
                        <div class="transaction-item d-flex mx-3 justify-content-between flex-column">
                            <input type="hidden" id="saleId" value="<?= $data['id'] ?>">
                            <span class="transactionNumber"><?= $data['transaction_no'] ?></span>
                            <small class="fw-bold ps-1"><?= date('F j, Y g:i A', strtotime($data['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center">
                        <small class="text-muted text-center">No transactions made</small>

                    </div>
                <?php endif ?>
            </div>

            <div class="p-3 border-top bg-light d-flex gap-2 justify-content-end mb-2">
                <button class="btn btn-sm btn-outline-danger px-3" id="viewBtn" disabled>View</button>
                <button class="btn btn-sm btn-outline-secondary" id="removeBtn" disabled>Remove</button>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="paymentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Checkout Receipt</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> -->
                    <button type="button" class="btn btn-danger w-100" id="confirmBtn">Confirm Checkout</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Transaction Receipt</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/js/custom.js"></script>
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
        const transaction = document.getElementById('transactionSideBar');
        const toggleBtn = document.getElementById('toggleSidebar');
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            transaction.classList.toggle('active');
        });

        $(document).ready(function() {
            $('#food').on('click', function() {
                $('#food-products').removeClass('d-none');
                $('#merch-products').addClass('d-none');
                $('#merch').removeClass('btn-danger').addClass('btn-outline-danger');
                $(this).addClass('btn-danger').removeClass('btn-outline-danger');
                $('#discountContainer').addClass('d-none');
                $('#discount-span').addClass('d-none');
                $('.br').addClass('d-none');
                $('#subTotal').addClass('d-none');
            })
        });
        $(document).ready(function() {
            $('#merch').on('click', function() {
                $('#merch-products').removeClass('d-none');
                $('#food-products').addClass('d-none');
                $(this).addClass('btn-danger').removeClass('btn-outline-danger');
                $('#food').removeClass('btn-danger').addClass('btn-outline-danger');
                $('#discountContainer').removeClass('d-none');
                $('#discount-span').removeClass('d-none');
                $('.br').removeClass('d-none');
                $('#subTotal').removeClass('d-none');

            })
        })
    </script>
</body>

</html>