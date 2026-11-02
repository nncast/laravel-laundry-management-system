# Soap Opera design system and UX notes (v2.0.0)

Soap Opera is used at a laundry counter, mostly on a touch screen, by staff who take
dozens of orders a day. Version 2.0.0 rebuilt every screen around that: big touch
targets, fewer taps per order, and one consistent set of components. What did not
change: the sign-in page, the bubble mascot, the blue-and-white brand, and the
business rules (prices, totals, discounts, payments, roles, backups).

This document covers:

1. [UX audit of v1.3](#1-ux-audit-of-v13)
2. [Information architecture](#2-information-architecture)
3. [User flows](#3-user-flows)
4. [Page blueprints](#4-page-blueprints)
5. [Design system](#5-design-system)
6. [Accessibility](#6-accessibility)
7. [Touch and mobile](#7-touch-and-mobile)
8. [Not built yet](#8-not-built-yet)

Code: tokens in `public/css/theme.css`, components and layout in `public/css/app.css`,
shared behaviour in `public/js/app.js`, Blade components in `resources/views/components/`.

---

## 1. UX audit of v1.3

What was found in the v1.3 screens, and what v2 does about it.

### Point of sale

| Issue | Impact | v2 |
|---|---|---|
| Tapping a service opened a quantity dialog; adding one item took 2 taps, more items took typing | Slow at a busy counter | One tap adds one; steppers and a typed amount in the order |
| `+`/`−` quantity buttons were about 24 px | Missed taps on a touch screen | 48 px steppers |
| Customer picked from a plain dropdown of every customer | Unusable with hundreds of customers; no phone search | Type-ahead by name or phone, recent customers as chips, New customer in place |
| Payment was a separate dialog, then **Save**, then 1–3 `alert()`/`confirm()` pop-ups | About 12 taps and 2 blocking pop-ups per order | Charge → Complete; pop-ups replaced with a success sheet |
| Payment method was always recorded as cash | Card/transfer money mixed into cash totals | Cash, card, transfer or other, chosen on the payment sheet |
| No change-due readout after payment | Cashiers did the subtraction in their head | Change (or remaining balance) shown live and on the success sheet |
| Editing an order could not take a payment | Extra trip to the order page | Edit mode can collect the balance |
| Refreshing the page lost the order in progress | Re-entry and mistakes | A new order is kept on the device until it is saved or cleared |
| Add-ons hidden behind a `+` dialog | Easy to forget (e.g. rush service) | One-tap chips under the order |

### Dashboard

| Issue | Impact | v2 |
|---|---|---|
| A large banner took the first 300 px | The numbers staff need were below the fold | Compact greeting with the mascot; KPI tiles first |
| Only 3 metrics, with odd comparisons ("0.0% from yesterday") | Little to act on | 8 tiles: today's, active, ready, overdue orders; revenue today and this month; collected today; unpaid balance. Each opens the matching filtered list |
| Sales chart used two y-axes (pesos and orders) | Easy to misread | One axis (pesos); orders in the tooltip; drawn in HTML, so Chart.js and its CDN request are gone |
| "Needs attention" showed 3 static boxes | No way to act from there | Tabs with counts, one-tap **Picked up** (with Undo), backup health line for admins |

### Orders and order details

| Issue | Impact | v2 |
|---|---|---|
| Search was the only filter | No way to see "ready", "overdue" or "unpaid" at a glance | Status tabs with counts, payment and date filters, saved views |
| Changing status meant opening each order | Many page loads per day | One-tap next step on every row; bulk status for several orders; Undo |
| No "ready for pickup" state | Washed orders and collected ones looked the same | New **Ready for pickup** status between processing and completed |
| Order details: status as a bare dropdown, payment dialog with only an amount | No method, no quick amounts, no change | Progress steps, a big next-step button, payment dialog with methods, quick amounts and change, payment history |
| Print printed the whole screen | Messy receipts | Receipt layout: business header, items, totals, payments |

### Everywhere else

| Issue | Impact | v2 |
|---|---|---|
| About 6,300 lines of CSS repeated inside each page | Pages drifted apart (button shapes, search boxes, headings) | One design system; pages carry only layout tweaks |
| Row actions were bare `<i>` icons | Not reachable by keyboard or screen reader; small on touch | 44 px labelled icon buttons |
| `window.confirm()` / `alert()` for every delete and error | Jarring, blocks the page, inconsistent wording | Confirm dialog in the app's style; toasts; inline field errors |
| Validation errors appeared in a toast, and the dialog closed | The person had to retype everything | Dialogs stay open with the errors under each field |
| Wrong page titles ("Service Type" on the services list, "Staff Dashboard") and duplicated headings | Disorienting | One page header pattern from the layout |
| Muted text and the brand blue were below WCAG AA (2.4:1 and 4.4:1) | Hard to read in a bright shop | Text 4.5:1 or better, white on blue 5.3:1, field borders 3:1 |
| Tables scrolled sideways on phones | Hard to use one-handed | Rows turn into cards on phones; bottom tab bar |
| Settings was a page of two tiles that opened dialogs | Extra taps to change one field | Business profile and backups shown directly |

---

## 2. Information architecture

```
Operations            Dashboard · Point of sale · Orders · Customers        (all roles)
Catalog & stock       Services [Services | Service types | Add-ons]          (manager, admin)
                      Inventory [Products | Categories | Units]
Administration        Reports [Daily summary | Sales | Orders]               (admin)
                      Staff
                      Settings (business profile, backups)
```

- **Sidebar** on screens 1024 px and wider; a drawer opened from **More** below that.
- **Section tabs** under the page title join pages that belong together (brackets above),
  so the sidebar stays at nine items.
- **Bottom tab bar** on phones and tablets: Home, Orders, **New order**, Customers, More.
- **Top bar**: order search on every page (`/` focuses it) and a **New order** button.
- The POS is a full-screen page with its own top bar: it needs every pixel on a tablet.

---

## 3. User flows

### Take an order (existing customer, cash, 2 × Wash & Dry + 1 Fold)

| | v1.3 | v2 |
|---|---|---|
| Add services | Tile → qty dialog → type 2 → Add; tile → Add | Tile, tile, tile |
| Customer | Open dropdown, scroll, pick | Tap a recent-customer chip (or type 2–3 letters, Enter) |
| Payment | Payment → Process Payment → OK on alert | Charge → Complete |
| Save | Save → OK on alert → order page | Success sheet shows the change → **Next order** |
| **Total** | **~12 taps, typing, 2 pop-ups** | **6 taps** |

Also on the way: Repeat last order fills the order from the customer's previous visit;
5/10/15/20 % discount chips; Pay later for customers who pay at pickup.

### Hand over a finished order

1. Dashboard → **Needs attention → Ready for pickup**, or Orders → **Ready for pickup** tab.
2. Paid: tap **Picked up**. Balance due: **Collect** opens the order with the payment
   dialog already open → method → Exact → Record → **Mark picked up**.

### Find an order

Type in the top-bar search on any page (order number, customer name or phone), or use
the status tabs and filters on Orders. Frequent filter combinations can be saved as views.

### End of day

Reports → **Daily summary**: orders taken, sales, money collected (by payment time, so it
matches the drawer), outstanding, and the day's orders. Download CSV or print.

---

## 4. Page blueprints

Measurements are for a 1440 × 900 screen unless noted.

### Point of sale

```
┌ ← New order · Soap Opera ───────────────── / services · F2 customer · Ctrl+Enter charge   (A) Admin ┐
├──────────────────────────────────────────┬──────────────────────┬───────────────────────────┤
│ [ Search services                      ] │ Order (3)     Clear  │ CUSTOMER                  │
│ (All 6)(Dry Clean 3)(Wash & Fold 3)      │ Wash & Dry   ₱360.00 │ [ Name or phone        ]  │
│ DRY CLEAN                                │ ₱180 each            │ (Maria)(Carlo)(Ana)…      │
│ ┌────────┐┌────────┐┌────────┐           │ [−] 2 [+]      🗑     │ [ + New customer       ]  │
│ │ Barong │ │Comfort.│ │Curtains│          │ Fold          ₱40.00 │ ─── or, once picked ───   │
│ │ ₱350   │ │ ₱250   │ │ ₱120   │          │ [−] 1 [+]      🗑     │ (M) Maria · 2 orders      │
│ └────────┘└────────┘└────────┘           │ ADD-ONS              │ [ Repeat last order ]     │
│ WASH & FOLD                              │ (✓ Softener ₱20)     │ Services        ₱400.00   │
│ ┌──── 1 ┐┌────────┐┌──── 2 ┐             │ (+ Rush ₱50)         │ Add-ons          ₱20.00   │
│ │ Fold   │ │ Press  │ │ Wash & │           │ Order date [ 11/02 ] │ Discount [₱|%] [  10  ]   │
│ │ ₱40    │ │ ₱60    │ │ ₱180   │           │ Notes [          ]   │ (5%)(10%)(15%)(20%)       │
│ └────────┘└────────┘└────────┘           │                      │ Total           ₱378.00   │
│                                          │                      │ [Pay later][Charge ₱378]  │
└──────────────────────────────────────────┴──────────────────────┴───────────────────────────┘
```

- **≥ 1280 px**: three columns (catalog, order 380 px, checkout 360 px).
- **1024–1279 px** (landscape tablets): catalog and one 400 px column holding order and
  checkout; the action bar stays pinned at the bottom.
- **< 1024 px**: the catalog fills the screen; a bottom bar ("3 items · Review order ·
  ₱400") slides the order up as a full-screen sheet.
- **Payment sheet**: amount due; method (Cash, Card, Transfer, Other); amount received with
  quick amounts (Exact, ₱100, ₱200, ₱500, ₱1,000) and a 3 × 4 keypad; the result line says
  the change or what stays due. Two columns on tablets and desktops, one on phones.
- **Success sheet**: mascot, order number, change to give back, Print receipt, View order,
  **Next order**.

### Dashboard

```
(mascot) MONDAY, NOVEMBER 2 / Good evening, Admin / 5 things need you today.
[+ New order][Find order][Add customer][Inventory][Reports]                ← quick actions
[Today's orders][Active orders][Ready for pickup][Overdue]                 ← KPI row 1
[Revenue today][Collected today][Revenue this month][Unpaid balance]        ← KPI row 2
┌ Needs attention ──────────────────────────┐ ┌ Recent orders ────────┐
│ (Overdue 1)(Ready 1)(Picked up, unpaid 1)  │ │ name · number  ₱ badge│
│ (Low stock 2)                              │ │ …                     │
│ row: customer · order · since   [Picked up]│ └───────────────────────┘
│ ✓ Data backed up 2 hours ago               │ ┌ Top services ─────────┐
└────────────────────────────────────────────┘ │ ranked bars           │
┌ Sales  [Week|Month|Year] ──────────────────┐ └───────────────────────┘
│ one-axis column chart, peak labelled       │
└────────────────────────────────────────────┘
```

Tiles are links to the matching list (for example *Overdue* → Orders, Overdue tab). Two
tiles per row on tablets and phones; the action center and lists stack.

### Orders

Title and count; status tabs with counts (All, Open, Overdue, Pending, Processing, Ready
for pickup, Completed, Cancelled); a filter row (search, payment, from, to; behind a
**Filters** button on phones); saved views as chips; a table with a checkbox, order, customer,
status, payment (badge and amount due), total, and actions (next step or Collect, edit,
delete). Ticking rows shows a bulk bar at the bottom: Processing, Ready, Completed.
Phones: each row is a card with the actions at the bottom, in thumb reach.

### Order details

Header: back to Orders, order number, status and payment badges, date; actions: the next
step (Start processing / Mark ready for pickup / Mark picked up), Add payment, Edit, Print.
Left: progress steps; items with add-ons and totals; payments history; notes.
Right: balance card with **Collect payment**; customer (tap-to-call phone, their other
orders); details (taken by, created, updated, change status, cancel order).
Printing gives a receipt with the business header.

### Customers

Header with **Add customer**; search; table: customer (initial, name, address), phone
(tap to call), number of orders (links to their orders), last order date, balance due,
and **New order** (opens the POS with them picked), edit, delete.

### Services, Inventory, Staff

One pattern: header with an **Add** button, instant search (or server search for staff),
table that becomes cards on phones, one dialog for add and edit. Status fields are
segmented controls (Active / Inactive); products show stock against the low-stock level
and offer a **Low stock only** filter.

### Reports

Daily summary: day picker with previous/next; four KPIs; the day's orders and top
services. Sales and Order report: date presets (Today, Yesterday, Last 7 days, This
month, Last month) and a custom range; KPI tiles; the table; Download CSV and Print.

### Settings

Two cards side by side: Business profile (name, address, phone, browser icon) saved in
place, and Backups (last backup, Download a backup, guarded Restore).

### Sign-in

Unchanged. It shares `theme.css`, so it picked up the AA-safe blue (#1766dc for #1f74f0),
a change of a few shades; a pixel comparison shows no visible difference.

---

## 5. Design system

### Tokens (`public/css/theme.css`)

**Color**

| Token | Value | Use | Contrast |
|---|---|---|---|
| `--text` | #0b1b34 | Body text | 17.2:1 on white |
| `--text-secondary` | #334663 | Labels | 9.6:1 |
| `--text-muted` | #596d8a | Meta text, placeholders | 5.3:1 on white, 4.9:1 on `--bg` |
| `--text-faint` | #7d8ea8 | Icons and decoration only | — |
| `--accent` | #1766dc | Primary actions, links, current state | 5.3:1 both ways with white |
| `--accent-soft` | #eef4ff | Selected rows, soft buttons | accent on it 4.8:1 |
| `--border-input` | #7f93b0 | Edges of form controls | 3.1:1 (WCAG 1.4.11) |
| `--bg` / `--surface` | #f4f7fb / #ffffff | Page / cards | — |
| `--success` / `--danger` | #0e7c3e / #c62f35 | Solid buttons | 5.3:1 / 5.4:1 with white |

Status badges (text on tint): Pending 5.8:1, Processing 4.8:1, Ready for pickup 5.9:1,
Completed 5.9:1, Cancelled 4.7:1, Unpaid 6.5:1. Every status is also written out, so
color is never the only signal.

**Type**: Plus Jakarta Sans. 12 (eyebrows), 13 (meta, table headers), 15 (body, cells),
16 (inputs, so phones don't zoom), 20, 24 (page titles), 28–32 (key figures). Money in
tables uses tabular figures; big standalone numbers use the default figures.

**Spacing**: 4 px grid (`--s-1` … `--s-10` = 4–40 px). **Radius**: 8 (controls), 12
(cards), 16 (dialogs), 20 (sheet corners), pill (chips, badges). **Elevation**: surfaces
are flat with 1 px borders; only floating layers cast shadows (`--shadow` for hover,
`--shadow-lg` for dialogs and toasts). **Motion**: 120 / 200 ms, eased; all animation
switches off under `prefers-reduced-motion`.

**Touch targets**: `--tap` is 44 px, raised to 48 px on touch screens
(`pointer: coarse`); `--tap-lg` 52 / 56 px. The POS uses 48–64 px.

**Breakpoints**: 640 (phone), 768, 1024 (sidebar appears; POS goes two-column), 1280
(POS three-column).

### Components (`public/css/app.css`)

| Component | Classes / markup | Notes |
|---|---|---|
| Button | `.btn` + `.btn-primary`, `.btn-secondary`, `.btn-soft`, `.btn-ghost`, `.btn-success`, `.btn-danger`, `.btn-danger-soft`; sizes `.btn-sm` `.btn-lg` `.btn-block`; `.is-loading` | Pressed state on tap; hover only on devices that hover |
| Icon button | `.icon-btn` (+ `.outlined`, `.danger`) | Always has `aria-label` |
| Field | `<x-field name label type …>` → `.field .label .input/.select/.textarea .help .field-error` | `type="segmented"` with `:options` for big two-to-four-way choices |
| Segmented control | `.segmented` with real radio inputs | Discount ₱/%, payment method, status |
| Chip | `.chip` with `aria-pressed` / `aria-current`, `.chip-count` | Filters, add-ons, presets |
| Badge | `.badge .badge-{pending,processing,ready,completed,cancelled,paid,partial,unpaid,…}` | |
| Card | `.card .card-header .card-title .card-body .card-footer` | |
| KPI tile | `.kpi-grid > .kpi .kpi-label .kpi-value .kpi-meta` | `a.kpi` when it links somewhere; `.is-alert` |
| Table | `.table-wrap > .table.table-stack` | Sticky header; `td[data-label]` turns rows into cards on phones; `tr[data-href]` makes the row tappable |
| List | `.list .list-item .list-main .list-title .list-sub .list-end` | |
| Dialog | `<dialog class="dialog">` + `.dialog-header/-body/-footer`; `<x-form-dialog>` for add/edit | Native focus trap and Esc; bottom sheet on phones |
| Confirm | `confirmAction({title, message, confirmText})` or `<form data-confirm="…">` | Replaces `window.confirm()` |
| Toast | `showToast(message, type, timeout, {label, onClick})`; `flashToast()` for the next page | Undo actions; shows above open dialogs |
| Empty state | `.empty .empty-icon/.empty-art .empty-title` | The mascot appears on the friendly ones |
| Stepper, keypad | `.stepper`; POS `.keypad` | 48–64 px keys |
| Bulk bar | `.bulk-bar` | Appears while rows are selected |
| Pagination | `resources/views/pagination/default.blade.php` | 44 px page buttons |

### Patterns

- **List page**: header (title, count, primary action) → optional section tabs → toolbar
  (search, filters) → card with the table → pagination.
- **Add / edit**: one `<x-form-dialog>` per page, opened by `data-form-dialog` buttons that
  carry the row's values. It submits in the background; 422 errors appear under the fields
  and the dialog stays open; success reloads the list with a toast.
- **Destructive actions**: `data-confirm` forms. Reversible status changes don't ask
  first; they offer **Undo** instead.
- **Status flow**: Pending → Processing → Ready for pickup → Completed (Cancelled from
  any open state). Lists offer the next step as one button.

---

## 6. Accessibility

Targets WCAG 2.1 AA:

- **Contrast**: see the token table; checked with a script, not by eye.
- **Keyboard**: skip link; every action is a real `<button>` or `<a>`; visible 3 px focus
  ring; dialogs trap focus and close on Esc; dashboard tabs use arrow keys; the customer
  search is a combobox with arrow keys and Enter; shortcuts (`/`, F2, Ctrl+Enter) are extras.
- **Screen readers**: labelled landmarks (`main`, navigation, search); `aria-current` for
  the current page, tab and status step; icon buttons have names; the POS announces each
  added service in a polite live region; toasts use `role="status"` (errors `alert`);
  chart bars are buttons named with their date, amount and order count.
- **Not color alone**: statuses and payment states are written out; alerts have icons.
- **Targets**: 44 px minimum (48 on touch screens), 8 px apart.
- **Motion**: honours `prefers-reduced-motion`.
- **Zoom**: layouts reflow down to 320 px wide without sideways scrolling (tables become cards).

Known gaps: the dashboard chart has no table view of its own (the Sales report is the
table), and browser date pickers vary in how well they work with screen readers.

---

## 7. Touch and mobile

- Nothing depends on hover; hover styles only apply on devices that can hover, and every
  control has a pressed state.
- Double-tap zoom is off on controls (`touch-action: manipulation`), so taps register at once.
- The POS keypad replaces the system keyboard on touch screens (`inputmode="none"` on
  the amount), so it doesn't cover the screen.
- Number fields use numeric keyboards (`inputmode`), phone fields the phone keypad.
- Dialogs become bottom sheets on phones, with their buttons in thumb reach and the safe
  area respected.
- Phones and portrait tablets get a bottom tab bar with **New order** in the middle.
- Lists turn into cards, filters fold behind a **Filters** button, page actions go full width.

---

## 8. Not built yet

From the redesign brief, these need new business logic or data and were left out of v2.0.0:

| Item | Why it waits | What it would take |
|---|---|---|
| Promo codes | No promotions data or rules exist | A promotions table, validity rules, a code field on the POS |
| Taxes | Prices are tax-inclusive today | A tax setting and a tax line on orders and reports |
| Weight-based pricing | Quantities are whole numbers | Decimal quantities and a per-kg unit on services |
| Roles editor | Roles are fixed (admin, manager, cashier) | Permissions table and checks |
| Inline table editing | Dialogs cover the needs with less risk | Per-field save endpoints |
| Customer profile page | The list and the order filters cover lookups | A page with history, notes and balance |
| SMS when an order is ready | Needs an SMS provider and consent | An integration and a setting |
| Thermal receipt layout (58/80 mm) | Receipts print on A4/Letter | A narrow print stylesheet |
| Offline fonts and icons | Fonts and icons load from Google Fonts and cdnjs | Bundle Plus Jakarta Sans and Font Awesome in `public/` |
| Dark mode | Not needed at a bright counter | Dark values for the tokens |
