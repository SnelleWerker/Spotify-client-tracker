<?php
require_once __DIR__ . '/config.php';

$db = getDB();
$search = trim($_GET['q'] ?? '');

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $db->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$id]);
    redirect('users.php?msg=deleted');
}

$sql = '
    SELECT u.*, a.account_name
    FROM users u
    JOIN spotify_accounts a ON u.spotify_account_id = a.id
';

if ($search !== '') {
    $sql .= ' WHERE LOWER(u.name) LIKE ? OR LOWER(u.username) LIKE ? ';
}

$sql .= ' ORDER BY u.name';
$stmt = $db->prepare($sql);

if ($search !== '') {
    $searchLike = '%' . strtolower($search) . '%';
    $stmt->execute([$searchLike, $searchLike]);
} else {
    $stmt->execute();
}

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'Users';
$currentPage = 'users';
require_once __DIR__ . '/includes/header.php';

$msg = $_GET['msg'] ?? '';
?>

<div class="page-header">
    <h1>Users</h1>
    <a href="add_user.php" class="btn btn-primary">+ Add User</a>
</div>

<?php if ($msg === 'deleted'): ?>
    <div class="message message-success">User deleted successfully.</div>
<?php elseif ($msg === 'created'): ?>
    <div class="message message-success">User created successfully.</div>
<?php elseif ($msg === 'updated'): ?>
    <div class="message message-success">User updated successfully.</div>
<?php endif; ?>

<form method="get" class="search-bar">
    <input
        type="text"
        name="q"
        value="<?= h($search) ?>"
        placeholder="Search by name or username"
        autocomplete="off"
    >
    <button type="submit" class="btn btn-secondary">Search</button>
    <?php if ($search !== ''): ?>
        <a href="users.php" class="btn btn-danger">Clear</a>
    <?php endif; ?>
</form>

<?php if (empty($users)): ?>
    <div class="empty-state">
        <?php if ($search !== ''): ?>
            <p>No users found for "<?= h($search) ?>".</p>
        <?php else: ?>
            <p>No users found. <a href="add_user.php">Add a user</a></p>
        <?php endif; ?>
    </div>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Username</th>
            <th>Account</th>
            <th>Joined</th>
            <th>Renewal Day</th>
            <th>Last Payment</th>
            <th>Months Ahead</th>
            <th>Paid</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= h($user['name']) ?></td>
            <td><?= h($user['username']) ?></td>
            <td><?= h($user['account_name']) ?></td>
            <td><?= h($user['date_joined']) ?></td>
            <td><?= (int) $user['renewal_day'] ?></td>
            <td><?= $user['last_payment_date'] ? h($user['last_payment_date']) : '—' ?></td>
            <td><?= (int) $user['months_paid_in_advance'] ?></td>
            <td><?= $user['is_paid'] ? '✓ Yes' : '✗ No' ?></td>
            <td class="actions">
                <a href="edit_user.php?id=<?= (int) $user['id'] ?>" class="btn btn-secondary btn-small">Edit</a>
                <a href="users.php?delete=<?= (int) $user['id'] ?>"
                   class="btn btn-danger btn-small"
                   onclick="return confirm('Delete this user?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
