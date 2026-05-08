# EXU Finance — Setup Guide

Exponential University Finance & Accounting System

---

## Requirements

- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js (for assets, optional — uses CDN by default)

---

## Installation

### 1. Install Laravel and copy project files

```bash
# Create a new Laravel 11 project first (if not done)
composer create-project laravel/laravel exu-finance
cd exu-finance

# Copy all files from this repo into the project
# (overwrite existing files)
```

### 2. Install dependencies

```bash
composer install
```

Key packages installed:
- `spatie/laravel-permission` — Role & permission management
- `maatwebsite/excel` — Excel export (XLSX)
- `barryvdh/laravel-dompdf` — PDF export

### 3. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```
DB_DATABASE=exu_finance
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Create the database

```sql
CREATE DATABASE exu_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run migrations

```bash
php artisan migrate
```

### 6. Publish Spatie config

```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 7. Seed the database

```bash
php artisan db:seed
```

This creates:
- **Roles**: admin, finance, ceo, coo, external_viewer, auditor
- **Permissions**: Full RBAC permission matrix
- **Event Categories**: Scale with AI, AI Hackathon, Corporate Training, etc.
- **Expense Categories**: Venue, Food & Beverage, Staff Salaries, etc.
- **Bank Accounts**: PayMongo Sales, PayMongo Expense, GoTyme, Metrobank
- **Default Users** (see below)

### 8. Create storage symlink

```bash
php artisan storage:link
```

### 9. Serve the application

```bash
php artisan serve
```

Open: http://localhost:8000

---

## Default Users

| Name             | Email                                      | Password          | Role     |
|------------------|--------------------------------------------|-------------------|----------|
| EXU Admin        | admin@exponentialuniversity.ph             | Admin@EXU2024!    | admin    |
| Finance Officer  | finance@exponentialuniversity.ph           | Finance@EXU2024!  | finance  |
| CEO              | ceo@exponentialuniversity.ph               | CEO@EXU2024!      | ceo      |
| External Auditor | auditor@exponentialuniversity.ph           | Auditor@EXU2024!  | auditor  |

> **Change all passwords immediately after first login!**

---

## Roles & Access

| Feature              | Admin | Finance | CEO/COO | External/Auditor |
|----------------------|-------|---------|---------|-----------------|
| Dashboard            | ✓     | ✓       | ✓       | ✗               |
| Revenue (CRUD)       | ✓     | ✓       | View    | ✗               |
| Expenses (CRUD)      | ✓     | ✓       | View    | ✗               |
| Bank Transactions    | ✓     | ✓       | View    | ✗               |
| Invoices/AR (CRUD)   | ✓     | ✓       | View    | ✗               |
| Bills/AP (CRUD)      | ✓     | ✓       | View    | ✗               |
| Staff Loans (CRUD)   | ✓     | ✓       | View    | ✗               |
| Financial Statements | ✓     | ✓       | ✓       | ✓ (view only)   |
| Export Reports       | ✓     | ✓       | ✓       | ✓               |
| Settings             | ✓     | ✗       | ✗       | ✗               |

---

## Project Structure

```
app/
├── Exports/                    # Excel export classes
│   ├── IncomeStatementExport.php
│   ├── BalanceSheetExport.php
│   └── CashFlowExport.php
├── Http/
│   ├── Controllers/
│   │   ├── DashboardController.php
│   │   ├── RevenueController.php
│   │   ├── ExpenseController.php
│   │   ├── BankAccountController.php
│   │   ├── BankTransactionController.php
│   │   ├── InvoiceController.php     # Accounts Receivable
│   │   ├── BillController.php        # Accounts Payable
│   │   ├── StaffLoanController.php
│   │   ├── FinancialStatementController.php
│   │   └── SettingsController.php
│   └── Middleware/
│       └── CheckPermission.php
├── Models/
│   ├── User.php
│   ├── EventCategory.php
│   ├── ExpenseCategory.php
│   ├── BankAccount.php
│   ├── RevenueEntry.php
│   ├── ExpenseEntry.php
│   ├── BankTransaction.php
│   ├── Invoice.php
│   ├── InvoicePayment.php
│   ├── Bill.php
│   ├── BillPayment.php
│   ├── StaffLoan.php
│   ├── LoanDeduction.php
│   └── Setting.php
database/
├── migrations/          # 15 migration files
└── seeders/             # 6 seeder files
resources/views/
├── layouts/app.blade.php    # Main layout (dark sidebar)
├── auth/login.blade.php
├── dashboard/index.blade.php
├── revenue/             # Index, Create, Edit, Show
├── expenses/            # Index, Create, Edit, Show
├── bank-accounts/       # Index, Show, Create, Edit
├── bank-transactions/   # Index, Create, Show
├── invoices/            # Index, Create, Edit, Show (with payment modal)
├── bills/               # Index, Create, Edit, Show (with payment modal)
├── staff-loans/         # Index, Create, Edit, Show (with deduction modal)
├── financial-statements/
│   ├── income-statement.blade.php
│   ├── balance-sheet.blade.php
│   ├── cash-flow.blade.php
│   └── pdf/             # PDF templates for DomPDF
└── settings/index.blade.php
routes/
└── web.php
```

---

## Key Features

### Dashboard
- KPI cards: Total Revenue, Total Expenses, Net Profit, Cash in Bank
- Trend chart: Revenue vs Expense (monthly bar + net profit line)
- Expense breakdown: Doughnut chart by category
- Revenue by event category: Horizontal bar chart
- Bank account balances chart
- Recent transactions, outstanding AR, AP, Staff Loans tables
- Alerts for overdue invoices, bills, and unusual expenses
- Period filter: Daily, Weekly, Monthly, Quarterly, Yearly, Custom

### Bank Integration
- Revenue/Expense entries auto-post to bank ledger
- Invoice/Bill payments auto-post to bank ledger
- Manual bank entries supported
- Bank transfers between accounts (debit/credit mirror)

### Financial Statements
- Income Statement (with comparative mode)
- Balance Sheet (with prior year compare)
- Cash Flow Statement (direct method)
- Export to Excel (XLSX) and PDF
- Monthly, Quarterly, Annual, Custom periods

### Accounts Receivable
- Invoice generation with auto-numbering (INV-YYYY-XXXX)
- Overdue detection and status updates
- Payment tracking with bank posting
- Partner management

### Accounts Payable
- Bill tracking with auto-numbering (BILL-YYYY-XXXX)
- Supplier management
- Attachment upload (PDF/image)
- Payment recording with bank deduction

### Staff Loans
- Loan disbursement with bank deduction
- Monthly/Manual/One-time deduction types
- Outstanding balance tracking
- Appears on Balance Sheet as employee receivable

---

## Currency

All amounts are in **Philippine Peso (₱)**. Format: `₱1,234,567.89`

## Fiscal Year

**January – December** (configurable in Settings)

---

## Tech Stack

| Layer        | Technology                          |
|--------------|-------------------------------------|
| Backend      | Laravel 11                          |
| Database     | MySQL 8.0                           |
| Frontend     | Blade + Bootstrap 5.3               |
| Charts       | Chart.js 4                          |
| Date Picker  | Flatpickr                           |
| Tables       | DataTables + Bootstrap 5            |
| Alerts       | SweetAlert2                         |
| Auth/RBAC    | Spatie Laravel Permission           |
| Excel Export | Maatwebsite Excel 3.1               |
| PDF Export   | Barryvdh DomPDF 2.2                 |
| File Storage | Laravel Storage (public disk)       |
