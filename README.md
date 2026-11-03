<div align="center"><img src="docs/logo.png" width="100" alt="Soap Opera logo"></div>
<h1 align="center">Soap Opera</h1>
<div align="center">
<img src="https://img.shields.io/badge/version-2.4.0-1766DC?style=flat-square" alt="version">
<img src="https://img.shields.io/badge/status-complete-1766DC?style=flat-square" alt="status">
<img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel">
<img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP">
<img src="https://img.shields.io/badge/MySQL-XAMPP-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL">
<img src="https://img.shields.io/badge/SQLite-supported-003B57?style=flat-square&logo=sqlite&logoColor=white" alt="SQLite">
</div>


**Soap Opera** is a web-based **Laravel** laundry management system for running a laundry shop: taking orders at the counter, tracking payments and order status, and reporting daily and monthly sales.
It replaces paper job orders and notebooks with a **point of sale, order tracking, customer records, inventory and reports** in one place, and works on desktop, tablet and phone. It is built for touch screens at the counter: every control is sized for a finger, in a blue-and-white design with the bubble mascot.

> **Current version: v2.4.0** — six languages: **English, Filipino, Spanish, Japanese, Chinese (Simplified) and Indonesian**. Each staff member picks their own from the menu (it stays on their account); admins choose which languages are offered, the default and the fallback. Every screen, message, form error, export and printed document is translated, numbers and dates can follow the language, and the layout is ready for right-to-left languages. Built on v2.3.0: printed documents: a numbered **receipt** for every payment, a **claim ticket** for the customer and a **job order** that travels with the laundry, on 58 or 80 mm receipt rolls, A4 or Letter. One click prints them from the POS or the order, they download as PDFs, and their QR code and barcode open the order when scanned. Orders now have a ready-by date and an optional weight. Built on v2.2.0: fast with 50,000 customers and 100,000 orders (the dashboard opens in about 0.3 s instead of 3.6 s, the POS in 20 ms, and a year of sales reports and CSV exports that used to run out of memory), with pages that update in place instead of reloading. Built on v2.1.0: ten currencies (PHP, USD, EUR, JPY, GBP, CAD, AUD, SGD, MYR, IDR) and the number, date and time formats chosen in Settings; every order keeps the currency it was taken in. Built on v2.0.0: a touch-first redesign of every screen except the sign-in page: a new point of sale (one tap per item, customer type-ahead, Repeat last order, a payment sheet with keypad, quick cash and change due), a dashboard of KPI tiles and a Needs attention center, order status tabs with bulk updates and saved views, a new **Ready for pickup** status, and one accessible design system (WCAG AA contrast, 44 px+ touch targets). See [docs/DESIGN.md](docs/DESIGN.md) for the UX audit, page blueprints and design system. Built on v1.3.0: discounts as a peso amount or a percentage, and restored backups brought up to the current database automatically. Built on v1.2.0: a flatter look around the bubble mascot (with arms, legs and facial expressions), a **Needs attention** panel on the dashboard and a daily automatic backup. Built on v1.1.0: the app is now **Soap Opera**, with a new blue-and-white bubble look on every page, a redesigned login page and dashboard, and a POS that groups services into sections by service type. Built on v1.0.0, the first stable release: real database backup and restore, correct order totals and payments, role-based access, faster pages and a mobile-friendly layout. See [Releases](https://github.com/nncast/laravel-laundry-management-system/releases) for the release notes.

<p align="center">
  <img width="400" alt="Login" src="docs/screenshots/login.png" />
  <img width="400" alt="Dashboard" src="docs/screenshots/dashboard.png" />
  <img width="400" alt="Point of sale" src="docs/screenshots/pos.png" />
  <img width="400" alt="Payment with keypad and quick cash" src="docs/screenshots/pos-payment.png" />
  <img width="400" alt="Orders" src="docs/screenshots/orders.png" />
  <img width="400" alt="Order details" src="docs/screenshots/order-details.png" />
  <img width="400" alt="Printing a receipt on an 80 mm roll" src="docs/screenshots/print-receipt.png" />
  <img width="400" alt="Sales report" src="docs/screenshots/sales-report.png" />
</p>

<p align="center">
  <img width="190" alt="Mobile login" src="docs/screenshots/mobile-login.png" />
  <img width="190" alt="Mobile dashboard" src="docs/screenshots/mobile-dashboard.png" />
  <img width="190" alt="Mobile menu" src="docs/screenshots/mobile-menu.png" />
  <img width="190" alt="Mobile POS" src="docs/screenshots/mobile-pos.png" />
  <img width="190" alt="Mobile POS order" src="docs/screenshots/mobile-pos-order.png" />
</p>

## Features

**Point of Sale** (built for touch screens)
- One tap adds a service; big steppers change amounts; add-ons are one-tap chips; Undo when removing
- Services grouped by type, with type chips and search (Enter adds the first match)
- Customer type-ahead by name or phone, recent customers, new customer without leaving the order, and **Repeat last order**
- Discounts as a fixed amount or a percentage, with 5/10/15/20% shortcuts (the order keeps its percentage when edited)
- Ready-by date filled in from the shop's usual turnaround, with **Same day** and **Next day** one tap away, and an optional weight in kg
- Payment sheet with cash, card, transfer or other, quick cash amounts, an on-screen keypad and the change due; **Pay later** for payment at pickup; only the amount owed is recorded
- Three panels on desktops, two on landscape tablets, and a slide-up order on phones; an unsaved order survives a page refresh
- Once saved: print the claim ticket, the job order and the receipt in one click each, without leaving the POS

**Orders**
- Status flow: **Pending → Processing → Ready for pickup → Completed** (or Cancelled)
- Status tabs with counts, payment and date filters, saved views, one-tap next step and bulk status updates, all with Undo
- Order details with progress, ready-by date, payment history (each payment with its receipt number and cashier), a payment dialog with change due, notes and editing
- The search box opens an order from its number, or from a scanned ticket: a USB scanner reads the barcode, a 2D scanner or phone the QR code

**Printing**
- **Receipt** for a payment: receipt number, order number, customer, date and time, services with quantity and unit price, weight, discount, total, payments, balance, method and cashier. Before any payment it prints as an invoice
- **Claim ticket** for the customer: order number in large type, ready-by date, services, total quantity, weight and the amount to pay at pickup
- **Job order** for the shop: services to tick off, instructions and notes, a box for stains or damage found, and a processing checklist (counted, sorted, washed, dried, folded, packed, checked)
- 58 mm and 80 mm receipt rolls (a receipt PDF is as long as the receipt), A4 and Letter; the print page shows the document as it will print
- PDF download, and **Share** on phones and tablets (email, chat apps)
- A QR code of the order on every document, and a Code 128 barcode of the order number on 80 mm paper and up
- Logo, header (e.g. tax number), thank-you and promo messages, pickup instructions and terms, set in Settings without code; documents use the order's currency and the shop's date format

**Records**
- Customers with contact number and address, their number of orders, last visit and balance due
- Services (with price, type and icon), service types and add-ons
- Inventory: products, categories and units with stock levels
- Staff accounts with Admin, Manager and Cashier roles

**Reports**
- Dashboard KPI tiles (today's, active, ready and overdue orders; revenue today and this month; money collected today; unpaid balance), each opening the matching order list
- Weekly / monthly / yearly sales chart and top services
- Daily, Sales and Order reports with date presets and status filters
- CSV export (opens in Excel) and print; long ranges load page by page and stream to the file

**Automations**
- **Needs attention** on the dashboard, checked on every visit: overdue orders (Pending 1+ day or Processing 3+ days), orders ready for pickup (with a one-tap **Picked up**), completed orders that are not fully paid, products at or below their minimum stock, and, for admins, whether backups are still being made
- **Daily automatic backup** without setting up cron: the first dashboard visit after a backup turns a day old makes a new one (kept with the last 10 in `storage/app/backups`); with a scheduler running (`php artisan schedule:work`) it also happens without anyone opening the app

**Settings & Backup**
- Business name, address, contact number and logo (favicon)
- Currency (10 currencies) and number, date and time formats, with a live preview
- Receipts & printing: logo, texts, default paper, the usual turnaround in days, QR code and barcode on or off
- Language: the languages staff can pick, the default for the sign-in page and new staff, and the fallback for missing translations

**Languages**
- English, Filipino, Spanish, Japanese, Chinese (Simplified) and Indonesian, switched from the menu or the sign-in page without losing an order in progress (the POS keeps its draft)
- Everything is translated: menus, pages, buttons, messages, form errors, CSV exports, receipts, claim tickets and job orders (PDFs in Japanese and Chinese use a bundled Noto Sans font)
- **Automatic** number and date formats follow the language (1,234.56 or 1.234,56; Nov 3, 2026, 3 nov. 2026 or 2026年11月3日); month and day names always do
- Ready for right-to-left languages: the layout uses logical CSS properties and mirrors with `dir="rtl"`
- One-click database backup (`.sql` file with all data)
- Restore from a backup, with an automatic safety backup taken first

## Roles

| Page | Cashier | Manager | Admin |
| --- | :---: | :---: | :---: |
| Dashboard, POS, Orders, Customers | ✓ | ✓ | ✓ |
| Delete orders and customers | | ✓ | ✓ |
| Inventory and Services | | ✓ | ✓ |
| Reports | | | ✓ |
| Staff, Settings, Backup & Restore | | | ✓ |

## Development environment

| Category | Details |
| --- | --- |
| Language | PHP 8.2+ |
| Framework | Laravel 12 |
| Front end | Blade templates, plain CSS and JavaScript (one design system: `public/css/app.css`, `public/js/app.js`), Font Awesome |
| Database | MySQL / MariaDB (XAMPP or Laragon) or SQLite |
| Languages | Laravel JSON translations (`lang/*.json`, English text as the keys) and `lang/{code}/validation.php`; [Noto Sans SC](https://github.com/notofonts/noto-cjk) (OFL) for Japanese and Chinese PDFs |
| Documents | [dompdf](https://github.com/dompdf/dompdf) (PDFs, LGPL-2.1) and [php-qrcode](https://github.com/chillerlan/php-qrcode) (QR codes, MIT); the Code 128 barcode is drawn by the app |
| Tests | PHPUnit (`php artisan test`) |

## Requirements

| Tool | Download |
| --- | --- |
| PHP 8.2 or later (XAMPP 8.2+ or Laragon) | [XAMPP](https://www.apachefriends.org/download.html) · [Laragon](https://laragon.org/download/) |
| Composer | [getcomposer.org](https://getcomposer.org/download/) |
| MySQL / MariaDB (included in XAMPP and Laragon) | — |
| Git (optional, for cloning) | [git-scm.com](https://git-scm.com/downloads) |

> **Note:** Laravel 12 needs **PHP 8.2 or later**. Older XAMPP versions ship PHP 8.0 — check with `php -v`. Node.js / npm are **not** needed.

## Setup and run instructions

### 1. Start MySQL
- **XAMPP:** open the XAMPP Control Panel and start **MySQL**
- **Laragon:** click **Start All**

### 2. Clone the project
```bash
git clone https://github.com/nncast/laravel-laundry-management-system.git
cd laravel-laundry-management-system
```
Or download the [source .zip](https://github.com/nncast/laravel-laundry-management-system/archive/refs/tags/v2.4.0.zip) and extract it.

### 3. Install dependencies
```bash
composer install
```

### 4. Setup environment
```bash
# Windows (CMD)
copy .env.example .env

# Linux / macOS
cp .env.example .env
```

Generate app key:

```bash
php artisan key:generate
```

### 5. Configure database

Edit the `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laundry_system
DB_USERNAME=root
DB_PASSWORD=

APP_TIMEZONE=Asia/Manila
SESSION_DRIVER=file
CACHE_STORE=file
```

> Use `127.0.0.1`, not `localhost`. On Windows, `localhost` can add about a second to every page load.

<details>
<summary>Using SQLite instead of MySQL</summary>

Keep `DB_CONNECTION=sqlite` in `.env` and create an empty database file:

```bash
# Windows (CMD)
type nul > database\database.sqlite

# Linux / macOS
touch database/database.sqlite
```
</details>

### 6. Run migrations and seed

```bash
php artisan migrate --seed
```

If prompted with:

```bash
WARN  The database 'laundry_system' does not exist on the 'mysql' connection.

Would you like to create it? (yes/no) [yes]
```

Type:

```bash
yes
```

Then press `Enter`.

### 7. Start the app
```bash
php artisan serve
```

Open `http://localhost:8000` in your browser.

---

## Default Account

| Role | Username | Password |
|------|----------|----------|
| Admin | `admin` | `admin123` |

**Important:** change the default password after first login (**Staff**), and set your business name and logo under **Settings**.

## Backup & Restore

Go to **Settings → Backups**.

<p align="center">
  <img width="600" alt="Backup and restore" src="docs/screenshots/backup.png" />
</p>

- **Download a backup** saves a `.sql` file with every table and record. Keep it somewhere safe (USB drive or cloud storage).
- **Automatic backups**: a copy is also saved to `storage/app/backups` once a day on its own (see Automations above), so the **Last backup** time stays recent even if nobody clicks the button. Copy them off the computer from time to time.
- **Restore backup** replaces all current data with a backup made by this system. A safety backup of the current data is saved to `storage/app/backups` first, and you are logged out afterwards.

## Upgrading

**From v2.x to v2.4.0:** as below, and `php artisan migrate` also adds each staff member's language and the language settings. Everyone keeps seeing English until they pick a language from the menu, or an admin changes the default under **Settings → Language**.

**Adding a language:** add it to `config/locales.php`, copy `lang/fil.json` to `lang/{code}.json` and translate the values (keep the keys), and copy `lang/fil/validation.php` for form errors. `php artisan test` fails while any string is missing. Then switch it on under **Settings → Language**.

**From v2.x to v2.3.0:** pull or download the latest version, then run `composer install` (it adds the PDF and QR code libraries) and `php artisan migrate` (ready-by dates, weights, the cashier on each payment and the receipt settings). Then set up your receipts under **Settings → Receipts & printing**. Orders taken before the upgrade have no ready-by date or weight; their documents show a dash instead.

**Printing tips:** a receipt printer prints the 58 or 80 mm documents through its Windows or Android driver; pick the roll as the paper size and set the margins to none in the print dialog. For printing without the dialog, start Chrome or Edge with `--kiosk-printing`. QR codes link to the address the app was opened from, so open the app by the computer's network address (not `localhost`) if phones should open scanned orders.

**From v1.x to v2.0.0:** pull or download the latest version, replace the files, then run `php artisan migrate`. It adds the **Ready for pickup** order status (and, coming from v1.2 or older, a column so a discount can be a percentage; existing orders keep their peso discounts). Your data, business name, address and logo are kept. If a page still looks like the old version, refresh it once more so the browser loads the new styles.

Restoring a backup made by an older version brings it up to the current database automatically.

**From 0.9.0:**

1. Download a backup from **Settings** first.
2. Pull or download the latest version, then run:
   ```bash
   composer install
   php artisan migrate
   ```
3. Add `APP_TIMEZONE=Asia/Manila` to your `.env`, and change `DB_HOST=localhost` to `DB_HOST=127.0.0.1` if you use MySQL.

The migration adds the payment method column, report indexes, the percentage-discount column and the Ready for pickup status, and recalculates the totals and paid amounts of existing orders that earlier versions saved incorrectly.

## Running tests

```bash
php artisan test
```

## Contributing

Contributions are welcome. Fork the repository, work on a branch from `main`, and open a pull request describing what changed and why. See [CONTRIBUTING.md](CONTRIBUTING.md) for the workflow and code style, and [AUTHORS.md](AUTHORS.md) for the people who built it.

## Security

Please don't report vulnerabilities in public issues. Use the repository's **Security → Report a vulnerability** tab instead. See [SECURITY.md](SECURITY.md) for details.

---

*Soap Opera · Laundry Management System · 2025 · Laravel 12 · PHP 8.2 · MySQL / SQLite*
