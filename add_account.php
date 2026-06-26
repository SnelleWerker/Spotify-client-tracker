<?php
require_once __DIR__ . '/config.php';

$db = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['account_name'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($name === '') {
        $error = 'Account name is required.';
    } else {
        $stmt = $db->prepare('INSERT INTO spotify_accounts (account_name, notes) VALUES (?, ?)');
        $stmt->execute([$name, $notes !== '' ? $notes : null]);
        redirect('accounts.php?msg=created');
    }
}

$pageTitle = 'Add Account';
$currentPage = 'accounts';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Add Spotify Account</h1>
    <a href="accounts.php" class="btn btn-secondary">← Back</a>
</div>

<?php if ($error): ?>
    <div class="message message-error"><?= h($error) ?></div>
<?php endif; ?>

<form method="post" class="form-card">
    <div class="form-group">
        <label for="account_name">Account Name *</label>
        <input type="text" id="account_name" name="account_name" required value="<?= h($_POST['account_name'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes"><?= h($_POST['notes'] ?? '') ?></textarea>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Account</button>
        <a href="accounts.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
