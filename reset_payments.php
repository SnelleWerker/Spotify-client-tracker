<?php
require_once __DIR__ . '/config.php';

$db = getDB();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_all'])) {
    $db->exec('UPDATE users SET is_paid = 0');
    $message = 'All users have been marked as unpaid. Update payments as they come in.';
}

$users = $db->query('
    SELECT u.name, u.is_paid, a.account_name
    FROM users u
    JOIN spotify_accounts a ON u.spotify_account_id = a.id
    ORDER BY u.name
')->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Reset Payments';
$currentPage = 'reset';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Reset Monthly Payments</h1>
</div>

<p style="color:#b3b3b3; margin-bottom:1.5rem;">
    Use this at the start of each billing cycle to mark everyone as unpaid.
    Then check off users on the dashboard as they pay.
</p>

<?php if ($message): ?>
    <div class="message message-success"><?= h($message) ?></div>
<?php endif; ?>

<?php if (!empty($users)): ?>
<table style="margin-bottom:1.5rem">
    <thead>
        <tr>
            <th>Name</th>
            <th>Account</th>
            <th>Currently Paid</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= h($user['name']) ?></td>
            <td><?= h($user['account_name']) ?></td>
            <td><?= $user['is_paid'] ? '✓ Yes' : '✗ No' ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<form method="post" class="form-card" onsubmit="return confirm('Mark ALL users as unpaid?')">
    <p style="margin-bottom:1rem">This will set <strong>is_paid = 0</strong> for every user.</p>
    <div class="form-actions">
        <button type="submit" name="reset_all" value="1" class="btn btn-danger">Reset All Payments</button>
        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
