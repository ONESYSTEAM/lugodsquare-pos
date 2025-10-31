<?php
$this->layout('Layout/DashboardLayout', ['title' => 'Restaurant POS']);
$this->start('mainContent');
?>

<div class="row g-3">
    <?php
    $products = [
        ['name' => 'Cheeseburger', 'price' => 120, 'image' => 'https://source.unsplash.com/400x300/?burger'],
        ['name' => 'Pepperoni Pizza', 'price' => 350, 'image' => 'https://source.unsplash.com/400x300/?pizza'],
        ['name' => 'Spaghetti Bolognese', 'price' => 180, 'image' => 'https://source.unsplash.com/400x300/?pasta'],
        ['name' => 'Iced Coffee', 'price' => 90, 'image' => 'https://source.unsplash.com/400x300/?coffee'],
        ['name' => 'Chicken Sandwich', 'price' => 150, 'image' => 'https://source.unsplash.com/400x300/?sandwich'],
        ['name' => 'French Fries', 'price' => 70, 'image' => 'https://source.unsplash.com/400x300/?fries'],
    ];

    foreach ($products as $product): ?>
        <div class="col-6 col-sm-6 col-md-4 col-lg-3">
            <div class="product-card" onclick="addToOrder('<?= $product['name'] ?>', <?= $product['price'] ?>)">
                <img src="<?= $product['image'] ?>" alt="<?= $product['name'] ?>">
                <div class="product-info text-center">
                    <h6 class="fw-bold mb-1"><?= $product['name'] ?></h6>
                    <p class="text-muted mb-2">₱<?= number_format($product['price'], 2) ?></p>
                    <button class="btn btn-sm btn-outline-danger w-100">Add to Order</button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    let subtotal = 0;
    const orderList = document.getElementById('order-items');
    const subtotalEl = document.getElementById('subtotal');

    function addToOrder(name, price) {
        if (orderList.querySelector('.text-muted')) orderList.innerHTML = '';
        const item = document.createElement('div');
        item.classList.add('d-flex', 'justify-content-between', 'align-items-center', 'mb-2');
        item.innerHTML = `<span>${name}</span><strong>₱${price.toFixed(2)}</strong>`;
        orderList.appendChild(item);
        subtotal += price;
        subtotalEl.textContent = '₱' + subtotal.toFixed(2);
    }
</script>

<?php $this->stop(); ?>
