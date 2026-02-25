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

                        <h4><?= $isTimedIn ? 'Thank you for your service!' : 'Hello! Ready to start your day?' ?></h4>
                        <h6 class="font-weight-light">
                            <?= $isTimedIn ? 'Scan your ID to time out.' : 'Scan your ID to time in.' ?>
                        </h6>

                        <form action="<?= $isTimedIn ? 'attendance/timeOut' : 'attendance/timeIn' ?>" method="post" class="pt-3">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control form-control-lg" name="idNumber" placeholder="ID Number" autofocus>
                            </div>
                            <div class="mt-3 d-grid gap-2">
                                <button type="submit" class="btn btn-block <?= $isTimedIn ? 'btn-danger' : 'btn-custom' ?> btn-lg font-weight-medium auth-form-btn text-uppercase">
                                    <?= $isTimedIn ? 'Time Out' : 'Time In' ?>
                                </button>
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

<?php $this->stop(); ?>