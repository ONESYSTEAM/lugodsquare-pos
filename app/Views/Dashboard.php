<?php
$this->layout('Layout/DashboardLayout', ['mainContent' => $this->fetch('Layout/DashboardLayout')]);
$this->start('mainContent');
$this->insert('Errors/Toasts');
?>

<?php $this->stop(); ?>