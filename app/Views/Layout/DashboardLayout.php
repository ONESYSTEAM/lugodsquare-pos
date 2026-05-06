<?php
$transaction_no = 'TXN-' . strtoupper(uniqid());
$date = date('F d, Y');

function getProductImage($imageName)
{
    $localPath = $_SERVER['DOCUMENT_ROOT'] . "/uploads/products/" . $imageName;
    $localUrl = "/uploads/products/" . $imageName;
    $remoteUrl = "https://admin.lugodsquare.com/uploads/products/" . $imageName;
    $placeholder = "https://placehold.co/150?text=No+Image";

    if (empty($imageName)) {
        return $placeholder;
    }
    if (file_exists($localPath)) {
        return $localUrl;
    }

    return $remoteUrl;
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

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <link rel="stylesheet" href="/css/custom.css">

    <title><?= isset($title) && !empty($title) ? $this->e($title) : htmlspecialchars($_ENV['APP_NAME'] ?? '') ?></title>

    <style>
        /* Custom styling for Payment Modals */
        .modal-content {
            border: none;
            border-radius: 15px;
        }

        .modal-header {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .form-control-lg {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: right;
        }

        .input-group-text {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        .spin {
            display: inline-block;
            animation: spin 1s linear infinite;
        }
    </style>
</head>

<body>
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
            <button class="btn btn-light btn-sm text-custom fw-bold" id="endShiftBtn"><i class="bi bi-stop-circle"></i>
                End Shift </button>
            <button class="btn btn-light btn-sm text-custom fw-bold" id="transactionBtn"><i
                    class="bi bi-clock-history"></i> Transaction History</button>
            <button class="btn btn-light btn-sm text-custom fw-bold" id="savedCartsBtn"><i class="bi bi-cart3"></i>
                Saved Carts <span id="savedCartsBadge" class="badge bg-danger d-none"></span></button>
            <button class="btn btn-light btn-sm text-custom fw-bold d-none" id="orderSumBtn"><i
                    class="bi bi-receipt"></i> Order Summary</button>
            <button class="btn btn-light btn-sm text-custom fw-bold " id="gcashBtn" data-bs-toggle="modal"
                data-bs-target="#gcashModal"><i class="bi bi-wallet"></i> Gcash</button>
            <button class="btn btn-light btn-sm text-custom fw-bold " id="cashBtn" data-bs-toggle="modal"
                data-bs-target="#cashModal"><i class="bi bi-receipt"></i> Cash</button>
            <a href="/logout" class="btn btn-light btn-sm text-custom fw-bold">
                <i class="mdi mdi-logout"></i> Logout
            </a>
        </div>
    </div>

    <div class="pos-container">
        <div class="pos-products">
            <div class="transaction-info">
                <p class="mb-1"><strong>Transaction No:</strong> <span
                        id="transaction-number"><?= $transaction_no ?></span></p>
                <p class="mb-1"><strong>Date:</strong> <?= $date ?></p>
                <p class="mb-0"><strong>Time:</strong> <span id="live-time"></span></p>
            </div>
            <?php
            // Get unique categories
            $categories = [];
            if (!empty($products)) {
                $categories = array_unique(array_column($products, 'product_category'));
                sort($categories);
            }
            $categoriesWithDiscount = ['Merch', 'Rentals']; // Categories that show discount
            ?>
            <div class="d-flex">
                <div class="btn-group mb-2" id="category-tabs">
                    <?php foreach ($categories as $category): ?>
                        <?php $categoryId = strtolower(preg_replace('/[^a-zA-Z0-0]/', '-', $category)); ?>
                        <button class="btn btn-outline-custom btn-sm category-btn" data-category-id="<?= $categoryId ?>"
                            data-category-name="<?= htmlspecialchars($category) ?>">
                            <?= htmlspecialchars($category) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php foreach ($categories as $category): ?>
                <?php $categoryId = strtolower(preg_replace('/[^a-zA-Z0-0]/', '-', $category)); ?>
                <div class="row g-3 d-none product-container" id="<?= $categoryId ?>-products">
                    <?php foreach ($products as $product): ?>
                        <?php if ($product['product_category'] === $category): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="product-card">
                                    <?php if ($category === 'Rentals'): ?>
                                        <img src="https://placehold.co/150?text=Rental" class="product-img"
                                            alt="<?= htmlspecialchars($product['product_name']) ?>">
                                    <?php else: ?>
                                        <img src="<?= getProductImage($product['product_image']) ?>" class="product-img"
                                            alt="<?= htmlspecialchars($product['product_name']) ?>">
                                    <?php endif; ?>
                                    <h6 class="mt-2 mb-1"><?= htmlspecialchars($product['product_name']) ?></h6>
                                    <p class="text-custom mb-0">₱<?= $product['price'] ?></p>
                                    <?php if (isset($product['qty']) && $product['qty'] !== null): ?>
                                        <small>Qty: <?= $product['qty'] ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <div class="d-flex align-items-center bg-light p-2 rounded border mt-3"
                style="position: sticky; bottom: 0; z-index: 10;">
                <div id="sync-indicator" class="rounded-circle me-2"
                    style="width: 12px; height: 12px; background-color: #6c757d; transition: background-color 0.3s ease;">
                </div>
                <div class="d-flex flex-column flex-grow-1">
                    <small id="sync-status" class="fw-bold text-uppercase" style="font-size: 0.65rem; line-height: 1;">
                        Offline
                    </small>
                    <small id="sync-time" class="text-muted" style="font-size: 0.6rem;">
                        Not synced yet
                    </small>
                </div>
                <button class="btn btn-sm btn-outline-secondary ms-2" id="manualSyncBtn"
                    style="font-size: 0.65rem; padding: 2px 8px;">
                    <i class="bi bi-arrow-repeat"></i> Sync
                </button>
            </div>
        </div>

        <div class="pos-sidebar" id="sidebar">
            <h6 class="fw-bold text-custom mb-1 p-3">Order Summary</h6>
            <div class="order-list">
                <div class="p-3 py-0"></div>
            </div>
            <div class="p-3 border-top bg-light d-flex gap-2 justify-content-end">
                <button class="btn btn-sm btn-outline-custom text-custom" id="editItemBtn" disabled>Edit Item</button>
                <button class="btn btn-sm btn-outline-secondary" id="removeItemBtn" disabled>Remove Item</button>
            </div>
            <div class="bg-custom p-3">
                <div class="mb-3 d-none" id="discountContainer">
                    <div class="fw-bold text-light mb-2 ">Membership Card Discount:</div>
                    <div class="" id="membershipCard-con">
                        <input type="text" id="membershipCard" class="form-control"
                            placeholder="Scan or enter card number">
                    </div>
                    <div class="mt-2 d-none" id="undoBtn-con">
                        <button class="btn btn-outline-light btn-sm" id="undoBtn">Undo Discount</button>
                    </div>
                    <hr>
                </div>
                <p class="text-light"><span id="subTotal" class="d-none"><small>Sub-Total: ₱0.00</small></span><br
                        class="br d-none"><small id="discount-span" class="d-none">Discount: ₱0.00</small> <br
                        class="br d-none"> <span class="fw-bold">Total: ₱0.00</span></p>

                <button class="btn btn-outline-light w-100 payment-btn" id="paymentBtn">Proceed to Payment</button>
                <button class="btn btn-outline-light w-100 mt-2" id="saveCartBtn"><i class="bi bi-bookmark-plus"></i>
                    Save Cart (Pay Later)</button>
            </div>
        </div>
        <div class="pos-sidebar d-none" id="transactionSideBar">
            <div class="d-flex justify-content-between border-bottom">
                <h6 class="fw-bold text-custom mb-1 p-3 ">Transaction History</h6>
                <div class="d-flex gap-2 align-items-center me-2">
                    <button class="btn btn-custom btn-sm text-light" id="generateSalesReportBtn"><i
                            class="bi bi-file-earmark-bar-graph"></i> Sales Report</button>
                </div>
            </div>
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
                <button class="btn btn-sm btn-outline-custom px-3" id="viewBtn" disabled>View</button>
                <button class="btn btn-sm btn-outline-secondary" id="removeBtn" disabled>Remove</button>
            </div>
        </div>
        <div class="pos-sidebar d-none" id="savedCartsSideBar">
            <div class="d-flex justify-content-between border-bottom">
                <h6 class="fw-bold text-custom mb-1 p-3">Saved Carts</h6>
            </div>
            <div class="saved-carts-list">
                <div class="text-center p-3">
                    <small class="text-muted">Loading saved carts...</small>
                </div>
            </div>
            <div class="p-3 border-top bg-light d-flex gap-2 justify-content-end mb-2">
                <button class="btn btn-sm btn-custom text-light px-3" id="loadCartBtn" disabled>Load Cart</button>
                <button class="btn btn-sm btn-outline-danger" id="deleteCartBtn" disabled>Delete</button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Checkout Receipt</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom w-100" id="confirmBtn">Confirm Checkout</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Transaction Receipt</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cashModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-success text-white">
                    <h1 class="modal-title fs-5"><i class="fas fa-money-bill-wave me-2"></i>Cash Payment</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="/payment" method="POST" id="cashPaymentForm">
                        <input type="hidden" name="payment_method" value="Cash">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Transaction No.</label>
                            <input type="text" class="form-control bg-light" value="<?= $transaction_no ?>"
                                name="transaction_no" readonly>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Amount Tendered</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" class="form-control form-control-lg border-success"
                                    placeholder="0.00" name="amount" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fs-5">Confirm Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="gcashModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header bg-primary text-white">
                    <h1 class="modal-title fs-5"><i class="fas fa-mobile-alt me-2"></i>GCash Payment</h1>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="/payment" method="POST" id="gcashPaymentForm">
                        <input type="hidden" name="payment_method" value="Gcash">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Reference No. / GCash Trans #</label>
                            <input type="text" class="form-control border-primary" placeholder="Enter Reference Number"
                                name="transaction_no" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Amount Paid</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" step="0.01" class="form-control form-control-lg border-primary"
                                    placeholder="0.00" name="amount" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fs-5">Confirm GCash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/js/custom.js"></script>
    <script>
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

        const sidebar = document.getElementById('sidebar');
        const transaction = document.getElementById('transactionSideBar');
        const toggleBtn = document.getElementById('toggleSidebar');
        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            transaction.classList.toggle('active');
        });

        $(document).ready(function () {
            const categoriesWithDiscount = ['Merch', 'Rentals'];

            // Set the first tab as active
            const firstTab = $('.category-btn').first();
            if (firstTab.length) {
                firstTab.addClass('btn-custom').removeClass('btn-outline-custom');
                $('#' + firstTab.data('category-id') + '-products').removeClass('d-none');

                // Check for discount on initial load
                if (categoriesWithDiscount.includes(firstTab.data('category-name'))) {
                    $('#discountContainer, #discount-span, .br, #subTotal').removeClass('d-none');
                } else {
                    $('#discountContainer, #discount-span, .br, #subTotal').addClass('d-none');
                }
            }

            $('#category-tabs').on('click', '.category-btn', function () {
                const $this = $(this);
                const categoryId = $this.data('category-id');
                const categoryName = $this.data('category-name');

                // Toggle button styles
                $('.category-btn').removeClass('btn-custom').addClass('btn-outline-custom');
                $this.addClass('btn-custom').removeClass('btn-outline-custom');

                // Show/hide product containers
                $('.product-container').addClass('d-none');
                $('#' + categoryId + '-products').removeClass('d-none');

                // Show/hide discount section
                if (categoriesWithDiscount.includes(categoryName)) {
                    $('#discountContainer, #discount-span, .br, #subTotal').removeClass('d-none');
                } else {
                    $('#discountContainer, #discount-span, .br, #subTotal').addClass('d-none');
                }
            });
        });

        let isSyncing = false;

        async function runAutoSync() {
            const indicator = document.getElementById('sync-indicator');
            const statusText = document.getElementById('sync-status');
            const timeText = document.getElementById('sync-time');

            if (!navigator.onLine) {
                indicator.style.backgroundColor = '#dc3545';
                statusText.innerText = 'OFFLINE';
                statusText.className = 'fw-bold text-danger text-uppercase';
                return;
            }

            if (isSyncing) return;

            isSyncing = true;
            indicator.style.backgroundColor = '#fd7e14';
            statusText.innerText = 'SYNCING...';

            try {
                const response = await fetch('/api/sync-trigger');

                if (!response.ok) throw new Error('Server error');

                const data = await response.json();
                console.log('Sync result:', data);

                indicator.style.backgroundColor = '#198754';
                statusText.innerText = 'ONLINE';
                statusText.className = 'fw-bold text-success text-uppercase';
                timeText.innerText = 'Last: ' + new Date().toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });

            } catch (error) {
                indicator.style.backgroundColor = '#dc3545';
                statusText.innerText = 'SYNC ERROR';
                statusText.className = 'fw-bold text-danger text-uppercase';
                console.error('Heartbeat failed:', error);
            } finally {
                isSyncing = false;
            }
        }

        // Remove this line:
        // setInterval(runAutoSync, 60000);

        // Keep the initial call but change it to just set offline state:
        document.getElementById('sync-indicator').style.backgroundColor = '#6c757d';
        document.getElementById('sync-status').innerText = 'OFFLINE';
        document.getElementById('sync-time').innerText = 'Not synced yet';

        // Manual sync button
        document.getElementById('manualSyncBtn').addEventListener('click', function () {
            // At the top of runAutoSync(), add:
            const syncBtn = document.getElementById('manualSyncBtn');
            syncBtn.disabled = true;
            syncBtn.querySelector('i').classList.add('spin'); // add CSS below

            // In the finally block, add:
            syncBtn.disabled = false;
            syncBtn.querySelector('i').classList.remove('spin');

            runAutoSync();
        });

        // Keep these — online/offline browser events still make sense
        window.addEventListener('online', runAutoSync);
        window.addEventListener('offline', () => {
            document.getElementById('sync-indicator').style.backgroundColor = '#dc3545';
            document.getElementById('sync-status').innerText = 'OFFLINE';
        });


        document.getElementById('endShiftBtn').addEventListener('click', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'End Shift?',
                text: "Are you sure you want to end your shift?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, end shift'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "/end-shift";
                }
            });
        });

        $('#generateSalesReportBtn').on('click', function () {
            const cashierName = "<?= $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] ?? 'Cashier' ?>";

            Swal.fire({
                title: 'Generate Report for ' + cashierName + '?',
                text: "Select transaction type:",
                icon: 'question',
                input: 'radio',
                inputOptions: {
                    'cash': 'Cash Only',
                    'gcash': 'GCash Only',
                    'both': 'Both (Full Report)'
                },
                inputValidator: (value) => {
                    if (!value) return 'You must choose an option!';
                },
                showCancelButton: true,
                confirmButtonText: 'Generate Excel',
                confirmButtonColor: '#28a745'
            }).then((result) => {
                if (result.isConfirmed) {
                    const mode = result.value;

                    // NEW: Use fetch to check if data exists before downloading
                    fetch(`/generate-sales-report?mode=${mode}&check=true`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'empty') {
                                Swal.fire('No Data', 'No sales transactions found for today.', 'info');
                            } else {
                                // Data exists, trigger the actual download
                                window.location.href = `/generate-sales-report?mode=${mode}`;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            // Fallback: If JSON check fails, just try the download anyway
                            window.location.href = `/generate-sales-report?mode=${mode}`;
                        });
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const handlePayment = (formId) => {
                const form = document.getElementById(formId);
                if (!form) return;

                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    // 1. Confirm with user first
                    const formData = new FormData(this);
                    const amount = formData.get('amount');
                    const method = formData.get('payment_method');

                    Swal.fire({
                        title: 'Confirm Payment',
                        text: `Process ${method} payment of ₱${amount}?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Confirm',
                        showLoaderOnConfirm: true, // Shows loading spinner on the button
                        preConfirm: () => {
                            // 2. Send AJAX request
                            return fetch('/payment', {
                                method: 'POST',
                                body: formData
                            })
                                .then(response => {
                                    if (!response.ok) throw new Error(response.statusText);
                                    return response.json(); // Parse JSON from PHP
                                })
                                .catch(error => {
                                    Swal.showValidationMessage(`Request failed: ${error}`);
                                });
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                        // 3. Handle the JSON response
                        if (result.isConfirmed && result.value.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Paid!',
                                text: result.value.message,
                                confirmButtonText: 'Done'
                            }).then(() => {
                                window.location.reload(); // Reload only after they click Done
                            });
                        } else if (result.value && result.value.status === 'error') {
                            Swal.fire('Error', result.value.message, 'error');
                        }
                    });
                });
            };

            handlePayment('cashPaymentForm');
            handlePayment('gcashPaymentForm');
        });
    </script>
</body>

</html>