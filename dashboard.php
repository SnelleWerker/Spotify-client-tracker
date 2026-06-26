<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/payment.php';

$db = getDB();
$search = trim($_GET['q'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_paid'])) {
    $userId = (int) $_POST['user_id'];
    $isPaid = isset($_POST['is_paid']) ? 1 : 0;

    if ($isPaid) {
        $stmt = $db->prepare('UPDATE users SET is_paid = 1, last_payment_date = CURDATE() WHERE id = ?');
    } else {
        $stmt = $db->prepare('UPDATE users SET is_paid = 0 WHERE id = ?');
    }
    $stmt->execute([$userId]);
    redirect('dashboard.php');
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

$alerts = getDashboardAlerts($users);

$totalUsers = count($users);
$paidCount = count(array_filter($users, fn($u) => $u['is_paid']));
$overdueCount = 0;
$dueSoonCount = 0;
foreach ($users as $user) {
    $info = getPaymentStatus($user);
    if (!(bool) $user['is_paid']) {
        if ($info['days_diff'] < 0) {
            $overdueCount++;
        } elseif ($info['days_diff'] <= 2) {
            $dueSoonCount++;
        }
    }
}

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Dashboard</h1>
</div>

<div class="stats">
    <div class="stat-card">
        <div class="number"><?= $totalUsers ?></div>
        <div class="label">Total Users</div>
    </div>
    <div class="stat-card">
        <div class="number"><?= $paidCount ?></div>
        <div class="label">Paid</div>
    </div>
    <div class="stat-card">
        <div class="number" style="color:#f5a623"><?= $dueSoonCount ?></div>
        <div class="label">Due Soon</div>
    </div>
    <div class="stat-card">
        <div class="number" style="color:#e91429"><?= $overdueCount ?></div>
        <div class="label">Overdue</div>
    </div>
</div>

<?php if (!empty($alerts)): ?>
<details class="alerts reminders-panel" open>
    <summary>Reminders (<?= count($alerts) ?>)</summary>
    <div class="reminders-content">
        <?php foreach ($alerts as $alert): ?>
            <div class="alert alert-<?= h($alert['type']) ?>"><?= h($alert['text']) ?></div>
        <?php endforeach; ?>
    </div>
</details>
<?php endif; ?>

<h2>All Users</h2>
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
        <a href="dashboard.php" class="btn btn-danger">Clear</a>
    <?php endif; ?>
</form>

<?php if (empty($users)): ?>
    <div class="empty-state">
        <?php if ($search !== ''): ?>
            <p>No users found for "<?= h($search) ?>".</p>
        <?php else: ?>
            <p>No users yet. <a href="add_user.php">Add your first user</a></p>
        <?php endif; ?>
    </div>
<?php else: ?>
<div class="table-scroll">
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Username</th>
            <th>Account</th>
            <th>Joined</th>
            <th>Renewal Day</th>
            <th>Next Due</th>
            <th>Status</th>
            <th>Paid?</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user):
            $info = getPaymentStatus($user);
        ?>
        <tr>
            <td><?= h($user['name']) ?></td>
            <td><?= h($user['username']) ?></td>
            <td><?= h($user['account_name']) ?></td>
            <td><?= h($user['date_joined']) ?></td>
            <td><?= (int) $user['renewal_day'] ?></td>
            <td><?= h($info['renewal_date']) ?></td>
            <td>
                <span class="status-badge <?= h($info['class']) ?>"><?= h($info['status']) ?></span>
                <br><small style="color:#b3b3b3"><?= h($info['message']) ?></small>
            </td>
            <td>
                <form method="post" class="inline-form">
                    <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                    <input type="checkbox" name="is_paid" value="1"
                        <?= $user['is_paid'] ? 'checked' : '' ?>
                        onchange="this.form.submit()">
                    <input type="hidden" name="toggle_paid" value="1">
                </form>
            </td>
            <td class="actions">
                <a href="edit_user.php?id=<?= (int) $user['id'] ?>" class="btn btn-secondary btn-small">Edit</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
