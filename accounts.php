<?php
require_once __DIR__ . '/config.php';

$db = getDB();

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare('DELETE FROM spotify_accounts WHERE id = ?');
    $stmt->execute([$id]);
    redirect('accounts.php?msg=deleted');
}

$accounts = $db->query('
    SELECT a.*, COUNT(u.id) AS user_count
    FROM spotify_accounts a
    LEFT JOIN users u ON u.spotify_account_id = a.id
    GROUP BY a.id
    ORDER BY a.account_name
')->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Spotify Accounts';
$currentPage = 'accounts';
require_once __DIR__ . '/includes/header.php';

$msg = $_GET['msg'] ?? '';
?>

<div class="page-header">
    <h1>Spotify Accounts</h1>
    <a href="add_account.php" class="btn btn-primary">+ Add Account</a>
</div>

<?php if ($msg === 'deleted'): ?>
    <div class="message message-success">Account deleted successfully.</div>
<?php elseif ($msg === 'created'): ?>
    <div class="message message-success">Account created successfully.</div>
<?php elseif ($msg === 'updated'): ?>
    <div class="message message-success">Account updated successfully.</div>
<?php endif; ?>

<?php if (empty($accounts)): ?>
    <div class="empty-state">
        <p>No accounts found. <a href="add_account.php">Add an account</a></p>
    </div>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>Account Name</th>
            <th>Users Linked</th>
            <th>Notes</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($accounts as $account): ?>
        <tr>
            <td><?= h($account['account_name']) ?></td>
            <td>
                <?= (int) $account['user_count'] ?> / 5
                <?php if ((int) $account['user_count'] >= 5): ?>
                    <span class="status-badge status-overdue">Full</span>
                <?php endif; ?>
            </td>
            <td><?= $account['notes'] ? h($account['notes']) : '—' ?></td>
            <td class="actions">
                <a href="edit_account.php?id=<?= (int) $account['id'] ?>" class="btn btn-secondary btn-small">Edit</a>
                <a href="accounts.php?delete=<?= (int) $account['id'] ?>"
                   class="btn btn-danger btn-small"
                   onclick="return confirm('Delete this account and all its users?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
