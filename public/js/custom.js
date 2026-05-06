$(document).ready(function () {
  // At the very top of $(document).ready, with the other let declarations
  let orders = {};
  let activeLoadedCartId = null; // ← move here, remove from bottom
  let activeLoadedCartName = null;

  const orderList = $(".order-list .p-3");
  const totalDisplay = $(".bg-custom p span.fw-bold");
  const subTotalDisplay = $("#subTotal small");

  // ✅ helper: trigger server-side printing
  function triggerPrint(printUrl) {
    if (!printUrl) return Promise.resolve({ status: "skip" });

    // use fetch (GET) so it hits /print-receipt?transaction_no=...
    return fetch(printUrl, { method: "GET" })
      .then((r) => r.json())
      .catch((err) => ({
        status: "error",
        message: err?.message || "Print request failed",
      }));
  }

  $(".product-card").on("click", function () {
    const productName = $(this).find("h6").text().trim();
    const productPrice = parseFloat(
      $(this).find("p").text().replace(/[₱,]/g, ""),
    );

    if (orders[productName]) {
      orders[productName].qty++;
    } else {
      orders[productName] = {
        qty: 1,
        price: productPrice,
      };
    }

    $("#transactionSideBar").addClass("d-none");
    $("#sidebar").removeClass("d-none");

    renderOrderList();
  });

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
                                <small class="text-custom ps-1"> ₱${item.price.toFixed(2)}</small>
                            </div>
                            <div class="text-end">
                                <strong>₱${totalItemPrice.toFixed(2)}</strong>
                            </div>
                        </div> `);
    });

    subTotalDisplay.text("Sub-Total: ₱" + subtotal.toFixed(2));
    totalDisplay.text("Total: ₱" + subtotal.toFixed(2));
  }

  let selectedItem = null;

  orderList.on("click", ".order-item", function () {
    $(".order-item").removeClass("selected");
    $(this).addClass("selected");

    selectedItem = $(this).data("name");

    $("#editItemBtn, #removeItemBtn").prop("disabled", false);
  });

  $(document).on("click", function (e) {
    // Order item deselect
    const isInsideOrder = $(e.target).closest(".order-item").length > 0;
    const isOrderAction = $(e.target).is("#editItemBtn, #removeItemBtn");
    if (!isInsideOrder && !isOrderAction) {
      $(".order-item").removeClass("selected");
      selectedItem = null;
      $("#editItemBtn, #removeItemBtn").prop("disabled", true);
    }

    // Transaction item deselect
    const isInsideTransaction =
      $(e.target).closest(".transaction-item").length > 0;
    const isTransactionAction = $(e.target).is("#viewBtn, #removeBtn");
    if (!isInsideTransaction && !isTransactionAction) {
      $(".transaction-item").removeClass("selected");
      selectedTransaction = null;
      $("#viewBtn, #removeBtn").prop("disabled", true);
    }

    // Saved cart deselect
    const isInsideSavedCart =
      $(e.target).closest(".saved-cart-item").length > 0;
    const isSavedCartAction = $(e.target).is("#loadCartBtn, #deleteCartBtn");
    if (!isInsideSavedCart && !isSavedCartAction) {
      $(".saved-cart-item").removeClass("selected");
      selectedSavedCart = null;
      $("#loadCartBtn, #deleteCartBtn").prop("disabled", true);
    }
  });

  $("#editItemBtn").on("click", function () {
    if (!selectedItem) return;
    Swal.fire({
      title: "Edit Quantity",
      input: "number",
      inputLabel: `Enter new quantity for ${selectedItem}`,
      inputAttributes: {
        min: 1,
      },
      showCancelButton: true,
      confirmButtonText: "Update",
    }).then((result) => {
      if (result.isConfirmed) {
        const newQty = parseInt(result.value);
        if (!isNaN(newQty) && newQty > 0) {
          orders[selectedItem].qty = newQty;
          renderOrderList();
          $("#editItemBtn, #removeItemBtn").prop("disabled", true);
          selectedItem = null;
        }
      }
    });
  });

  $("#removeItemBtn").on("click", function () {
    if (!selectedItem) return;
    Swal.fire({
      title: "Remove Item?",
      text: `Are you sure you want to remove "${selectedItem}"?`,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Confirm",
    }).then((result) => {
      if (result.isConfirmed) {
        delete orders[selectedItem];
        renderOrderList();
        $("#editItemBtn, #removeItemBtn").prop("disabled", true);
        selectedItem = null;
      }
    });
  });

  const paymentBtn = $("#paymentBtn");
  const modalBody = $("#paymentModal .modal-body");
  const modal = new bootstrap.Modal($("#paymentModal")[0]);

  paymentBtn.on("click", function () {
    if ($.isEmptyObject(orders)) {
      Swal.fire({
        icon: "warning",
        title: "No items in order",
        text: "Please add items to the order before proceeding to payment.",
      });
      return false;
    }

    let total = 0;
    $.each(orders, function (_, item) {
      total += item.qty * item.price;
    });

    let discountText = $("#subTotal")
      .next("br")
      .next("small")
      .text()
      .replace("Discount: ₱", "")
      .trim();
    let discountAmount = parseFloat(discountText.replace(/,/g, "")) || 0;
    let finalTotal = total - discountAmount;
    let transaction_no = $("#transaction-number").text();

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
                            <input type="number" id="gcashPayment" class="form-control d-none" placeholder="Enter Gcash Amount">
                            <input type="text" id="cardPayment" class="form-control d-none" placeholder="Scan or enter card number">
                        </div>
                        <div class="col-12 mt-2" id="paymentOptionBtn">
                            <button class="btn btn-outline-custom btn-sm" id="cardPaymentBtn">Pay with Card </button>
                            <button class="btn btn-outline-custom btn-sm" id="gcashPaymentBtn">Pay with Gcash </button>
                        </div>
                        <div class="mt-2 d-none" id="undoPaymentCon">
                            <button class="btn btn-custom btn-sm" id="undoPaymentBtn">Undo Card Payment</button>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Change:</span>
                        <span id="receiptChange">₱0.00</span>
                    </div>`;

    modalBody.html(receiptHTML);

    modalBody
      .off("input", "#amountPaid")
      .on("input", "#amountPaid", function () {
        const paid = parseFloat($(this).val()) || 0;
        const currentTotal =
          parseFloat(
            $("#receiptTotal").text().replace("₱", "").replace(/,/g, ""),
          ) || 0;
        const change = paid - currentTotal;
        $("#receiptChange").text(
          change >= 0 ? "₱" + change.toFixed(2) : "Insufficient",
        );
      });

    modalBody
      .off("input", "#gcashPayment")
      .on("input", "#gcashPayment", function () {
        const paid = parseFloat($(this).val()) || 0;
        const currentTotal =
          parseFloat(
            $("#receiptTotal").text().replace("₱", "").replace(/,/g, ""),
          ) || 0;
        const change = paid - currentTotal;
        $("#receiptChange").text(
          change >= 0 ? "₱" + change.toFixed(2) : "Insufficient",
        );
      });

    modalBody
      .off("keydown", "#amountPaid")
      .on("keydown", "#amountPaid", function (e) {
        if (e.key === "Enter") {
          e.preventDefault();
          e.stopPropagation();

          if (scanBuffer.length >= 8) {
            Swal.fire({
              icon: "warning",
              title: "Card Scan Detected",
              text: "Please switch to Card Payment mode before scanning a card.",
              confirmButtonText: "OK",
            });

            $(this).val("");
            $("#receiptChange").text("₱0.00");
          }

          scanBuffer = "";
          return;
        }

        if (e.key.length === 1) {
          scanBuffer += e.key;

          clearTimeout(scanTimeout);
          scanTimeout = setTimeout(() => {
            scanBuffer = "";
          }, 150);
        }
      });

    modal.show();

    $("#paymentModal").on("shown.bs.modal", function () {
      $("#amountPaid").trigger("focus");
    });
  });

  $("#membershipCard").on("change", function () {
    if ($.isEmptyObject(orders)) {
      Swal.fire({
        icon: "warning",
        title: "No items in order",
        text: "Please add items to the order before applying discount.",
      });
      $("#membershipCard").val("");
      return false;
    }
    const cardNumber = $(this).val().trim();
    if (cardNumber !== "" && cardNumber.length >= 10) {
      verifyMembership(cardNumber);
    }
  });

  function verifyMembership(cardNumber) {
    $.ajax({
      url: "/verify-membership",
      method: "POST",
      dataType: "json",
      data: { card_number: cardNumber },
      success: function (response) {
        if (response.status === "success" && response.is_valid) {
          Swal.fire({
            icon: "success",
            title: "Membership verified!",
            text: "Discount applied.",
          });
          let subTotalText = $("#subTotal small")
            .text()
            .replace("Sub-Total: ₱", "")
            .trim();
          let subTotal = parseFloat(subTotalText.replace(/,/g, "")) || 0;

          let discountAmount = subTotal * 0.1;
          let total = subTotal - discountAmount;

          $("#subTotal small").text("Sub-Total: ₱" + subTotal.toFixed(2));
          $("#subTotal")
            .next("br")
            .next("small")
            .text("Discount: ₱" + discountAmount.toFixed(2));
          $("#subTotal")
            .parent()
            .find(".fw-bold")
            .text("Total: ₱" + total.toFixed(2));

          $("#membershipCard").prop("disabled", true);
          $("#undoBtn-con").removeClass("d-none");
        } else {
          Swal.fire({
            icon: "error",
            title: "Invalid card",
            text: "Membership card not found.",
          });
        }
      },
      error: function () {
        Swal.fire({
          icon: "error",
          title: "Server Error",
          text: "Error verifying membership. Please try again.",
        });
      },
    });
  }

  $("#undoBtn").on("click", function () {
    Swal.fire({
      title: "Undo Discount?",
      text: "Are you sure you want to remove the membership discount?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      cancelButtonColor: "#6c757d",
      confirmButtonText: "Confirm",
      cancelButtonText: "Cancel",
    }).then((result) => {
      if (result.isConfirmed) {
        let totalText = $("#subTotal")
          .parent()
          .find(".fw-bold")
          .text()
          .replace("Total: ₱", "")
          .trim();
        let currentTotal = parseFloat(totalText.replace(/,/g, "")) || 0;

        let discountText = $("#subTotal")
          .next("br")
          .next("small")
          .text()
          .replace("Discount: ₱", "")
          .trim();
        let discountAmount = parseFloat(discountText.replace(/,/g, "")) || 0;

        let originalSubTotal = currentTotal + discountAmount;

        $("#subTotal small").text("Sub-Total: ₱" + originalSubTotal.toFixed(2));
        $("#subTotal").next("br").next("small").text("Discount: ₱0.00");
        $("#subTotal")
          .parent()
          .find(".fw-bold")
          .text("Total: ₱" + originalSubTotal.toFixed(2));

        $("#membershipCard").prop("disabled", false).val("");
        $("#membershipCard-con").removeClass("col-9").addClass("col-12");
        $("#undoBtn-con").addClass("d-none");

        Swal.fire({
          icon: "success",
          title: "Discount removed",
          text: "Membership discount has been undone.",
          timer: 1800,
          showConfirmButton: false,
        });
      }
    });
  });

  $(document).on("click", "#cardPaymentBtn", function () {
    const isCardMode = $("#cardPayment").is(":visible");

    if (isCardMode) {
      $("#cardPayment").addClass("d-none");
      $("#amountPaid").removeClass("d-none").trigger("focus");
      $(this).text("Pay with Card");
      $("#paymentOptionLbl").text("Cash Payment");
      $("#amountPaid").val("");
      $("#receiptChange").text("₱0.00");
    } else {
      $("#amountPaid").addClass("d-none");
      $("#gcashPayment").addClass("d-none");
      $("#cardPayment").removeClass("d-none").trigger("focus");
      $("#gcashPaymentBtn").text("Pay with Gcash");
      $(this).text("Pay with Cash");
      $("#paymentOptionLbl").text("Membership Card Payment");
      $("#receiptChange").text("₱0.00");
    }
  });

  $(document).on("click", "#gcashPaymentBtn", function () {
    const isGcashMode = $("#gcashPayment").is(":visible");

    if (isGcashMode) {
      $("#gcashPayment").addClass("d-none");
      $("#amountPaid").removeClass("d-none").trigger("focus");
      $(this).text("Pay with Gcash");
      $("#paymentOptionLbl").text("Cash Payment");
      $("#amountPaid").val("");
      $("#receiptChange").text("₱0.00");
    } else {
      $("#amountPaid").addClass("d-none");
      $("#cardPayment").addClass("d-none");
      $("#gcashPayment").removeClass("d-none").trigger("focus");
      $("#cardPaymentBtn").text("Pay with Card");
      $(this).text("Pay with Cash");
      $("#paymentOptionLbl").text("Walk-in Gcash Payment");
      $("#receiptChange").text("₱0.00");
      $("#gcashPayment").val("");
    }
  });

  let memberCardNumber = "N/A";
  let cardAmountPaid;
  let cardPaymentDone = false;

  $(document).on("change", "#cardPayment", function () {
    const cardNumber = $(this).val().trim();
    const totalAmount =
      parseFloat(
        $("#receiptTotal").text().replace("₱", "").replace(/,/g, ""),
      ) || 0;
    if (!cardNumber) return;

    $.ajax({
      url: "/cardPayment",
      method: "POST",
      dataType: "json",
      data: { cardNumber: cardNumber, total: totalAmount },
      success: function (response) {
        if (response.success) {
          Swal.fire({
            icon: "success",
            title: "Payment Successful",
            text: `₱${response.amountPaid.toFixed(2)} deducted from wallet.`,
            confirmButtonColor: "#3085d6",
          });

          $("#cardPayment")
            .val(`₱${response.amountPaid.toFixed(2)}`)
            .prop("disabled", true);
          $("#paymentOptionBtn").addClass("d-none");
          $("#undoPaymentCon").removeClass("d-none");

          memberCardNumber = cardNumber;
          cardAmountPaid = response.amountPaid;
          cardPaymentDone = true;

          setCardPaymentDone(cardNumber, response.amountPaid);

          if (response.change !== undefined) {
            $("#changeAmount").val("₱" + response.change.toFixed(2));
          }
        } else {
          Swal.fire({
            icon: "error",
            title: "Transaction Failed",
            text: response.message || "Insufficient balance or invalid card.",
          });
        }
      },
      error: function () {
        Swal.fire({
          icon: "error",
          title: "Error",
          text: "Unable to process payment. Please try again.",
        });
      },
    });
  });

  $("#confirmBtn").on("click", function () {
    const amountPaid = $("#amountPaid").val()?.trim();
    const cardPayment = $("#cardPayment").val()?.trim();
    const gcashPayment = $("#gcashPayment").val()?.trim();

    const total =
      parseFloat(
        $("#receiptTotal").text().replace("₱", "").replace(/,/g, ""),
      ) || 0;

    if (
      (!amountPaid || parseFloat(amountPaid) < total) &&
      !cardPayment &&
      !gcashPayment
    ) {
      Swal.fire({
        icon: "warning",
        title: "Incomplete Payment",
        text: "Please enter a valid cash amount or provide card or GCash payment before confirming.",
      });
      return;
    }

    confirmTransaction();
  });

  function confirmTransaction() {
    const transactionNo = $("#transactionNo").text().trim();

    // guard: if these elements don't exist (no discount case), fallback to sidebar totals
    const subTotalEl = $("#receiptSubTotal");
    const discountEl = $("#receiptDiscount");

    const subTotal = subTotalEl.length
      ? parseFloat(subTotalEl.text().replace("₱", "").replace(/,/g, "")) || 0
      : parseFloat(
          $("#subTotal small")
            .text()
            .replace("Sub-Total: ₱", "")
            .replace(/,/g, ""),
        ) || 0;

    const discount = discountEl.length
      ? parseFloat(discountEl.text().replace("₱", "").replace(/,/g, "")) || 0
      : parseFloat(
          $("#subTotal")
            .next("br")
            .next("small")
            .text()
            .replace("Discount: ₱", "")
            .replace(/,/g, ""),
        ) || 0;

    const finalTotal =
      parseFloat(
        $("#receiptTotal").text().replace("₱", "").replace(/,/g, ""),
      ) || 0;
    const paymentMode = $("#cardPayment").is(":visible")
      ? "Card"
      : $("#gcashPayment").is(":visible")
        ? "GCash"
        : "Cash";

    let cashAmount = null;
    let cashChange = null;

    if (paymentMode === "Cash") {
      cashAmount = parseFloat($("#amountPaid").val()) || 0;

      const chTextRaw = ($("#receiptChange").text() || "").trim();
      const chText = chTextRaw.replace("₱", "").replace(/,/g, "").trim();

      // If UI shows "Insufficient", block confirmation
      if (chTextRaw.toLowerCase().includes("insufficient")) {
        Swal.fire({
          icon: "warning",
          title: "Insufficient Cash",
          text: "Please enter an amount equal or greater than the total.",
        });
        return;
      }

      cashChange = parseFloat(chText) || 0;
    }

    const orderData = [];
    $.each(orders, function (name, item) {
      orderData.push({
        name: name,
        qty: item.qty,
        price: item.price,
        total: (item.qty * item.price).toFixed(2),
      });
    });

    Swal.fire({
      title: "Confirm Transaction?",
      text: "This will finalize the sale.",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Confirm",
      cancelButtonText: "Cancel",
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "/confirm-transaction",
          method: "POST",
          dataType: "json",
          data: {
            transaction_no: transactionNo,
            orders: JSON.stringify(orderData),
            subtotal: subTotal,
            discount: discount,
            final_total: finalTotal,
            payment_mode: paymentMode,
            card_number: memberCardNumber,

            // ✅ SEND TO BACKEND
            cash_amount: cashAmount,
            cash_change: cashChange,
          },
          // REPLACE this block in confirmTransaction():
          success: function (response) {
            if (response.status === "success") {
              triggerPrint(response.print_url).then((printRes) => {
                if (printRes && printRes.status === "success") {
                  Swal.fire({
                    icon: "success",
                    title: "Transaction Confirmed!",
                    text: "Sale recorded and receipt printed.",
                    timer: 1500,
                    showConfirmButton: false,
                  }).then(() => {
                    clearCardPaymentState();
                    // ✅ delete AFTER everything is done
                    if (activeLoadedCartId) {
                      $.post("/delete-saved-cart", {
                        cart_id: activeLoadedCartId,
                      });
                      activeLoadedCartId = null;
                      activeLoadedCartName = null;
                    }
                    location.reload();
                  });
                } else {
                  Swal.fire({
                    icon: "warning",
                    title: "Transaction Saved",
                    text:
                      "Sale recorded, but printing failed. " +
                      (printRes?.message ? "(" + printRes.message + ")" : ""),
                  }).then(() => {
                    clearCardPaymentState();
                    // ✅ also clean up on print failure since transaction did save
                    if (activeLoadedCartId) {
                      $.post("/delete-saved-cart", {
                        cart_id: activeLoadedCartId,
                      });
                      activeLoadedCartId = null;
                    }
                    location.reload();
                  });
                }
              });

              // ❌ REMOVE the if (activeLoadedCartId) block that was here
            }
          },
          error: function () {
            Swal.fire({
              icon: "error",
              title: "Server Error",
              text: "Unable to connect to the server.",
            });
          },
        });
      }
    });
  }

  $(document).on("click", "#undoPaymentBtn", function () {
    Swal.fire({
      title: "Undo Card Payment?",
      text: "This will cancel the card payment and reset the wallet balance.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Confirm",
      cancelButtonText: "Cancel",
    }).then((result) => {
      if (result.isConfirmed) {
        undoCardPayment();
      }
    });
  });

  function undoCardPayment() {
    $.ajax({
      url: "/undo-card-payment",
      method: "POST",
      dataType: "json",
      data: {
        card_number: memberCardNumber,
        amount_paid: cardAmountPaid,
      },
      success: function (response) {
        if (response.status === "success") {
          $("#cardPayment").val("").addClass("d-none").prop("disabled", false);
          $("#amountPaid").removeClass("d-none").val("");
          $("#undoPaymentCon").addClass("d-none");
          $("#paymentOptionBtn").removeClass("d-none");
          $("#paymentOptionLbl").text("Cash Payment");
          $("#cardPaymentBtn").text("Pay Via Membership Card");

          cardPaymentDone = false;
          clearCardPaymentState();

          Swal.fire({
            icon: "success",
            title: "Wallet Balance Refunded",
            text: "Card payment undone and wallet balance restored.",
            timer: 2000,
            showConfirmButton: false,
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Failed",
            text: response.message || "Could not reset wallet balance.",
          });
        }
      },
      error: function () {
        Swal.fire({
          icon: "error",
          title: "Server Error",
          text: "Unable to connect to the server.",
        });
      },
    });
  }

  $(document).on("click", "#paymentModal .btn-close", function (e) {
    e.preventDefault();

    if (cardPaymentDone) {
      Swal.fire({
        icon: "warning",
        title: "Cancel Checkout?",
        text: "A card payment has already been processed. Continuing will reset the transaction and refund the wallet.",
        showCancelButton: true,
        confirmButtonText: "Confirm",
        cancelButtonText: "Cancel",
      }).then((result) => {
        if (result.isConfirmed) {
          undoCardPayment();
          clearCardPaymentState();
        } else {
          $("#paymentModal").modal("show");
        }
      });
    } else {
      Swal.fire({
        icon: "question",
        title: "Cancel Checkout?",
        text: "Are you sure you want to cancel the checkout?",
        showCancelButton: true,
        confirmButtonText: "Confirm",
        cancelButtonText: "Cancel",
      }).then((result) => {
        if (result.isConfirmed) {
          $("#paymentModal").modal("hide");
        } else {
          $("#paymentModal").modal("show");
        }
      });
    }
  });

  function setCardPaymentDone(cardNumber, amountPaid) {
    sessionStorage.setItem("cardPaymentDone", "true");
    sessionStorage.setItem("memberCardNumber", cardNumber);
    sessionStorage.setItem("cardAmountPaid", amountPaid);
  }

  function clearCardPaymentState() {
    sessionStorage.removeItem("cardPaymentDone");
    sessionStorage.removeItem("memberCardNumber");
    sessionStorage.removeItem("cardAmountPaid");
  }

  window.addEventListener("beforeunload", function (e) {
    if (sessionStorage.getItem("cardPaymentDone") === "true") {
      const msg =
        "A card payment was processed. Reloading will reset the transaction and refund the wallet.";
      e.preventDefault();
      e.returnValue = msg;
      return msg;
    }
  });

  $(window).on("load", function () {
    if (sessionStorage.getItem("cardPaymentDone") === "true") {
      const pendingCard = sessionStorage.getItem("memberCardNumber") || "";
      const pendingAmount =
        parseFloat(sessionStorage.getItem("cardAmountPaid") || "0") || 0;

      $.ajax({
        url: "/undo-card-payment",
        method: "POST",
        dataType: "json",
        data: {
          card_number: pendingCard,
          amount_paid: pendingAmount,
        },
        success: function (res) {
          if (res.status === "success") {
            clearCardPaymentState();
            Swal.fire({
              icon: "info",
              title: "Card Payment Refunded",
              text: "A previous card payment was detected and has been refunded for your security.",
              confirmButtonColor: "#3085d6",
            }).then(() => {
              $("#paymentModal").modal("hide");
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Refund Failed",
              text: "We could not process the refund automatically. Please check the wallet balance manually.",
              confirmButtonColor: "#d33",
            });
          }
        },
        error: function () {
          Swal.fire({
            icon: "error",
            title: "Server Error",
            text: "Unable to contact server to verify the card payment.",
            confirmButtonColor: "#d33",
          });
        },
      });
    }
  });

  let selectedTransaction = null;

  $("#orderSumBtn").on("click", function () {
    $("#transactionSideBar").addClass("d-none");
    $("#savedCartsSideBar").addClass("d-none");
    $("#sidebar").removeClass("d-none");
    $("#transactionBtn").removeClass("d-none");
    $("#savedCartsBtn").removeClass("d-none");
    $(this).addClass("d-none");
  });

  $("#transactionBtn").on("click", function () {
    $("#transactionSideBar").removeClass("d-none");
    $("#savedCartsSideBar").addClass("d-none");
    $("#sidebar").addClass("d-none");
    $("#orderSumBtn").removeClass("d-none");
    $("#savedCartsBtn").removeClass("d-none");
    $(this).addClass("d-none");
  });

  $(document).on("click", ".transaction-item", function () {
    $(".transaction-item").removeClass("selected");
    $(this).addClass("selected");

    selectedTransaction = $(this).find("#saleId").val()?.trim() || null;

    if (selectedTransaction) {
      $("#viewBtn").prop("disabled", false);
      $("#removeBtn").prop("disabled", false);
    }
  });

  $("#viewBtn").on("click", function () {
    const transactionNumber = $(".transaction-list .d-flex.bg-primary")
      .find(".transaction-number")
      .text()
      .trim();

    $("#transactionModal").modal("show");

    $(".modal-body").html(
      '<p class="text-center text-muted">Loading transaction details...</p>',
    );

    $("#confirmBtn").prop("disabled", true);

    $.ajax({
      url: "/get-sales-items",
      method: "POST",
      dataType: "json",
      data: {
        sale_id: selectedTransaction,
      },
      success: function (response) {
        if (response.status === "success") {
          const items = response.items || [];
          const subtotal = parseFloat(response.subtotal || 0);
          const discount = parseFloat(response.discount || 0);
          const total = parseFloat(response.total || 0);
          const mode = response.mode || "N/A";

          let itemRows = "";

          items.forEach((item) => {
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

          $(".modal-body").html(receiptHTML);
          $("#confirmBtn").prop("disabled", false);
        } else {
          $(".modal-body").html(
            '<p class="text-danger text-center">Failed to load transaction details.</p>',
          );
        }
      },
      error: function () {
        $(".modal-body").html(
          '<p class="text-danger text-center">Server error while loading transaction.</p>',
        );
      },
    });
  });

  $("#removeBtn").on("click", function () {
    let saleId = selectedTransaction;

    $(document).off("click.transactionDeselect");
    Swal.fire({
      title: "Admin Verification Required",
      input: "password",
      inputLabel: "Enter Admin Username",
      inputPlaceholder: "Admin Username",
      inputAttributes: {
        autocapitalize: "off",
      },
      showCancelButton: true,
      confirmButtonText: "Verify & Remove",
      showLoaderOnConfirm: true,
      preConfirm: (adminId) => {
        return $.ajax({
          url: "/verify-admin",
          method: "POST",
          dataType: "json",
          data: {
            username: adminId,
          },
        })
          .then((response) => {
            if (!response.valid) {
              throw new Error(response.message || "Invalid admin ID");
            }
            return response;
          })
          .catch((error) => {
            Swal.showValidationMessage(
              error.message || "Verification failed. Please try again.",
            );
          });
      },
      allowOutsideClick: () => !Swal.isLoading(),
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: "/remove-transaction",
          method: "POST",
          dataType: "json",
          data: {
            sale_id: saleId,
          },
          success: function (res) {
            if (res.status === "success") {
              let alertMsg = `Transaction <b>${res.transactionNum}</b> has been deleted.`;

              if (res.refund) {
                alertMsg += `<br><br><b>Wallet refund:</b> ₱${res.refund.amount} refunded to ${res.refund.member_name}<br><b>Card Number: </b>${res.refund.membership_id}`;
              }

              Swal.fire({
                icon: "success",
                title: "Transaction Removed",
                html: alertMsg,
                confirmButtonText: "OK",
              }).then(() => {
                location.reload();
              });
            } else {
              Swal.fire({
                icon: "error",
                title: "Failed",
                text: res.message || "Unable to delete transaction.",
              });
            }
          },

          error: function () {
            Swal.fire({
              icon: "error",
              title: "Server Error",
              text: "Could not contact server.",
            });
          },
        });
      }
    });
  });

  // ─── SAVED CARTS ───────────────────────────────────────────────────────────

  let selectedSavedCart = null;

  // Load saved carts from server and render the list
  function loadSavedCarts() {
    $.ajax({
      url: "/saved-carts",
      method: "GET",
      dataType: "json",
      success: function (response) {
        const list = $(".saved-carts-list");
        list.empty();

        const carts = response.carts || [];

        // Update badge count in header button
        if (carts.length > 0) {
          $("#savedCartsBadge").text(carts.length).removeClass("d-none");
        } else {
          $("#savedCartsBadge").addClass("d-none");
        }

        if (carts.length === 0) {
          list.html(
            '<div class="text-center p-3"><small class="text-muted">No saved carts</small></div>',
          );
          return;
        }

        carts.forEach(function (cart) {
          list.append(`
          <div class="transaction-item d-flex mx-3 justify-content-between flex-column saved-cart-item" data-cart-id="${cart.id}">
            <input type="hidden" class="cartId" value="${cart.id}">
            <span class="fw-bold">${cart.customer_name}</span>
            <small class="ps-1">${cart.item_count} item(s) &mdash; ₱${parseFloat(cart.total).toFixed(2)}</small>
            <small class="text-muted ps-1">${new Date(cart.created_at).toLocaleTimeString("en-US", { hour: "2-digit", minute: "2-digit" })}</small>
          </div>
        `);
        });
      },
      error: function () {
        $(".saved-carts-list").html(
          '<div class="text-center p-3"><small class="text-danger">Failed to load carts</small></div>',
        );
      },
    });
  }

  loadSavedCarts();

  // Show/hide saved carts sidebar
  $("#savedCartsBtn").on("click", function () {
    $("#sidebar").addClass("d-none");
    $("#transactionSideBar").addClass("d-none");
    $("#savedCartsSideBar").removeClass("d-none");
    $("#orderSumBtn").removeClass("d-none");
    $("#transactionBtn").removeClass("d-none");
    $(this).addClass("d-none");
    loadSavedCarts();
  });

  // Select a saved cart item
  $(document).on("click", ".saved-cart-item", function () {
    $(".saved-cart-item").removeClass("selected");
    $(this).addClass("selected");
    selectedSavedCart = $(this).data("cart-id");
    $("#loadCartBtn, #deleteCartBtn").prop("disabled", false);
  });

  // Load cart back into active order
  $("#loadCartBtn").on("click", function () {
    if (!selectedSavedCart) return;

    if (!$.isEmptyObject(orders)) {
      Swal.fire({
        icon: "warning",
        title: "Active order exists",
        text: "Clear the current order before loading a saved cart.",
      });
      return;
    }

    $.ajax({
      url: "/load-cart",
      method: "POST",
      dataType: "json",
      data: { cart_id: selectedSavedCart },
      success: function (response) {
        if (response.status === "success") {
          orders = {};
          response.orders.forEach(function (item) {
            orders[item.name] = {
              qty: item.qty,
              price: parseFloat(item.price),
            };
          });

          renderOrderList();

          // Switch to order sidebar
          $("#savedCartsSideBar").addClass("d-none");
          $("#sidebar").removeClass("d-none");
          $("#orderSumBtn").addClass("d-none");
          $("#savedCartsBtn").removeClass("d-none");
          $("#transactionBtn").removeClass("d-none");

          // Update the transaction number display
          $("#transaction-number").text(
            response.transaction_no || $("#transaction-number").text(),
          );

          Swal.fire({
            icon: "success",
            title: "Cart loaded!",
            text: `${response.customer_name}'s order is ready.`,
            timer: 1500,
            showConfirmButton: false,
          });

          activeLoadedCartId = selectedSavedCart;
          activeLoadedCartName = response.customer_name;

          loadSavedCarts(); // refresh badge
        } else {
          Swal.fire(
            "Error",
            response.message || "Failed to load cart.",
            "error",
          );
        }
      },
      error: function () {
        Swal.fire("Server Error", "Unable to load cart.", "error");
      },
    });
  });

  // Delete a saved cart
  $("#deleteCartBtn").on("click", function () {
    if (!selectedSavedCart) return;

    const cartName = $(".saved-cart-item.selected").find(".fw-bold").text();

    Swal.fire({
      title: `Delete ${cartName}'s cart?`,
      text: "This cannot be undone.",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#d33",
      confirmButtonText: "Delete",
    }).then(function (result) {
      if (result.isConfirmed) {
        $.ajax({
          url: "/delete-saved-cart",
          method: "POST",
          dataType: "json",
          data: { cart_id: selectedSavedCart },
          success: function (response) {
            if (response.status === "success") {
              selectedSavedCart = null;
              $("#loadCartBtn, #deleteCartBtn").prop("disabled", true);
              loadSavedCarts();
              Swal.fire({
                icon: "success",
                title: "Cart deleted",
                timer: 1200,
                showConfirmButton: false,
              });
            } else {
              Swal.fire("Error", response.message, "error");
            }
          },
          error: function () {
            Swal.fire("Server Error", "Could not delete cart.", "error");
          },
        });
      }
    });
  });

  // Save current order as a cart (pay later)
  $("#saveCartBtn").on("click", function () {
    if ($.isEmptyObject(orders)) {
      Swal.fire({
        icon: "warning",
        title: "No items",
        text: "Add items before saving a cart.",
      });
      return;
    }

    // If a cart is already loaded, skip the name prompt
    if (activeLoadedCartId) {
      resaveCart(activeLoadedCartName);
      return;
    }

    Swal.fire({
      title: "Save Cart",
      input: "text",
      inputLabel: "Customer name",
      inputPlaceholder: "e.g. Juan dela Cruz",
      inputAttributes: { maxlength: 80 },
      showCancelButton: true,
      confirmButtonText: "Save",
      inputValidator: (value) => {
        if (!value?.trim()) return "Please enter a customer name.";
      },
    }).then(function (result) {
      if (result.isConfirmed) {
        resaveCart(result.value.trim());
      }
    });
  });

  function resaveCart(customerName) {
    const orderData = [];
    let subtotal = 0;

    $.each(orders, function (name, item) {
      const total = item.qty * item.price;
      subtotal += total;
      orderData.push({
        name,
        qty: item.qty,
        price: item.price,
        total: total.toFixed(2),
      });
    });

    $.ajax({
      url: "/save-cart",
      method: "POST",
      dataType: "json",
      data: {
        customer_name: customerName,
        orders: JSON.stringify(orderData),
        subtotal: subtotal.toFixed(2),
        total: subtotal.toFixed(2),
      },
      success: function (response) {
        if (response.status === "success") {
          if (activeLoadedCartId) {
            $.post("/delete-saved-cart", { cart_id: activeLoadedCartId });
            activeLoadedCartId = null;
            activeLoadedCartName = null;
          }

          orders = {};
          renderOrderList();
          loadSavedCarts();

          Swal.fire({
            icon: "success",
            title: "Cart saved!",
            text: `${customerName}'s order saved. You can load it when they're ready to pay.`,
            timer: 2000,
            showConfirmButton: false,
          });
        } else {
          Swal.fire(
            "Error",
            response.message || "Could not save cart.",
            "error",
          );
        }
      },
      error: function () {
        Swal.fire("Server Error", "Unable to save cart.", "error");
      },
    });
  }

  // Also auto-delete the saved cart after a successful confirm-transaction
  // Patch into the existing confirmTransaction success handler:
  // After `location.reload()` in the success block, add a call to
  // /delete-saved-cart if a cart was loaded. Track this with a variable:
});
