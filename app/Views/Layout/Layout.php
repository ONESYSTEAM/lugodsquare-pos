<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($_ENV['APP_DESCRIPTION'] ?? '') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($_ENV['APP_KEYWORDS'] ?? '') ?>">
    <meta name="author" content="<?= htmlspecialchars($_ENV['APP_AUTHOR'] ?? '') ?>">

    <link rel="shortcut icon" href="<?= htmlspecialchars($_ENV['APP_ICON'] ?? '') ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= assets('css/style.css') ?>">

    <title><?= isset($title) && !empty($title) ? $this->e($title) : htmlspecialchars($_ENV['APP_NAME'] ?? '') ?></title>
</head>

<style>
    .powered-by {
        position: fixed;
        bottom: 10px;
        right: 15px;
        font-size: 0.875rem;
        color: #6c757d;
        z-index: 999;
        background-color: rgba(255, 255, 255, 0.6);
        padding: 6px 12px;
        border-radius: 4px;
    }
</style>

<body>

    <main>
        <?= $this->section('mainContent') ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= assets("js/Toasts.js") ?>"></script>

</body>

</html>