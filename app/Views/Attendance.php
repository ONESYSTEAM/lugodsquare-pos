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

                        <h4 id="attendance-header">Hello! let's get started</h4>
                        <h6 id="attendance-subheader" class="font-weight-light">Scan your ID to time in / time out.</h6>

                        <form id="attendance-form" action="/attendance/submit" method="post" class="pt-3">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control form-control-lg" name="idNumber" placeholder="ID Number" autofocus>
                            </div>
                            <div class="mt-3 d-grid gap-2">
                                <button type="submit" id="attendance-btn" class="btn btn-block btn-custom btn-lg font-weight-medium auth-form-btn text-uppercase">
                                    Confirm
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

<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>


<?php $this->stop(); ?>