$(document).ready(function () {
    const orderList = $('.order-list .p-3');
    const totalDisplay = $('.bg-danger p span.fw-bold');
    const subTotalDisplay = $('#subTotal small');

    let orders = {}; // { "Burger": { qty: 2, price: 100 } }

    // Handle product card click
    $('.product-card').on('click', function () {
        const productName = $(this).find('h6').text().trim();
        const productPrice = parseFloat($(this).find('p').text().replace('₱', ''));

        // If item exists, increase qty; else create new entry
        if (orders[productName]) {
            orders[productName].qty++;
        } else {
            orders[productName] = {
                qty: 1,
                price: productPrice
            };
        }

        $('#transactionSideBar').addClass('d-none');
        $('#sidebar').removeClass('d-none');

        renderOrderList();
    });

    // ✅ Render order list
    function renderOrderList() {
        orderList.empty();
        let subtotal = 0;

        $.each(orders, function (name, item) {
            const totalItemPrice = item.qty * item.price;
            subtotal += totalItemPrice;

            orderList.append(`
                        <div class="order-item d-flex justify-content-between align-items-center mb-2 p-2 rounded" data-name="${name}">
                            <div class="d-flex flex-column">
                                <span class="item-name">${item.qty}x ${name}</span>
                                <small class="text-muted ps-1"> ₱${item.price.toFixed(2)} each</small>
                            </div>
                            <div class="text-end">
                                <strong>₱${totalItemPrice.toFixed(2)}</strong>
                            </div>
                        </div> `);
        });

        subTotalDisplay.text('Sub-Total: ₱' + subtotal.toFixed(2));
        totalDisplay.text('Total: ₱' + subtotal.toFixed(2));
    }

    let selectedItem = null;

    // Select order item
    orderList.on('click', '.order-item', function () {
        $('.order-item').removeClass('selected');
        $(this).addClass('selected');

        selectedItem = $(this).data('name');

        $('#editItemBtn, #removeItemBtn').prop('disabled', false);
    });

    // Deselect order item when clicking outside
    $(document).on('click', function (e) {
        const isInsideOrder = $(e.target).closest('.order-item').length > 0;
        const isActionButton = $(e.target).is('#editItemBtn, #removeItemBtn');

        if (!isInsideOrder && !isActionButton) {
            $('.order-item').removeClass('selected');
            selectedItem = null;
            $('#editItemBtn, #removeItemBtn').prop('disabled', true);
        }
    });


    // Edit item button
    $('#editItemBtn').on('click', function () {
        if (!selectedItem) return;
        Swal.fire({
            title: 'Edit Quantity',
            input: 'number',
            inputLabel: `Enter new quantity for ${selectedItem}`,
            inputAttributes: {
                min: 1
            },
            showCancelButton: true,
            confirmButtonText: 'Update',
        }).then(result => {
            if (result.isConfirmed) {
                const newQty = parseInt(result.value);
                if (!isNaN(newQty) && newQty > 0) {
                    orders[selectedItem].qty = newQty;
                    renderOrderList(); // ✅ fixed
                    $('#editItemBtn, #removeItemBtn').prop('disabled', true);
                    selectedItem = null;
                }
            }
        });
    });

    // Remove item button
    $('#removeItemBtn').on('click', function () {
        if (!selectedItem) return;
        Swal.fire({
            title: 'Remove Item?',
            text: `Are you sure you want to remove "${selectedItem}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirm',
        }).then(result => {
            if (result.isConfirmed) {
                delete orders[selectedItem];
                renderOrderList(); // ✅ fixed
                $('#editItemBtn, #removeItemBtn').prop('disabled', true);
                selectedItem = null;
            }
        });
    });




    const paymentBtn = $('#paymentBtn');
    const modalBody = $('#paymentModal .modal-body');
    const modal = new bootstrap.Modal($('#paymentModal')[0]);

    paymentBtn.on('click', function () {
        if ($.isEmptyObject(orders)) {
            Swal.fire({
                icon: 'warning',
                title: 'No items in order',
                text: 'Please add items to the order before proceeding to payment.'
            });
            return false; // stop here, don't open modal
        }

        let total = 0;
        $.each(orders, function (_, item) {
            total += item.qty * item.price;
        });

        // ✅ Check if discount is already applied in the summary
        let discountText = $('#subTotal').next('br').next('small').text().replace('Discount: ₱', '').trim();
        let discountAmount = parseFloat(discountText.replace(/,/g, '')) || 0;
        let finalTotal = total - discountAmount;
        let transaction_no = $('#transaction-number').text();

        // ✅ Generate receipt HTML
        let receiptHTML = `
                    <div class="text-center mb-3">
                        <p class="mb-1 text-center" ><strong>Transaction No:</strong><span id="transactionNo">${transaction_no}</span></p>
                        <small>${new Date().toLocaleString()}</small>
                    </div>
                    <hr>
                    <div>`;

        $.each(orders, function (name, item) {
            const totalItem = item.qty * item.price;
            receiptHTML += `
                    <div class="d-flex justify-content-between mb-1">
                        <span>${item.qty}x ${name}</span>
                        <span>₱${totalItem.toFixed(2)}</span>
                    </div>`;
        });
        receiptHTML += `
                    </div>
                    <hr>`;

        // ✅ Only show subtotal/discount if there’s a difference (discount applied)
        if (total !== finalTotal) {
            receiptHTML += `
        <div class="d-flex justify-content-between">
            <span>Sub-Total:</span>
            <span id="receiptSubTotal">₱${total.toFixed(2)}</span>
        </div>`;
            if (discountAmount > 0) {
                receiptHTML += `
            <div class="d-flex justify-content-between text-danger">
                <span>Discount:</span>
                <span id="receiptDiscount">₱${discountAmount.toFixed(2)}</span>
            </div>`;
            }
        }

        receiptHTML += `
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span id="receiptTotal" class="fw-bold">₱${finalTotal.toFixed(2)}</span>
                    </div>
                    <div class="mt-3 row">
                        <label class="form-label" id="paymentOptionLbl">Cash Payment</label>
                        <div class="col-12">
                            <input type="number" id="amountPaid" class="form-control" placeholder="Enter Cash Amount">
                            <input type="text" id="cardPayment" class="form-control d-none" placeholder="Scan or enter card number">
                        </div>
                        <div class="col-12 mt-2" id="paymentOptionBtn">
                            <button class="btn btn-danger btn-sm" id="cardPaymentBtn">Pay Via Membership Card </button>
                        </div>
                        <div class="mt-2 d-none" id="undoPaymentCon">
                            <button class="btn btn-danger btn-sm" id="undoPaymentBtn">Undo Card Payment</button>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Change:</span>
                        <span id="receiptChange">₱0.00</span>
                    </div>`;

        modalBody.html(receiptHTML);

        // ✅ Change calculator (now respects discount)
        modalBody.on('input', '#amountPaid', function () {
            const paid = parseFloat($(this).val()) || 0;
            const currentTotal = parseFloat($('#receiptTotal').text().replace('₱', '').replace(/,/g, '')) || 0;
            const change = paid - currentTotal;
            $('#receiptChange').text(change >= 0 ? '₱' + change.toFixed(2) : 'Insufficient');
        });

        let scanBuffer = '';
        let scanTimeout = null;

        modalBody.on('keydown', '#amountPaid', function (e) {
            // Prevent modal from closing when Enter is pressed
            if (e.key === 'Enter') {
                e.preventDefault();
                e.stopPropagation();

                // Detect long, fast input sequence (likely a scan)
                if (scanBuffer.length >= 8) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Card Scan Detected',
                        text: 'Please switch to Card Payment mode before scanning a card.',
                        confirmButtonText: 'OK'
                    });

                    $(this).val(''); // clear input if mistaken scan
                    $('#receiptChange').text('₱0.00');

                }

                // Reset buffer always after Enter
                scanBuffer = '';
                return;
            }

            // Capture only normal characters
            if (e.key.length === 1) {
                scanBuffer += e.key;

                // Reset buffer if delay between keys is too long (human typing)
                clearTimeout(scanTimeout);
                scanTimeout = setTimeout(() => {
                    scanBuffer = '';
                }, 150);
            }
        });


        // ✅ Finally, show the modal
        modal.show();

        // ✅ Focus the amountPaid input after the modal is fully shown
        $('#paymentModal').on('shown.bs.modal', function () {
            $('#amountPaid').trigger('focus');
        });
    });

    $('#membershipCard').on('change', function () {
        if ($.isEmptyObject(orders)) {
            Swal.fire({
                icon: 'warning',
                title: 'No items in order',
                text: 'Please add items to the order before applying discount.'
            });
            $('#membershipCard').val('');
            return false;
        }
        const cardNumber = $(this).val().trim();
        if (cardNumber !== '' && cardNumber.length >= 10) {
            verifyMembership(cardNumber);
        }
    });

    function verifyMembership(cardNumber) {
        $.ajax({
            url: '/verify-membership', // must point to your controller route
            method: 'POST',
            dataType: 'json', // ✅ ensure response is parsed as JSON
            data: {
                card_number: cardNumber
            },
            success: function (response) {
                if (response.status === 'success' && response.is_valid) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Membership verified!',
                        text: 'Discount applied.'
                    });
                    // ✅ Apply discount logic here
                    // ✅ Apply 10% discount logic
                    let subTotalText = $('#subTotal small').text().replace('Sub-Total: ₱', '').trim();
                    let subTotal = parseFloat(subTotalText.replace(/,/g, '')) || 0;

                    let discountAmount = subTotal * 0.10; // 10% discount
                    let total = subTotal - discountAmount;

                    // ✅ Update the display
                    $('#subTotal small').text('Sub-Total: ₱' + subTotal.toFixed(2));
                    $('#subTotal').next('br').next('small').text('Discount: ₱' + discountAmount.toFixed(2));
                    $('#subTotal').parent().find('.fw-bold').text('Total: ₱' + total.toFixed(2));

                    $('#membershipCard').prop('disabled', true);;
                    $('#undoBtn-con').removeClass('d-none');


                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid card',
                        text: 'Membership card not found.'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Error verifying membership. Please try again.'
                });

                console.log(cardNumber)
            }
        });
    }
    $('#undoBtn').on('click', function () {
        Swal.fire({
            title: 'Undo Discount?',
            text: 'Are you sure you want to remove the membership discount?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // ✅ Get current values
                let totalText = $('#subTotal').parent().find('.fw-bold').text().replace('Total: ₱', '').trim();
                let currentTotal = parseFloat(totalText.replace(/,/g, '')) || 0;

                let discountText = $('#subTotal').next('br').next('small').text().replace('Discount: ₱', '').trim();
                let discountAmount = parseFloat(discountText.replace(/,/g, '')) || 0;

                // ✅ Compute original subtotal (before discount)
                let originalSubTotal = currentTotal + discountAmount;

                // ✅ Reset all displays
                $('#subTotal small').text('Sub-Total: ₱' + originalSubTotal.toFixed(2));
                $('#subTotal').next('br').next('small').text('Discount: ₱0.00');
                $('#subTotal').parent().find('.fw-bold').text('Total: ₱' + originalSubTotal.toFixed(2));

                // ✅ Re-enable membership input
                $('#membershipCard').prop('disabled', false).val('');
                $('#membershipCard-con').removeClass('col-9').addClass('col-12');
                $('#undoBtn-con').addClass('d-none');

                // ✅ Confirmation message
                Swal.fire({
                    icon: 'success',
                    title: 'Discount removed',
                    text: 'Membership discount has been undone.',
                    timer: 1800,
                    showConfirmButton: false
                });
            }
        });
    });

    $(document).on('click', '#cardPaymentBtn', function () {
        const isCardMode = $('#cardPayment').is(':visible');

        if (isCardMode) {
            // Switch back to cash input
            $('#cardPayment').addClass('d-none');
            $('#amountPaid').removeClass('d-none');
            $(this).text('Pay with Card'); // button label (optional)
            $('#paymentOptionLbl').text('Cash Payment');
        } else {
            // Switch to card payment input
            $('#amountPaid').addClass('d-none');
            $('#cardPayment').removeClass('d-none').trigger('focus');
            $(this).text('Pay with Cash'); // button label (optional)
            $('#paymentOptionLbl').text('Card Payment');

        }
    });


    let memberCardNumber = "N/A";
    let cardAmountPaid;
    let cardPaymentDone = false;
    $(document).on('change', '#cardPayment', function () {
        const cardNumber = $(this).val().trim();
        const totalAmount = parseFloat($('#receiptTotal').text().replace('₱', '').replace(/,/g, '')) || 0;
        if (!cardNumber) return;

        $.ajax({
            url: '/cardPayment', // your controller route
            method: 'POST',
            dataType: 'json',
            data: {
                cardNumber: cardNumber,
                total: totalAmount
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Successful',
                        text: `₱${response.amountPaid.toFixed(2)} deducted from wallet.`,
                        confirmButtonColor: '#3085d6'
                    });

                    // Optional: auto-fill the amountPaid field for record
                    $('#cardPayment').val(`₱${response.amountPaid.toFixed(2)}`).prop('disabled', true);
                    $('#paymentOptionBtn').addClass('d-none');
                    $('#undoPaymentCon').removeClass('d-none');

                    memberCardNumber = cardNumber;
                    cardAmountPaid = response.amountPaid
                    cardPaymentDone = true;

                    setCardPaymentDone(cardNumber, response.amountPaid);

                    // Optional: show change if included
                    if (response.change !== undefined) {
                        $('#changeAmount').val('₱' + response.change.toFixed(2));
                    }

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Transaction Failed',
                        text: response.message || 'Insufficient balance or invalid card.'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Unable to process payment. Please try again.'
                });
            }
        });
    });

    $('#confirmBtn').on('click', function () {
        const amountPaid = $('#amountPaid').val()?.trim();
        const cardPayment = $('#cardPayment').val()?.trim();
        const total = parseFloat($('#receiptTotal').text().replace('₱', '').replace(/,/g, '')) || 0;

        // 🧾 Check if neither valid cash nor card payment is provided
        if ((!amountPaid || parseFloat(amountPaid) < total) && !cardPayment) {
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Payment',
                text: 'Please enter a valid cash amount or provide card payment before confirming.',
            });
            return; // ⛔ stop checkout
        }

        // ✅ proceed to confirm transaction
        confirmTransaction();
    });


    function confirmTransaction() {
        // Collect data
        const transactionNo = $('#transactionNo').text().trim(); // Example: from your receipt
        const subTotal = parseFloat($('#receiptSubTotal').text().replace('₱', '').replace(/,/g, '')) || 0;
        const discount = parseFloat($('#receiptDiscount').text().replace('₱', '').replace(/,/g, '')) || 0;
        const finalTotal = parseFloat($('#receiptTotal').text().replace('₱', '').replace(/,/g, '')) || 0;
        const paymentMode = $('#cardPayment').is(':visible') ? 'Card' : 'Cash';

        // Collect orders (assuming you have them in your JS orders object)
        const orderData = [];
        $.each(orders, function (name, item) {
            orderData.push({
                name: name,
                qty: item.qty,
                price: item.price,
                total: (item.qty * item.price).toFixed(2)
            });
        });

        // Confirm before submitting
        Swal.fire({
            title: 'Confirm Transaction?',
            text: 'This will finalize the sale.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Send to backend
                $.ajax({
                    url: '/confirm-transaction',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        transaction_no: transactionNo,
                        orders: JSON.stringify(orderData),
                        subtotal: subTotal,
                        discount: discount,
                        final_total: finalTotal,
                        payment_mode: paymentMode,
                        card_number: memberCardNumber
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Transaction Confirmed!',
                                text: 'Sale successfully recorded.',
                                timer: 1800,
                                showConfirmButton: false
                            }).then(() => {
                                clearCardPaymentState();
                                location.reload(); // Refresh page or redirect to summary
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to record transaction.'
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Unable to connect to the server.'
                        });
                    }
                });
            }
        });
    }

    $(document).on('click', '#undoPaymentBtn', function () {
        Swal.fire({
            title: 'Undo Card Payment?',
            text: 'This will cancel the card payment and reset the wallet balance.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirm',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                undoCardPayment();
            }
        });
    });

    function undoCardPayment() {
        $.ajax({
            url: '/undo-card-payment',
            method: 'POST',
            dataType: 'json',
            data: {
                card_number: memberCardNumber,
                amount_paid: cardAmountPaid
            },
            success: function (response) {
                if (response.status === 'success') {
                    // 🔁 Reset UI back to cash payment
                    $('#cardPayment').val('').addClass('d-none').prop('disabled', false);
                    $('#amountPaid').removeClass('d-none').val('');
                    $('#undoPaymentCon').addClass('d-none');
                    $('#paymentOptionBtn').removeClass('d-none');
                    $('#paymentOptionLbl').text('Cash Payment');
                    $('#cardPaymentBtn').text('Pay Via Membership Card');

                    cardPaymentDone = false;
                    clearCardPaymentState();


                    Swal.fire({
                        icon: 'success',
                        title: 'Wallet Balance Refunded',
                        text: 'Card payment undone and wallet balance restored.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: response.message || 'Could not reset wallet balance.'
                    });
                }
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Unable to connect to the server.'
                });
            }
        });
    }

    // Detect close button click on modal
    $(document).on('click', '#paymentModal .btn-close', function (e) {
        e.preventDefault(); // prevent modal from immediately closing

        if (cardPaymentDone) {
            Swal.fire({
                icon: 'warning',
                title: 'Cancel Checkout?',
                text: 'A card payment has already been processed. Continuing will reset the transaction and refund the wallet.',
                showCancelButton: true,
                confirmButtonText: 'Confirm',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    undoCardPayment();
                    clearCardPaymentState();
                } else {
                    // Keep or re-show modal if user clicks "No, go back"
                    $('#paymentModal').modal('show');
                }
            });
        } else {
            Swal.fire({
                icon: 'question',
                title: 'Cancel Checkout?',
                text: 'Are you sure you want to cancel the checkout?',
                showCancelButton: true,
                confirmButtonText: 'Confirm',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#paymentModal').modal('hide');
                } else {
                    // Keep or re-show modal if user clicks "No, go back"
                    $('#paymentModal').modal('show');
                }
            });
        }
    });

    // ✅ Save payment state (when card payment succeeds)
    function setCardPaymentDone(cardNumber, amountPaid) {
        sessionStorage.setItem('cardPaymentDone', 'true');
        sessionStorage.setItem('memberCardNumber', cardNumber);
        sessionStorage.setItem('cardAmountPaid', amountPaid);
    }

    // ✅ Clear payment state (when checkout finishes or is undone)
    function clearCardPaymentState() {
        sessionStorage.removeItem('cardPaymentDone');
        sessionStorage.removeItem('memberCardNumber');
        sessionStorage.removeItem('cardAmountPaid');
    }

    // native beforeunload (keeps browser native dialog)
    window.addEventListener('beforeunload', function (e) {
        if (sessionStorage.getItem('cardPaymentDone') === 'true') {
            const msg = 'A card payment was processed. Reloading will reset the transaction and refund the wallet.';
            e.preventDefault();
            e.returnValue = msg;
            return msg;
        }
        // no return -> no prompt
    });

    // On page load, check if a pending card payment exists and show Swal
    $(window).on('load', function () {
        if (sessionStorage.getItem('cardPaymentDone') === 'true') {
            const pendingCard = sessionStorage.getItem('memberCardNumber') || '';
            const pendingAmount = parseFloat(sessionStorage.getItem('cardAmountPaid') || '0') || 0;

            // 🟡 Automatically refund and notify
            $.ajax({
                url: '/undo-card-payment',
                method: 'POST',
                dataType: 'json',
                data: {
                    card_number: pendingCard,
                    amount_paid: pendingAmount
                },
                success: function (res) {
                    if (res.status === 'success') {
                        clearCardPaymentState();
                        Swal.fire({
                            icon: 'info',
                            title: 'Card Payment Refunded',
                            text: 'A previous card payment was detected and has been refunded for your security.',
                            confirmButtonColor: '#3085d6',
                        }).then(() => {
                            $('#paymentModal').modal('hide'); // ensure modal is closed
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Refund Failed',
                            text: 'We could not process the refund automatically. Please check the wallet balance manually.',
                            confirmButtonColor: '#d33'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Server Error',
                        text: 'Unable to contact server to verify the card payment.',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        }
    });

    let selectedTransaction = null;

    // 🔹 Open Order Summary sidebar
    $('#orderSumBtn').on('click', function () {
        $('#transactionSideBar').addClass('d-none');
        $('#sidebar').removeClass('d-none');
        $('#transactionBtn').removeClass('d-none');
        $(this).addClass('d-none');
    });

    // 🔹 Open transaction sidebar
    $('#transactionBtn').on('click', function () {
        $('#transactionSideBar').removeClass('d-none');
        $('#sidebar').addClass('d-none');
        $('#orderSumBtn').removeClass('d-none');
        $(this).addClass('d-none');
    });

    // 🔹 When user clicks a transaction item
    $(document).on('click', '.transaction-item', function () {
        $('.transaction-item').removeClass('selected');
        $(this).addClass('selected');

        selectedTransaction = $(this).find('#saleId').val()?.trim() || null;

        if (selectedTransaction) {
            $('#viewBtn').prop('disabled', false);
            $('#removeBtn').prop('disabled', false);
        }
    });

    // 🔹 Deselect transaction if user clicks outside
    $(document).on('click', function (e) {
        const isInsideTransaction = $(e.target).closest('.transaction-item').length > 0;
        const isActionButton = $(e.target).is('#viewBtn, #removeBtn');

        if (!isInsideTransaction && !isActionButton) {
            $('.transaction-item').removeClass('selected');
            selectedTransaction = null;
            $('#viewBtn, #removeBtn').prop('disabled', true);
        }
    });
    // 🔹 View Transaction (show modal and load details)
    $('#viewBtn').on('click', function () {
        // Get the transaction number from the selected list item
        const transactionNumber = $('.transaction-list .d-flex.bg-primary')
            .find('.transaction-number')
            .text()
            .trim();

        // Show modal
        $('#transactionModal').modal('show');

        // Show loading message
        $('.modal-body').html('<p class="text-center text-muted">Loading transaction details...</p>');

        // Disable the confirm button until loaded
        $('#confirmBtn').prop('disabled', true);

        // 🔹 Fetch transaction details via AJAX
        $.ajax({
            url: '/get-sales-items',
            method: 'POST',
            dataType: 'json',
            data: {
                sale_id: selectedTransaction
            },
            success: function (response) {
                if (response.status === 'success') {
                    const items = response.items || [];
                    const subtotal = parseFloat(response.subtotal || 0);
                    const discount = parseFloat(response.discount || 0);
                    const total = parseFloat(response.total || 0);
                    const mode = response.mode || 'N/A';

                    let itemRows = '';

                    items.forEach(item => {
                        itemRows += `
                                <tr>
                                    <td>${item.item_name}</td>
                                    <td>${item.qty}</td>
                                    <td>₱${parseFloat(item.price).toFixed(2)}</td>
                                    <td>₱${(item.qty * item.price).toFixed(2)}</td>
                                </tr>`;
                    });

                    const receiptHTML = `
                            <div class="text-center mb-3">
                                <h5 class="fw-bold mb-0">${response.transactionNumber}</h5>
                                <small>${response.datetime}</small>
                            </div>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>${itemRows}</tbody>
                            </table>
                            <div>
                                <p class="d-flex justify-content-between mb-0"><span>Subtotal:</span> <strong>₱${subtotal.toFixed(2)}</strong></p>
                                <p class="d-flex justify-content-between mb-0"><span>Discount:</span> <strong>₱${discount.toFixed(2)}</strong></p>
                                <p class="d-flex justify-content-between mb-0"><span>Total:</span> <strong>₱${total.toFixed(2)}</strong></p>
                                <hr>
                                <p>Mode of Payment: <strong>${mode}</strong></p>
                            </div>`;

                    $('.modal-body').html(receiptHTML);
                    $('#confirmBtn').prop('disabled', false);
                } else {
                    $('.modal-body').html('<p class="text-danger text-center">Failed to load transaction details.</p>');
                }
            },
            error: function () {
                $('.modal-body').html('<p class="text-danger text-center">Server error while loading transaction.</p>');
            }
        });

    });


    // 🔹 Remove Transaction (requires admin ID)
    $('#removeBtn').on('click', function () {
        let saleId = selectedTransaction;

        $(document).off('click.transactionDeselect');
        // e.stopPropagation();
        Swal.fire({
            title: 'Admin Verification Required',
            input: 'password',
            inputLabel: 'Enter Admin Username',
            inputPlaceholder: 'Admin Username',
            inputAttributes: {
                autocapitalize: 'off'
            },
            showCancelButton: true,
            confirmButtonText: 'Verify & Remove',
            showLoaderOnConfirm: true,
            preConfirm: (adminId) => {
                return $.ajax({
                    url: '/verify-admin', // 👈 backend route to check admin
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        username: adminId
                    },
                }).then(response => {
                    if (!response.valid) {
                        throw new Error(response.message || 'Invalid admin ID');
                    }
                    return response; // ✅ valid admin
                }).catch(error => {
                    Swal.showValidationMessage(error.message || 'Verification failed. Please try again.');
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceed to remove transaction
                $.ajax({
                    url: '/remove-transaction',
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        sale_id: saleId
                    },
                    success: function (res) {
                        if (res.status === 'success') {
                            let alertMsg = `Transaction <b>${res.transactionNum}</b> has been deleted.`;

                            // 🔹 If refund happened, append refund info in bold on new line
                            if (res.refund) {
                                alertMsg += `<br><br><b>Wallet refund:</b> ₱${res.refund.amount} refunded to ${res.refund.member_name}<br><b>Card Number: </b>${res.refund.membership_id}`;
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Transaction Removed',
                                html: alertMsg, // ✅ use 'html' instead of 'text'
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed',
                                text: res.message || 'Unable to delete transaction.'
                            });
                        }
                    },

                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Could not contact server.'
                        });
                    }
                });
            }
        });
    });
});