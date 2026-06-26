# Spotify Subscription Tracker

A simple PHP + MySQL app to track Spotify Family Plan sharing — who pays, when they're due, and payment status.

Runs locally on **XAMPP** with no frameworks.

---

## Folder Structure

```
spotify/
├── index.php              → redirects to dashboard
├── dashboard.php          → main page (all users + payment status)
├── users.php              → list users
├── add_user.php           → create user
├── edit_user.php          → edit user
├── accounts.php           → list Spotify accounts
├── add_account.php        → create account
├── edit_account.php       → edit account
├── reset_payments.php     → reset all is_paid flags
├── config.php             → database connection
├── style.css              → shared styles
├── database.sql           → MySQL schema + sample data
├── includes/
│   ├── header.php         → HTML header + navigation
│   ├── footer.php         → HTML footer
│   └── payment.php        → payment status logic
└── README.md
```

---

## Requirements

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP)
- PHP 7.4+ (comes with XAMPP)
- phpMyAdmin (included in XAMPP)

---

## Setup Instructions

### 1. Copy project to XAMPP

Copy the entire `spotify` folder to your XAMPP web root:

```
C:\xampp\htdocs\spotify\
```

### 2. Start XAMPP

1. Open **XAMPP Control Panel**
2. Start **Apache**
3. Start **MySQL**

### 3. Import the database (phpMyAdmin)

1. Open your browser and go to: **http://localhost/phpmyadmin**
2. Click the **Import** tab
3. Click **Choose File** and select `database.sql` from this project folder
4. Click **Go** at the bottom

This creates the `spotify_tracker` database with two tables and sample data.

**Alternative — run SQL manually:**

1. In phpMyAdmin, click **SQL** tab
2. Paste the contents of `database.sql`
3. Click **Go**

### 4. Configure database connection (if needed)

Open `config.php` and verify these settings match your XAMPP setup:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // empty by default in XAMPP
define('DB_NAME', 'spotify_tracker');
```

If you set a MySQL root password in XAMPP, update `DB_PASS`.

### 5. Open the app

Go to: **http://localhost/spotify/**

You should see the dashboard with sample users.

---

## Usage

| Page | URL |
|------|-----|
| Dashboard | http://localhost/spotify/dashboard.php |
| Users | http://localhost/spotify/users.php |
| Accounts | http://localhost/spotify/accounts.php |
| Reset Payments | http://localhost/spotify/reset_payments.php |

### Workflow

1. **Add Spotify accounts** (Accounts → Add Account) — max 5 users each
2. **Add users** and assign them to an account
3. **Dashboard** shows payment status with color badges:
   - 🟢 **PAID** — marked as paid this month
   - 🟡 **DUE SOON** — renewal within 2 days
   - 🔴 **OVERDUE** — past renewal date, not paid
   - **PAID IN ADVANCE** — shown when `months_paid_in_advance > 1`
4. **Check the checkbox** on the dashboard to mark a user as paid (sets `last_payment_date` to today)
5. **Start of each month** — go to Reset Payments to clear all paid flags

---

## Payment Status Logic

- **Renewal day** = day of the month payment is due (1–31)
- **Next due date** is calculated from `last_payment_date` + `months_paid_in_advance`, or the next occurrence of `renewal_day`
- Alerts on the dashboard show overdue and due-soon reminders

---

## Database Tables

### spotify_accounts
| Column | Type |
|--------|------|
| id | INT (PK) |
| account_name | VARCHAR(100) |
| notes | TEXT |

### users
| Column | Type |
|--------|------|
| id | INT (PK) |
| name | VARCHAR(100) |
| username | VARCHAR(100) |
| date_joined | DATE |
| renewal_day | INT (1–31) |
| last_payment_date | DATE (nullable) |
| months_paid_in_advance | INT (default 1) |
| is_paid | BOOLEAN |
| spotify_account_id | FK → spotify_accounts.id |

Max **5 users per account** is enforced in PHP when adding or moving users.

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Blank page / PHP error | Check Apache is running; enable errors in `php.ini`: `display_errors = On` |
| Database connection failed | Verify MySQL is running; check `config.php` credentials |
| 404 Not Found | Confirm folder is at `C:\xampp\htdocs\spotify\` |
| Import fails in phpMyAdmin | Increase `upload_max_filesize` in php.ini, or paste SQL manually |

---

## Tech Stack

- Plain PHP (PDO)
- MySQL
- HTML + CSS (no JavaScript frameworks)
- Minimal inline JS only for form confirm dialogs and checkbox auto-submit
