<?php
require_once __DIR__ . '/config.php';

$db = getDB();
$error = '';
$id = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare('SELECT * FROM spotify_accounts WHERE id = ?');
$stmt->execute([$id]);
$account = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$account) {
    redirect('accounts.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['account_name'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if ($name === '') {
        $error = 'Account name is required.';
    } else {
        $stmt = $db->prepare('UPDATE spotify_accounts SET account_name = ?, notes = ? WHERE id = ?');
        $stmt->execute([$name, $notes !== '' ? $notes : null, $id]);
        redirect('accounts.php?msg=updated');
    }
}

$pageTitle = 'Edit Account';
$currentPage = 'accounts';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Edit Spotify Account</h1>
    <a href="accounts.php" class="btn btn-secondary">← Back</a>
</div>

<?php if ($error): ?>
    <div class="message message-error"><?= h($error) ?></div>
<?php endif; ?>

<form method="post" class="form-card">
    <div class="form-group">
        <label for="account_name">Account Name *</label>
        <input type="text" id="account_name" name="account_name" required value="<?= h($_POST['account_name'] ?? $account['account_name']) ?>">
    </div>
    <div class="form-group">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes"><?= h($_POST['notes'] ?? $account['notes'] ?? '') ?></textarea>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Update Account</button>
        <a href="accounts.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
