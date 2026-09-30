# Payben-style Recharge Portal (PHP + MySQL)

A full-stack retailer recharge portal in the spirit of Payben: retailers sign
up, get approved, top up a wallet, perform mobile/DTH recharges, and earn
commission. An admin panel approves retailers, approves wallet top-ups, and
manages operators/commission rates.

**This build is a demo/prototype**: recharges and wallet top-ups are
simulated — there is no real telecom aggregator or payment gateway wired in.
Wire one in before handling real money (see "Going live" below).

## Requirements
- PHP 8.0+
- MySQL 5.7+ / MariaDB
- Any web server (Apache, Nginx) or PHP's built-in server for local testing

## Running on XAMPP specifically

1. Copy the whole `payben-portal` folder into `htdocs`, e.g.
   `C:\xampp\htdocs\payben-portal` (Windows) or `/opt/lampp/htdocs/payben-portal` (Linux).
2. Start **Apache** and **MySQL** from the XAMPP control panel.
3. Import the schema: open `http://localhost/phpmyadmin`, create/import via
   the **Import** tab using `schema.sql` (or run it via the `mysql` CLI —
   see step 1 below).
4. Visit `http://localhost/payben-portal/seed_admin.php` once to create the
   admin login, then delete `seed_admin.php`.
5. Go to `http://localhost/payben-portal/login.php`.

Links, form actions, and redirects all resolve through a `url()` helper
that auto-detects this `/payben-portal` subfolder from `config/database.php`
(`BASE_URL`) — you don't need to configure anything for this to work,
whether the project lives at the web root or several folders deep in
`htdocs`.

## Setup (general / non-XAMPP)

1. **Create the database.**
   ```
   mysql -u root -p < schema.sql
   ```
   This creates the `payben_portal` database, all tables, and seeds a
   starter list of operators (Airtel, Jio, Vi, BSNL, Tata Play, etc).

2. **Set your DB credentials** in `config/database.php` (host, name, user,
   password — defaults assume local MySQL with a `root` user and no
   password).

3. **Create the admin account.** Start the app and open `seed_admin.php`
   once in your browser, e.g.:
   ```
   php -S localhost:8000
   ```
   then visit `http://localhost:8000/seed_admin.php`. It prints a login
   (phone `9999999999`, password `admin123`) and refuses to run again once
   an admin exists. **Delete `seed_admin.php` right after**, and change that
   password from the Profile-equivalent flow (see note below) or directly in
   the database.

4. **Sign in.**
   - Admin: `http://localhost:8000/login.php` with the phone/password above.
   - Retailers: they self-register at `/register.php`, then an admin
     approves them under **Admin → Retailers** before they can sign in.

## How the pieces fit together

| Area | Pages |
|---|---|
| Public | `login.php`, `register.php`, `logout.php` |
| Retailer | `dashboard.php`, `recharge.php`, `wallet.php`, `transactions.php`, `commission.php`, `profile.php` |
| Admin | `admin/dashboard.php`, `admin/retailers.php`, `admin/transactions.php`, `admin/fund_requests.php`, `admin/operators.php` |

Key flows:
- **Recharge** (`recharge.php` → `recharge_process.php`): validates the
  amount against wallet balance, simulates the operator call (~85% success),
  and on success debits the wallet and credits commission — all in one DB
  transaction so the balance never drifts.
- **Wallet top-up** (`wallet.php` → `fund_request_submit.php`): a retailer
  requests funds; it sits in `admin/fund_requests.php` until an admin
  approves (credits the wallet) or rejects it.
- **Operators** (`admin/operators.php`): add/edit operators and their
  commission % at any time; retailers immediately see updated rates on the
  recharge form.

## Notes on the admin password
There's no separate "change admin password" screen yet — the quickest path
is running a one-off update after seeding:
```sql
UPDATE users SET password_hash = '<paste output of PHP password_hash()>' WHERE role = 'admin';
```
or temporarily reuse the retailer profile page's password form by adjusting
the `require_role('retailer')` check to `'admin'` if you want a UI for it.

## Going live (beyond this demo)
To move from mock to real money movement you'd need, at minimum:
- A recharge API aggregator account (for real mobile/DTH recharges) and
  their SDK/API credentials in place of `recharge_process.php`'s random
  outcome.
- A real payment gateway (Razorpay, Cashfree, etc.) for wallet top-ups,
  replacing the manual admin-approval flow in `fund_request_submit.php`.
- KYC/compliance handling appropriate for a payments/recharge business in
  your jurisdiction — this is a business & legal step, not just code.
