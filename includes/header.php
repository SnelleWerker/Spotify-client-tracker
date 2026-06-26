<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle ?? 'Spotify Tracker') ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="nav">
        <a href="dashboard.php" class="nav-brand">🎵 Spotify Tracker</a>
        <div class="nav-links">
            <a href="dashboard.php"<?= ($currentPage ?? '') === 'dashboard' ? ' class="active"' : '' ?>>Dashboard</a>
            <a href="users.php"<?= ($currentPage ?? '') === 'users' ? ' class="active"' : '' ?>>Users</a>
            <a href="accounts.php"<?= ($currentPage ?? '') === 'accounts' ? ' class="active"' : '' ?>>Accounts</a>
            <a href="reset_payments.php"<?= ($currentPage ?? '') === 'reset' ? ' class="active"' : '' ?>>Reset Payments</a>
        </div>
    </nav>
    <main class="container">
