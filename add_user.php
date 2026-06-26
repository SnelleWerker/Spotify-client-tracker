<?php
require_once __DIR__ . '/config.php';

$db = getDB();
$error = '';

$accounts = $db->query('
    SELECT a.*, COUNT(u.id) AS user_count
    FROM spotify_accounts a
    LEFT JOIN users u ON u.spotify_account_id = a.id
    GROUP BY a.id
    ORDER BY a.account_name
')->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $dateJoined = $_POST['date_joined'] ?? '';
    $renewalDay = (int) ($_POST['renewal_day'] ?? 0);
    $accountId = (int) ($_POST['spotify_account_id'] ?? 0);
    $monthsPaid = max(1, (int) ($_POST['months_paid_in_advance'] ?? 1));
    $isPaid = isset($_POST['is_paid']) ? 1 : 0;
    $lastPayment = $_POST['last_payment_date'] ?? '';

    if ($name === '' || $username === '' || $dateJoined === '' || $renewalDay < 1 || $renewalDay > 31 || $accountId < 1) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE spotify_account_id = ?');
        $stmt->execute([$accountId]);
        if ((int) $stmt->fetchColumn() >= 5) {
            $error = 'This Spotify account already has 5 users (maximum).';
        } else {
            $lastPaymentDate = $lastPayment !== '' ? $lastPayment : null;
            $stmt = $db->prepare('
                INSERT INTO users (name, username, date_joined, renewal_day, last_payment_date, months_paid_in_advance, is_paid, spotify_account_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([$name, $username, $dateJoined, $renewalDay, $lastPaymentDate, $monthsPaid, $isPaid, $accountId]);
            redirect('users.php?msg=created');
        }
    }
}

$pageTitle = 'Add User';
$currentPage = 'users';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Add User</h1>
    <a href="users.php" class="btn btn-secondary">← Back</a>
</div>

<?php if ($error): ?>
    <div class="message message-error"><?= h($error) ?></div>
<?php endif; ?>

<?php if (empty($accounts)): ?>
    <div class="message message-error">
        No Spotify accounts found. <a href="add_account.php">Create an account first</a>.
    </div>
<?php else: ?>
<form method="post" class="form-card">
    <div class="form-group">
        <label for="name">Name *</label>
        <input type="text" id="name" name="name" required value="<?= h($_POST['name'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="username">Username *</label>
        <input type="text" id="username" name="username" required value="<?= h($_POST['username'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="date_joined">Date Joined *</label>
        <input type="date" id="date_joined" name="date_joined" required value="<?= h($_POST['date_joined'] ?? date('Y-m-d')) ?>">
    </div>
    <div class="form-group">
        <label for="renewal_day">Renewal Day (1–31) *</label>
        <input type="number" id="renewal_day" name="renewal_day" min="1" max="31" required value="<?= h($_POST['renewal_day'] ?? '1') ?>">
    </div>
    <div class="form-group">
        <label for="spotify_account_id">Spotify Account *</label>
        <select id="spotify_account_id" name="spotify_account_id" required>
            <option value="">— Select account —</option>
            <?php foreach ($accounts as $account): ?>
                <option value="<?= (int) $account['id'] ?>"
                    <?= (($_POST['spotify_account_id'] ?? '') == $account['id']) ? 'selected' : '' ?>>
                    <?= h($account['account_name']) ?> (<?= (int) $account['user_count'] ?>/5 users)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="last_payment_date">Last Payment Date</label>
        <input type="date" id="last_payment_date" name="last_payment_date" value="<?= h($_POST['last_payment_date'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label for="months_paid_in_advance">Months Paid in Advance</label>
        <input type="number" id="months_paid_in_advance" name="months_paid_in_advance" min="1" max="12" value="<?= h($_POST['months_paid_in_advance'] ?? '1') ?>">
    </div>
    <div class="form-group checkbox">
        <label>
            <input type="checkbox" name="is_paid" value="1" <?= isset($_POST['is_paid']) ? 'checked' : '' ?>>
            Mark as paid
        </label>
    </div>
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save User</button>
        <a href="users.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
