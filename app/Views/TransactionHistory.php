<?php
$this->layout('Layout/Layout', ['mainContent' => $this->fetch('Layout/Layout')]);
$this->start('mainContent');
$this->insert('Errors/Toasts');
?>

<style>
    .text-danger{
     color: #dc3545 !important;   
    }
</style>

<!-- Add your content here to be displayed in the browser -->
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
        <a href="/transaction-history" class="btn btn-light btn-sm text-danger fw-bold"><i class="bi bi-arrow-left-short"></i> Back</a>
        <a href="/logout" class="btn btn-light btn-sm text-danger fw-bold">
            <i class="mdi mdi-logout"></i> Logout
        </a>
    </div>
</div>
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product Number</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <!-- <?php if (!empty($products)): ?>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= $product['product_number'] ?></td>
                                        <td><?= $product['product_name'] ?></td>
                                        <td>₱<?= $product['price'] ?>.00</td>
                                        <td>
                                            <a href="/viewUser/<?= $product['id'] ?>" class="btn btn-primary btn-sm"><i class="fa fa-eye"></i> View</a>
                                            <a href="/updateProduct/<?= $product['id'] ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Update</a>
                                            <a href="/deleteProduct/<?= $product['id'] ?>" class="btn btn-primary btn-sm"><i class="fa fa-trash-o"></i> Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        <?php else: ?> -->
                        <tbody>
                            <tr>
                                <td colspan="4" class="text-center">No products found.</td>
                            </tr>
                        </tbody>
                        <!-- <?php endif; ?> -->
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$this->stop();
?>