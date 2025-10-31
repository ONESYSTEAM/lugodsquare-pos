<?php
$this->layout('Layout/Layout', ['mainContent' => $this->fetch('Layout/Layout')]);
$this->start('mainContent');
$this->insert('Errors/Toasts');
?>

<div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="content-wrapper d-flex align-items-center auth">
            <div class="row flex-grow">
                <div class="col-lg-4 mx-auto">
                    <div class="auth-form-light text-left p-5">
                        <div class="brand-logo text-center">
                            <img src="<?= htmlspecialchars($_ENV['APP_LOGO'] ?? '') ?>">
                        </div>
                        <h4>Hello! let's get started</h4>
                        <h6 class="font-weight-light">Login to continue.</h6>
                        <form action="/login" method="post" class="pt-3">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control form-control-lg" name="username" placeholder="Username">
                            </div>
                            <div class="form-group">
                                <input type="password" class="form-control form-control-lg" name="password" placeholder="Password">
                            </div>
                            <div class="mt-3 d-grid gap-2">
                                <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn text-uppercase">Login</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="powered-by">
    Powered by: <a href="https://onesysteam.com/" class="text-decoration-none text-danger" target="_blank">OneSysteam</a>
</div>

<?php
$this->stop();
?>