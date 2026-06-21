# Comprehensive Overview: RAB (Rencana Anggaran Biaya) Application
## PT Sertifikasi Bermutu Ketenagalistrikan (PT SBK)

---

## TABLE OF CONTENTS
1. [Executive Summary](#executive-summary)
2. [Main Features & Functionality](#main-features--functionality)
3. [User Roles & Permissions](#user-roles--permissions)
4. [Database Structure](#database-structure)
5. [Business Process & Workflow](#business-process--workflow)
6. [Key Controllers & Models](#key-controllers--models)
7. [Form Inputs & Validations](#form-inputs--validations)
8. [Integration Points](#integration-points)
9. [File Structure & Purposes](#file-structure--purposes)
10. [Critical Business Logic](#critical-business-logic)

---

## EXECUTIVE SUMMARY

**RAB** is a comprehensive **web-based financial management system** designed to digitalize, automate, and secure the complete lifecycle of budgetary expense planning and execution for PT SBK (a certification organization for electrical engineering).

**Key Characteristics:**
- **Technology Stack:** Laravel 12 (PHP 8.3+), MySQL, Tailwind CSS, Vite, vanilla JavaScript
- **Architecture:** Layered Architecture (Controllers, Services, Repositories, Models)
- **Core Function:** Manage budget proposals (RAB) from creation → approval (multi-stage) → payment → cash flow recording
- **Users:** 3 main roles with strict RBAC (Role-Based Access Control)
- **Database Model:** 5 dynamic expense categories with tailored table structures
- **Approval System:** Pool System (shared approval queue) with database transaction locking to prevent race conditions
- **Key Innovation:** Auto-generated WhatsApp notifications, PDF export capabilities, and real-time cash flow integration

---

## MAIN FEATURES & FUNCTIONALITY

### 1. **Dashboard Analytics (Premium Visual Design)**
   - **Summary Widgets:** Display key metrics (Total Proposals, Realized Payments, Awaiting Approvals, Rejected Count)
   - **Double-Line Chart:** Compare budgeted amount vs actual realization
   - **Doughnut Chart with Pointer Callout Lines:** Show RAB status distribution (using custom Google Sheets-style pointer lines with percentage labels)
   - **Dynamic Category Trend Chart:** Visualize spending by category per month with interactive filters
   - **Sorting Controls:** Terbaru/Terlama (Newest/Oldest) with ↑↓ indicators

### 2. **Budget Request Management (RAB Creation)**
   - Create new RAB with auto-generated number (format: `XXX/RAB/SBK/ROMAN_MONTH/YEAR`)
   - Select expense category (determines which detail table to use)
   - Save as Draft (editable, non-submitted)
   - Submit for approval (notifies manager via WhatsApp)
   - Attach description and period information
   - Real-time total calculation via JavaScript

### 3. **Dynamic 5-Category Expense System**
   
   **a) Biaya Operasional (Operational Expenses)**
   - Structure: Volume × Unit Price = Total
   - Grouped into 5 operational groups:
     1. Honor Pencari Peserta (Participant Recruiter Fees)
     2. Uang Transport/Honor Peserta Uji (Transportation/Participant Exam Fees)
     3. Operasional Pembekalan (Provisioning Operations)
     4. Operasional Uji Serkom (Certification Exam Operations)
     5. Honor Asesor (Assessor Fees)
   - **Responsive UI:** Desktop = table; Mobile = card-stack
   - Auto-calculates subtotals per group and grand total

   **b) Petty Cash (Kas Kecil)**
   - Daily small office expenses (parking, snacks, transportation, emergency purchases)
   - Structure: Expense Name, Description, Transaction Date, Nominal Amount
   - Total = Sum of all nominals
   - Optional receipt upload

   **c) Biaya Gaji (Salary Expenses)**
   - Employee name, position (Direktur/Manajer/Admin/OB/Lainnya)
   - Bank account & account number
   - Attendance days, base salary, meal allowance, transport allowance, overtime
   - Total = Salary + (Attendance × (Meal + Transport)) + Overtime
   - Confidential salary data protection

   **d) Biaya Bulanan (Monthly Recurring Expenses)**
   - Electricity, WiFi, water, security, software subscriptions
   - Structure: Payment Name, Customer ID, Account Holder, Period, Bill Amount, Admin Fee
   - Total = Bill Amount + Admin Fee

   **e) Pembayaran PNBP (State Non-Tax Revenue)**
   - Government revenue settlement payments
   - Structure: Item Name, Agenda Number, Level, PNBP Tariff, Company Name

### 4. **Multi-Stage Approval Workflow**
   - **Stage 1 - Manager Review:** Manajer Keuangan reviews and approves/rejects with optional notes
   - **Stage 2 - Director Approval:** Direktur gives final approval or rejection
   - **Pool System:** All managers see the same queue; first-to-action wins (prevented via database locking)
   - **Rejection Handling:** Reverts to Draft with reason for Admin to edit and resubmit
   - **Status Tracking:** `DRAFT` → `DIAJUKAN` → `DISETUJUI_MANAJER` → `DISETUJUI` → `SELESAI`

### 5. **Payment Realization & Proof Upload**
   - Upload payment proof (JPG, PNG, PDF; max 100KB)
   - Record payment date, amount, payment method, recipient details
   - Auto-generates arus kas (cash flow) entry as Dana Keluar (credit)
   - Changes RAB status to `SELESAI` (completed)
   - Permanently locks transaction for audit immutability

### 6. **Cash Flow Management (Arus Kas)**
   - **Manajer Keuangan Only:** Record Dana Masuk (incoming funds) and Saldo Awal (opening balance)
   - **Direktur Read-Only:** Monitor cash position, download proofs, view balances
   - **Auto-Recording:** Payment uploads automatically create Dana Keluar entries
   - **Balance Calculation:** Real-time running balance = Previous Balance ± Transactions
   - **Proof Files:** Upload supporting documents for manual entries

### 7. **Collaborative Discussion Panel**
   - Contextual notes/comments on each RAB detail modal
   - Real-time discussion history with user avatars and timestamps
   - Auto-logged system messages (approval, rejection, payment upload events)
   - Transparent communication trail for audit purposes

### 8. **WhatsApp Integration**
   - Automatic notification when RAB is submitted (formatted message with number, amount, terbilang, and approval link)
   - Integration via Fonnte API (third-party WhatsApp gateway)
   - Phone number normalization (0xxx → 62xxx format)
   - Manual "Send to WhatsApp" button for resending

### 9. **PDF Export Capabilities**
   - **Single RAB Detail Export:** Professional report with company letterhead, tables, and totals
   - **Monthly Report Export:** Summary of all completed RABs with financial recap
   - **Cash Flow Report Export:** Complete arus kas history for month
   - All exports follow professional accounting format (Rupiah currency, signatures, date)

### 10. **User Management (Direktur Only)**
   - Create new user accounts with role assignment
   - Toggle user active/inactive status (soft-deactivate)
   - Upload and crop avatar photos
   - Prevention of duplicate direktur accounts (backend enforcement)

### 11. **Premium UI/UX Features**
   - **Session Keep-Alive:** Background ping every 5 minutes prevents `419 Page Expired` errors
   - **Double-Submit Guard:** Disable button on first click, show loading spinner
   - **DOM Cleanup:** Remove hidden required inputs to prevent silent form failures
   - **Scroll Position Maintenance:** Fragment URLs (#riwayat-table) maintain table focus on pagination
   - **File Viewer:** Smart detection of PDF vs images; inline preview or image viewer

---

## USER ROLES & PERMISSIONS

### 1. **Admin Keuangan (Finance Administrator)**
   **Responsibility:** Daily operational budget management, payments, and field coordination

   **Permissions:**
   - ✅ Create new RAB proposals
   - ✅ Edit RAB at DRAFT, DITOLAK, and DIAJUKAN status
   - ✅ Delete RAB at DRAFT and DIAJUKAN status
   - ✅ Submit RAB for approval (changes status to DIAJUKAN)
   - ✅ Send WhatsApp notification to manager
   - ✅ Upload payment proof and realization for DISETUJUI RABs
   - ✅ Add comments to discussion panel
   - ✅ Export single RAB to PDF
   - ❌ Cannot access `/cash-flow` (arus kas) page
   - ❌ Cannot record Dana Masuk manually

   **Routes:** `/rab/*`, `/admin/dashboard`, `/rab/{rab}/payment/*`

---

### 2. **Manajer Keuangan (Finance Manager)**
   **Responsibility:** First-stage verification of budgets, cash liquidity management, reporting

   **Permissions:**
   - ✅ View approval queue (RABs with DIAJUKAN status)
   - ✅ Approve RAB (changes status to DISETUJUI_MANAJER)
   - ✅ Reject RAB with mandatory reason notes (reverts to DITOLAK)
   - ✅ Record Dana Masuk (incoming funds) in arus kas
   - ✅ Record Saldo Awal (opening balance) in arus kas
   - ✅ Upload proof files for manual arus kas entries
   - ✅ View and filter arus kas (read/write)
   - ✅ Export monthly report to PDF
   - ✅ Add comments to discussion panel
   - ❌ Cannot create/edit/delete RABs
   - ❌ Cannot approve as director
   - ❌ Cannot manage users

   **Routes:** `/manajer/*`, `/manajer/dashboard`, `/manajer/rab/*`, `/manajer/cash-flow/*`, `/report/*`

---

### 3. **Direktur (Director)**
   **Responsibility:** Final financial decision, global cash monitoring, user account governance

   **Permissions:**
   - ✅ View approval queue (RABs with DISETUJUI_MANAJER status)
   - ✅ Give final approval (changes status to DISETUJUI)
   - ✅ Reject with mandatory reason notes (reverts to DITOLAK)
   - ✅ View arus kas in READ-ONLY mode (cannot record transactions)
   - ✅ Download proof files from arus kas
   - ✅ Filter and monitor cash position
   - ✅ Create new user accounts
   - ✅ Toggle user active/inactive status
   - ✅ Upload and crop user avatars
   - ✅ Add comments to discussion panel
   - ❌ Cannot be created more than once (single director instance enforced by backend)
   - ❌ Cannot create/edit/delete RABs
   - ❌ Cannot approve as manager
   - ❌ Cannot record cash flow transactions

   **Routes:** `/direktur/*`, `/direktur/dashboard`, `/direktur/rab/*`, `/direktur/users/*`, `/direktur/cash-flow/*` (read-only)

---

## DATABASE STRUCTURE

### Entity-Relationship Overview

```
Users (1) ──── (M) Rabs ──── (M) {
                                 - OperationalExpenseItems
                                 - PettyCashItems
                                 - SalaryExpenseItems
                                 - MonthlyExpenseItems
                                 - PnbpExpenseItems
                               }

Users (1) ──── (M) RabApprovals (M) Rabs
Users (1) ──── (M) RabPayments (M) Rabs
Users (1) ──── (M) CashFlows (M) {Rabs, RabPayments}
Users (1) ──── (M) RabDiscussions (M) Rabs
Users (1) ──── (M) AuditLogs (M) Rabs
Users (1) ──── (M) ReportExports (M) null

ExpenseTypes (1) ──── (M) Rabs
```

### Core Tables

#### **1. users**
```
- id (BIGINT, PK)
- name (VARCHAR)
- email (VARCHAR, Unique)
- password (VARCHAR, hashed)
- role (ENUM: admin_keuangan, manajer_keuangan, direktur)
- phone_number (VARCHAR, nullable) — For WhatsApp notifications
- avatar (VARCHAR, nullable) — URL to profile picture
- is_active (BOOLEAN, default: true)
- remember_token (VARCHAR, nullable)
- created_at, updated_at, deleted_at (soft delete)
```

#### **2. rabs** (Main Budget Document)
```
- id (BIGINT, PK)
- rab_number (VARCHAR, Unique) — Auto-generated format: XXX/RAB/SBK/ROMAN_MONTH/YEAR
- request_date (DATE)
- period_month (VARCHAR, nullable)
- period_year (VARCHAR, nullable)
- user_id (BIGINT, FK → users) — Admin creator
- expense_type_id (BIGINT, FK → expense_types) — Determines which detail table
- description (TEXT, nullable)
- total_amount (DECIMAL(15,2))
- status (ENUM: draft, diajukan, disetujui_manajer, disetujui_direktur, disetujui, ditolak, selesai)
- submitted_at (TIMESTAMP, nullable)
- approved_by_manager_at (TIMESTAMP, nullable)
- approved_by_director_at (TIMESTAMP, nullable)
- completed_at (TIMESTAMP, nullable)
- created_at, updated_at, deleted_at (soft delete)
```

#### **3. expense_types** (Budget Categories)
```
- id (BIGINT, PK)
- name (VARCHAR) — e.g., "Biaya Operasional"
- code (VARCHAR, Unique) — e.g., "operasional", "petty_cash", "gaji", "bulanan", "pnbp"
- description (TEXT, nullable)
- is_active (BOOLEAN, default: true)
- created_at, updated_at, deleted_at
```

#### **4. operational_expense_items** (Detail for Operasional)
```
- id (BIGINT, PK)
- rab_id (BIGINT, FK → rabs)
- need_name (VARCHAR)
- description (TEXT, nullable)
- volume (DECIMAL(12,2))
- unit (VARCHAR)
- unit_price (DECIMAL(15,2))
- total (DECIMAL(15,2)) = volume × unit_price
- created_at, updated_at
```

#### **5. petty_cash_items** (Detail for Kas Kecil)
```
- id (BIGINT, PK)
- rab_id (BIGINT, FK → rabs)
- expense_name (VARCHAR)
- description (TEXT, nullable)
- transaction_date (DATE)
- nominal (DECIMAL(15,2))
- receipt_path (VARCHAR, nullable)
- created_at, updated_at
```

#### **6. salary_expense_items** (Detail for Gaji)
```
- id (BIGINT, PK)
- rab_id (BIGINT, FK → rabs)
- employee_name (VARCHAR)
- position (VARCHAR, nullable)
- bank_account_number (VARCHAR)
- bank_name (VARCHAR)
- attendance_days (INTEGER)
- salary_nominal (DECIMAL(15,2))
- meal_allowance (DECIMAL(15,2))
- transport_allowance (DECIMAL(15,2))
- overtime (DECIMAL(15,2))
- total_salary (DECIMAL(15,2)) = salary + (attendance × (meal + transport)) + overtime
- description (TEXT, nullable)
- created_at, updated_at
```

#### **7. monthly_expense_items** (Detail for Bulanan)
```
- id (BIGINT, PK)
- rab_id (BIGINT, FK → rabs)
- payment_name (VARCHAR)
- customer_id (VARCHAR)
- account_holder (VARCHAR)
- period (VARCHAR)
- description (TEXT, nullable)
- bill_nominal (DECIMAL(15,2))
- admin_fee (DECIMAL(15,2), default: 0)
- total_payment (DECIMAL(15,2)) = bill_nominal + admin_fee
- created_at, updated_at
```

#### **8. pnbp_expense_items** (Detail for PNBP)
```
- id (BIGINT, PK)
- rab_id (BIGINT, FK → rabs)
- item_name (VARCHAR)
- agenda_number (VARCHAR)
- level (VARCHAR)
- tarif_pnbp (DECIMAL(15,2))
- company_name (VARCHAR)
- created_at, updated_at
```

#### **9. rab_approvals** (Approval History)
```
- id (BIGINT, PK)
- rab_id (BIGINT, FK → rabs)
- user_id (BIGINT, FK → users) — Who approved/rejected
- role (ENUM: manajer_keuangan, direktur)
- approval_level (ENUM: manager, director)
- status (ENUM: approved, rejected)
- notes (TEXT, nullable) — Required if rejected
- approved_at (TIMESTAMP, nullable)
- rejected_at (TIMESTAMP, nullable)
- created_at, updated_at
```

#### **10. rab_payments** (Payment Records)
```
- id (BIGINT, PK)
- rab_id (BIGINT, FK → rabs) — DISETUJUI RAB only
- paid_by (BIGINT, FK → users) — Admin who processed payment
- payment_date (DATE)
- paid_amount (DECIMAL(15,2))
- payment_method (VARCHAR) — e.g., "Bank Transfer"
- recipient_account (VARCHAR, nullable) — Target account
- recipient_name (VARCHAR, nullable)
- proof_file_path (VARCHAR) — JPG/PNG/PDF, max 100KB
- notes (TEXT, nullable)
- created_at, updated_at
```

#### **11. cash_flows** (Arus Kas Ledger)
```
- id (BIGINT, PK)
- rab_id (BIGINT, FK → rabs, nullable)
- payment_id (BIGINT, FK → rab_payments, nullable)
- transaction_date (DATE)
- type (ENUM: saldo_awal, dana_masuk, dana_keluar)
- description (TEXT)
- debit (DECIMAL(15,2), default: 0) — Incoming funds
- credit (DECIMAL(15,2), default: 0) — Outgoing funds
- balance (DECIMAL(15,2)) — Running balance = Previous + Debit - Credit
- proof_file_path (VARCHAR, nullable)
- created_by (BIGINT, FK → users)
- created_at, updated_at
```

#### **12. rab_discussions** (Collaborative Notes)
```
- id (BIGINT, PK)
- rab_id (BIGINT, FK → rabs)
- user_id (BIGINT, FK → users)
- message (TEXT)
- created_at, updated_at
```

#### **13. rab_notifications** (System Messages)
```
- id (BIGINT, PK)
- user_id (BIGINT, FK → users)
- rab_id (BIGINT, FK → rabs)
- type (VARCHAR) — e.g., "submission", "approval", "rejection"
- title (VARCHAR)
- message (TEXT)
- link (VARCHAR, nullable) — URL to RAB
- is_read (BOOLEAN, default: false)
- created_at, updated_at
```

#### **14. audit_logs** (Activity Trail)
```
- id (BIGINT, PK)
- user_id (BIGINT, FK → users)
- rab_id (BIGINT, FK → rabs, nullable)
- action (VARCHAR) — e.g., "create", "approve_manager", "approve_director", "upload_payment"
- description (TEXT)
- old_values (JSON, nullable)
- new_values (JSON, nullable)
- ip_address (VARCHAR, nullable)
- created_at, updated_at
```

#### **15. report_exports** (Export History)
```
- id (BIGINT, PK)
- exported_by (BIGINT, FK → users)
- report_type (VARCHAR) — e.g., "monthly_report", "cash_flow_report"
- file_path (VARCHAR)
- filters (JSON, nullable)
- created_at, updated_at
```

---

## BUSINESS PROCESS & WORKFLOW

### **Complete RAB Lifecycle State Machine**

```
                     ┌─────────────────────────────────┐
                     │      DRAFT (Editable)           │
                     │ (Admin creates locally, unsent) │
                     └────────────┬────────────────────┘
                                  │
                                  │ Admin clicks "Submit"
                                  ▼
                     ┌─────────────────────────────────┐
                     │    DIAJUKAN (In Queue)          │
                     │ (Awaiting Manager approval)     │
                     │ (Still editable by Admin)       │
                     └────────────┬────────────────────┘
                                  │
                    ┌─────────────┴─────────────┐
                    │                           │
        Manager rejects              Manager approves
        (with reason)                (optional notes)
                    │                           │
                    ▼                           ▼
         ┌─────────────────────────┐   ┌────────────────────────┐
         │     DITOLAK             │   │ DISETUJUI_MANAJER      │
         │ (Back to editable)      │   │ (Locked from Admin)    │
         │ (Admin must revise)     │   │ (Forwarded to Director)│
         │                         │   │                        │
         │ (Can re-submit)         │   └────────┬───────────────┘
         └─────────────────────────┘            │
                 ^                               │
                 │                   ┌───────────┴──────────┐
                 │                   │                      │
                 │       Director rejects   Director approves
                 │       (with reason)      (optional notes)
                 │           │                      │
                 └───────────┘                      ▼
                                      ┌────────────────────────┐
                                      │    DISETUJUI           │
                                      │ (Ready to pay)         │
                                      │ (Locked completely)    │
                                      └────────────┬───────────┘
                                                   │
                                    Admin uploads payment proof
                                                   │
                                                   ▼
                                      ┌────────────────────────┐
                                      │     SELESAI            │
                                      │ (Payment completed)    │
                                      │ (PERMANENTLY LOCKED)   │
                                      │ (Auto arus kas entry)  │
                                      └────────────────────────┘
```

### **Detailed Workflow Steps**

#### **Phase 1: Creation & Submission (Admin Keuangan)**
1. Admin navigates to `/rab/create`
2. Enters RAB metadata: number (auto or manual), period, description
3. Selects expense type (triggers JavaScript to load appropriate detail form)
4. Fills detail items (volume, prices, employee data, etc.)
5. JavaScript auto-calculates subtotals in real-time
6. **Option A:** Saves as DRAFT (stored but not notified)
7. **Option B:** Submits as DIAJUKAN (status changes, WhatsApp notifies manager)

#### **Phase 2: Manager Approval (Manajer Keuangan - Pool System)**
1. Manager views dashboard showing DIAJUKAN queue
2. Clicks on RAB to open detail modal via AJAX
3. Reviews all line items, total amount, and discussion notes
4. **Option A:** Clicks "Setujui Tahap 1"
   - Backend locks RAB row (select for update)
   - Validates current status is DIAJUKAN
   - If validation passes: Creates RabApproval record, changes status to DISETUJUI_MANAJER, notifies Direktur via WhatsApp
   - If another manager already processed it: Shows info message and rolls back transaction
5. **Option B:** Clicks "Tolak"
   - Prompts for mandatory rejection reason
   - Creates RabApproval record with REJECTED status
   - Changes status to DITOLAK
   - RAB becomes editable again for Admin to revise and resubmit

#### **Phase 3: Director Final Approval (Direktur)**
1. Director views dashboard showing DISETUJUI_MANAJER queue
2. Clicks on RAB to review final details
3. **Option A:** Clicks "Setujui Akhir"
   - Same transaction locking as manager approval
   - Changes status to DISETUJUI
   - Creates RabApproval record
   - Sends WhatsApp notification to Admin: "Your RAB is approved and ready for payment"
4. **Option B:** Clicks "Tolak"
   - Same rejection flow as manager; reverts to DITOLAK for Admin revision

#### **Phase 4: Payment Realization (Admin Keuangan)**
1. Admin views DISETUJUI RABs (only these are payable)
2. Clicks "Upload Bukti Bayar" button
3. Fills payment form:
   - Payment date
   - Paid amount (normalized from Rp format)
   - Payment method (e.g., Bank Transfer)
   - Recipient account & name (optional)
   - Proof file upload (JPG/PNG/PDF, max 100KB)
   - Notes (optional)
4. System validates:
   - RAB status is DISETUJUI
   - Proof file is ≤ 100KB
   - Paid amount ≤ cash flow balance
5. On success:
   - Creates RabPayment record
   - Stores proof file to `storage/app/public/payment-proofs/`
   - Changes RAB status to SELESAI
   - **Auto-records** arus kas entry as Dana Keluar (credit)
   - Locks transaction permanently (immutable for audit)
   - Adds auto-comment to discussion panel

#### **Phase 5: Cash Flow Recording (Manajer Keuangan)**
- **Manual Entry:**
  1. Manager navigates to `/manajer/cash-flow`
  2. Clicks "Tambah Transaksi" (Add Transaction)
  3. Selects type: Saldo Awal (opening) or Dana Masuk (incoming)
  4. Fills: transaction date, description, amount, proof file (optional)
  5. System calculates: New Balance = Previous Balance + Debit (for incoming) - Credit (for outgoing)
- **Auto-Entry (from Payment):**
  - Triggered by payment upload
  - Type = Dana Keluar
  - Amount = Paid Amount
  - Description = "Pembayaran kebutuhan RAB {number}"
  - Balance automatically decrements

---

## KEY CONTROLLERS & MODELS

### **Controllers** (`app/Http/Controllers/`)

#### **1. RabController**
- `index()` — List RABs with filters (status, expense_type, search, date range, sort)
- `create()` — Show form to create new RAB
- `store()` — Save new RAB to database
- `show()` — Display RAB detail modal (with all related items, approvals, discussions, payment)
- `edit()` — Show edit form (Admin only, if status allows)
- `update()` — Update RAB data
- `destroy()` — Delete RAB (DRAFT/DIAJUKAN only)
- `submit()` — Change status to DIAJUKAN and send WhatsApp
- `listForApprover()` — Return filtered list for Manager/Director (DIAJUKAN/DISETUJUI_MANAJER respectively)
- `exportPdf()` — Generate and download PDF report of single RAB

#### **2. ApprovalRabController**
- `approveByManager(Request $request, Rab $rab)` — Manager approves (DIAJUKAN → DISETUJUI_MANAJER)
  - Uses DB transaction with lockForUpdate() to prevent race condition
  - Creates RabApproval record
  - Notifies Director via WhatsApp
- `approveByDirector(Request $request, Rab $rab)` — Director approves (DISETUJUI_MANAJER → DISETUJUI)
  - Same transaction protection
  - Notifies Admin that RAB is ready for payment
- `reject(Request $request, Rab $rab)` — Both roles can reject (→ DITOLAK)
  - Mandatory notes required
  - RAB becomes editable again
  - Creates RabApproval with REJECTED status

#### **3. PaymentController**
- `create(Rab $rab)` — Show payment proof upload form
- `store(Request $request, Rab $rab)` — Process payment:
  - Validates RAB is DISETUJUI
  - Validates proof file ≤ 100KB
  - Validates sufficient cash balance
  - Stores proof file
  - Creates RabPayment record
  - Auto-creates arus kas entry as Dana Keluar
  - Changes RAB status to SELESAI

#### **4. CashFlowController**
- `index(Request $request)` — Show arus kas ledger with filters (type, date range, search)
  - Manager: Full access (read/write)
  - Director: Read-only view
- `store(Request $request)` — Manager manually records Dana Masuk or Saldo Awal
  - Validates transaction date
  - Stores proof file if provided
  - Calculates new running balance
  - Creates CashFlow record

#### **5. UserManagementController** (Direktur only)
- `index()` — List all users with avatars and status
- `create()` — Show user registration form
- `store()` — Create new user
  - Prevents creating duplicate direktur role
- `edit()` — Show edit user form
- `update()` — Update user data
- `toggleActive()` — Soft-deactivate/reactivate user (PATCH)
- `destroy()` — Delete user (soft delete)

#### **6. DashboardController**
- `admin()` — Return Admin Keuangan dashboard with analytics
- `manajer()` — Return Manajer Keuangan dashboard
- `direktur()` — Return Direktur dashboard
- `chartData()` — AJAX endpoint returning chart data (for Chart.js)

#### **7. ReportExportController**
- `index()` — Show report selection page
- `exportPdf()` — Generate PDF report (monthly summary, arus kas, completion status)

#### **8. RabDiscussionController**
- `store()` — Save new comment/note to RabDiscussion

#### **9. RabNotificationController**
- `open()` — Mark notification as read and redirect to RAB

#### **10. ProfileController**
- `update()` — Update user profile (name, email, avatar crop)

---

### **Models** (`app/Models/`)

#### **1. User**
```php
Relationships:
- hasMany(Rab) — RABs created by this user
- hasMany(RabApproval) — Approvals given by this user
- hasMany(RabPayment, 'paid_by') — Payments processed by this user
- hasMany(CashFlow, 'created_by') — Arus kas entries created by this user
- hasMany(RabDiscussion) — Discussion messages from this user
- hasMany(AuditLog) — Audit log actions by this user

Key Methods:
- isAdmin(), isManager(), isDirector() — Role checking
- scopeActive() — Filter active users only
```

#### **2. Rab**
```php
Relationships:
- belongsTo(User) — Admin creator
- belongsTo(ExpenseType) — Expense category
- hasMany(OperationalExpenseItem)
- hasMany(PettyCashItem)
- hasMany(SalaryExpenseItem)
- hasMany(MonthlyExpenseItem)
- hasMany(PnbpExpenseItem)
- hasMany(RabApproval) — Approval history
- hasOne(RabPayment) — Payment record
- hasMany(RabDiscussion) — Discussion notes
- hasMany(AuditLog) — Audit trail
- hasMany(CashFlow) — Related arus kas entries

Key Methods:
- getDetailItems() — Dynamically fetch correct item table based on expense_type
- getTotalAmount() — Calculate sum of all detail items
- canEdit() — Check if status allows editing
- canDelete() — Check if status allows deletion
- generateNumber() — Static method to auto-generate RAB number
- buildWhatsAppSubmissionMessage() — Format WhatsApp message
- notifyRole() — Send notification to specific role
```

#### **3. ExpenseType**
```php
Relationships:
- hasMany(Rab) — RABs of this type

Enum Values:
- OPERASIONAL
- PETTY_CASH
- GAJI
- BULANAN
- PNBP
```

#### **4. {Operational,PettyCash,Salary,Monthly,Pnbp}ExpenseItem**
```php
Relationships:
- belongsTo(Rab) — Parent RAB

Calculated Fields (Virtual):
- total (auto-calculated per formula per type)
```

#### **5. RabApproval**
```php
Attributes:
- rab_id, user_id, role, approval_level, status, notes
- approved_at / rejected_at timestamps

Usage:
- Audit trail for who approved/rejected and when
- Prevents multiple simultaneous approvals via state checking
```

#### **6. RabPayment**
```php
Relationships:
- belongsTo(Rab)
- belongsTo(User, 'paid_by')
- hasOne(CashFlow, 'payment_id') — Related arus kas entry

Attributes:
- payment_date, paid_amount, payment_method, recipient_account, recipient_name
- proof_file_path (immutable after creation)
```

#### **7. CashFlow**
```php
Relationships:
- belongsTo(Rab, nullable)
- belongsTo(RabPayment, 'payment_id', nullable)
- belongsTo(User, 'created_by')

Attributes:
- transaction_date, type (ENUM: saldo_awal, dana_masuk, dana_keluar)
- debit (incoming), credit (outgoing), balance (running)

Key: Immutable after creation (no edit/delete allowed for audit compliance)
```

#### **8. AuditLog**
```php
Relationships:
- belongsTo(User)
- belongsTo(Rab, nullable)

Attributes:
- action (e.g., create, approve_manager, approve_director, upload_payment)
- description, old_values (JSON), new_values (JSON)
- ip_address

Static Method:
- log($action, $description, $rabId, $oldValues, $newValues)
```

#### **9. RabDiscussion**
```php
Relationships:
- belongsTo(Rab)
- belongsTo(User) — Who posted message

Attributes:
- message, timestamps

Auto-populated by:
- Manual user comments
- System notifications (approval, rejection, payment upload)
```

#### **10. Setting**
```php
Simple key-value store:
- company_name, company_address
- report_signer_name, report_signer_position
- etc.
```

---

## FORM INPUTS & VALIDATIONS

### **RAB Creation Form**
| Field | Type | Required | Validation | Purpose |
|-------|------|----------|-----------|---------|
| RAB Number | Text/Auto | Yes | Unique | Document identifier |
| Request Date | Date | Yes | past/today | When request was made |
| Period Month | Select | No | 1-12 | Budget month |
| Period Year | Select | No | 4-digit year | Budget year |
| Expense Type | Select | Yes | Must exist | Determines detail table |
| Description | Textarea | No | Max 1000 chars | Budget purpose |

### **Operational Expense Items** (Repeating Rows)
| Field | Type | Required | Validation | Formula |
|-------|------|----------|-----------|---------|
| Need Name | Text | Yes | Max 255 | What's being purchased |
| Description | Textarea | No | Max 1000 | Details |
| Volume | Decimal | Yes | > 0 | Quantity |
| Unit | Text | Yes | Max 50 | Unit of measure |
| Unit Price | Currency | Yes | > 0 | Price per unit |
| Total | Auto | — | volume × unit_price | —  |

### **Petty Cash Items**
| Field | Type | Required | Validation |
|-------|------|----------|-----------|
| Expense Name | Text | Yes | Max 255 |
| Description | Textarea | No | Max 1000 |
| Transaction Date | Date | Yes | Past/today |
| Nominal | Currency | Yes | > 0 |
| Receipt | File | No | JPG/PNG/PDF, ≤ 100KB |

### **Salary Expense Items**
| Field | Type | Required | Validation |
|-------|------|----------|-----------|
| Employee Name | Text | Yes | Max 255 |
| Position | Select | No | Direktur/Manajer/Admin/OB/Lainnya |
| Bank Name | Text | Yes | Max 255 |
| Bank Account Number | Text | Yes | Max 30 digits |
| Attendance Days | Integer | Yes | 1-31 |
| Base Salary | Currency | Yes | > 0 |
| Meal Allowance (daily) | Currency | No | ≥ 0 |
| Transport Allowance (daily) | Currency | No | ≥ 0, default 20,000 |
| Overtime | Currency | No | ≥ 0 |
| Description | Textarea | No | Max 1000 |

### **Monthly Expense Items**
| Field | Type | Required | Validation |
|-------|------|----------|-----------|
| Payment Name | Text | Yes | Max 255 |
| Customer ID | Text | Yes | Max 50 |
| Account Holder | Text | Yes | Max 255 |
| Period | Text | Yes | Format: MM/YYYY |
| Description | Textarea | No | Max 1000 |
| Bill Amount | Currency | Yes | > 0 |
| Admin Fee | Currency | No | ≥ 0 |

### **PNBP Expense Items**
| Field | Type | Required | Validation |
|-------|------|----------|-----------|
| Item Name | Text | Yes | Max 255 |
| Agenda Number | Text | Yes | Max 100 |
| Level | Text | Yes | Max 100 |
| PNBP Tariff | Currency | Yes | > 0 |
| Company Name | Text | Yes | Max 255 |

### **Approval Form**
| Field | Type | Required | When |
|-------|------|----------|------|
| Notes | Textarea | ✓ (if rejecting) | Only on rejection |

### **Payment Proof Upload Form**
| Field | Type | Required | Validation |
|-------|------|----------|-----------|
| Payment Date | Date | Yes | Past/today |
| Paid Amount | Currency | Yes | > 0, ≤ cash balance |
| Payment Method | Text | Yes | Max 100 |
| Recipient Account | Text | No | Max 100 |
| Recipient Name | Text | No | Max 255 |
| Proof File | File | Yes | JPG/PNG/PDF, max 100KB (validated frontend & backend) |
| Notes | Textarea | No | Max 1000 |

### **Cash Flow Manual Entry Form**
| Field | Type | Required | Validation |
|-------|------|----------|-----------|
| Transaction Type | Select | Yes | Saldo Awal, Dana Masuk |
| Transaction Date | Date | Yes | Past/today |
| Description | Text | Yes | Max 255 |
| Amount | Currency | Yes | > 0 |
| Proof File | File | No | JPG/PNG/PDF |

### **User Registration Form** (Direktur only)
| Field | Type | Required | Validation |
|-------|------|----------|-----------|
| Name | Text | Yes | Max 255 |
| Email | Email | Yes | Unique, valid email format |
| Password | Password | Yes | Min 8 chars |
| Role | Select | Yes | admin_keuangan, manajer_keuangan (direktur prevented) |
| Phone Number | Tel | No | For WhatsApp; normalized to 62xxx format |

---

## INTEGRATION POINTS

### **1. WhatsApp Notifications (Fonnte API)**
**Service:** `App\Services\WhatsAppService`

**Method:** `send(string $target, string $message): bool`

**Parameters:**
- `$target` — Phone number (0xxxx or 62xxxx format, auto-normalized)
- `$message` — Message content with auto-line breaks

**Triggered Events:**
- RAB submitted (Admin → Manager)
- RAB approved by Manager (Manager → Director)
- RAB approved by Director (Director → Admin)
- RAB rejected at any stage

**Message Format Example:**
```
📋 *Pengajuan RAB Baru*

Nomor RAB: RAB/001/SBK/V/2026
Kategori: Biaya Gaji
Total Nominal: Rp 25.000.000
Terbilang: Dua Puluh Lima Juta Rupiah

Silakan tinjau dan berikan persetujuan:
https://app.rab.test/manajer/rab/123

Terimakasih,
Sistem RAB PT SBK
```

**Configuration:**
- API Provider: Fonnte (Third-party WhatsApp Gateway)
- Token: Environment variable `FONNTE_TOKEN`
- Endpoint: `https://api.fonnte.com/send`
- Logs: All attempts logged to `storage/logs/laravel.log`

---

### **2. PDF Export (Barryvdh/DomPDF)**
**Package:** `barryvdh/laravel-dompdf`

**Routes:**
- `GET /rab/{rab}/export-pdf` — Single RAB detail PDF
- `GET /report/export-pdf` — Monthly report PDF

**Controller:** `ReportExportController@exportPdf()`

**Implementation:**
```php
use Barryvdh\DomPDF\Facade\Pdf;

Pdf::loadView('reports.pdf', $data)
    ->setPaper('A4', 'landscape')
    ->download('RAB_' . $rab->rab_number . '.pdf');
```

**PDF Contents:**
- Company letterhead (PT SBK logo, address, contact)
- RAB number, date, period
- Detailed item breakdown with subtotals
- Approval signatures section
- Footer with generated date/time

---

### **3. File Storage (Laravel Storage Disk)**
**Disk:** Public (`storage/app/public/`)

**Upload Locations:**
- `/payment-proofs/` — Payment proof files (JPG, PNG, PDF)
- `/user-avatars/` — User profile pictures
- `/arus-kas-proofs/` — Cash flow entry proof files
- `/export-reports/` — Generated PDF reports

**File Access:**
- Route: `GET /file/{path}?download=1` (forces download) or `GET /file/{path}` (inline view)
- Smart detection: PDF → iframe viewer; Images → img tag viewer
- 404 handling: Friendly error message if file missing

---

### **4. Email (Laravel Mail)** - Optional Future Integration
- `.env` configuration for SMTP (currently not heavily used)
- Can send password reset emails via `AuthController`

---

### **5. Database Transactions (ACID Compliance)**
**Purpose:** Prevent race conditions during multi-manager approval

**Implementation:**
```php
DB::beginTransaction();
try {
    $rab = Rab::where('id', $rab->id)->lockForUpdate()->first();
    
    if ($rab->status !== RabStatus::DIAJUKAN) {
        DB::rollBack();
        return back()->with('error', 'Already processed');
    }
    
    // Update status, create approval record, etc.
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    return back()->withErrors(['error' => $e->getMessage()]);
}
```

**Impact:** Guarantees atomic state changes; no partial updates

---

### **6. Real-Time Calculations (Vanilla JavaScript)**
**Location:** `resources/js/`

**Functions:**
- Auto-calculate item totals when volume/price changes
- Auto-calculate RAB grand total from all items
- Auto-format currency input (Rp display)
- Real-time validation feedback

**Framework:** Vanilla ES6+ (no jQuery/Vue/React)

---

## FILE STRUCTURE & PURPOSES

### **App Directory Structure**

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ApprovalRabController.php — Approval workflow logic
│   │   ├── AuthController.php — Login, logout, password reset
│   │   ├── CashFlowController.php — Arus kas management
│   │   ├── Controller.php — Base controller
│   │   ├── DashboardController.php — Analytics dashboards per role
│   │   ├── PaymentController.php — Payment realization
│   │   ├── ProfileController.php — User profile updates
│   │   ├── RabController.php — CRUD and approval queues
│   │   ├── RabDiscussionController.php — Comments/notes
│   │   ├── RabNotificationController.php — Notification handling
│   │   ├── ReportExportController.php — PDF exports
│   │   └── UserManagementController.php — User admin (Direktur only)
│   │
│   ├── Middleware/
│   │   ├── CheckRole.php — Role-based access control middleware
│   │   └── EnsureUserIsActive.php — Check user is_active = true
│   │
│   └── Requests/
│       ├── Rab/ — Form request validation for RAB operations
│       ├── Approval/ — Form request validation for approvals
│       ├── Payment/ — Form request validation for payments
│       └── User/ — Form request validation for user management
│
├── Models/
│   ├── AuditLog.php — Activity audit trail
│   ├── CashFlow.php — Arus kas ledger entries
│   ├── ExpenseType.php — Budget category reference
│   ├── MonthlyExpenseItem.php — Biaya Bulanan detail
│   ├── OperationalExpenseItem.php — Biaya Operasional detail
│   ├── PettyCashItem.php — Petty Cash detail
│   ├── PnbpExpenseItem.php — PNBP payment detail
│   ├── Rab.php — Main budget document
│   ├── RabApproval.php — Approval history record
│   ├── RabDiscussion.php — Comments/discussion notes
│   ├── RabNotification.php — System notifications
│   ├── RabPayment.php — Payment records
│   ├── ReportExport.php — Export history
│   ├── SalaryExpenseItem.php — Biaya Gaji detail
│   ├── Setting.php — System configuration key-value store
│   └── User.php — User account model
│
├── Enums/
│   ├── ApprovalStatus.php — approved, rejected
│   ├── CashFlowType.php — saldo_awal, dana_masuk, dana_keluar
│   ├── ExpenseType.php — operasional, petty_cash, gaji, bulanan, pnbp
│   ├── RabStatus.php — draft, diajukan, disetujui_manajer, disetujui_direktur, disetujui, ditolak, selesai
│   └── UserRole.php — admin_keuangan, manajer_keuangan, direktur
│
├── Services/
│   └── WhatsAppService.php — Fonnte API wrapper for WhatsApp notifications
│
├── Providers/
│   ├── AppServiceProvider.php — Service container bindings
│   └── RouteServiceProvider.php (inherited) — Route registration
│
├── Traits/
│   ├── HasAuditLog.php — Auto-log model changes
│   ├── GeneratesRabNumber.php — RAB number generation
│   └── HasStatusBadge.php — Status color/badge formatting
│
└── Jobs/
    └── SendWhatsAppNotification.php — Queued WhatsApp sending (if needed for async)
```

### **Resources Directory (Views)**

```
resources/views/
├── auth/
│   ├── login.blade.php — Login form
│   ├── forgot-password.blade.php — Password reset request
│   └── reset-password.blade.php — Password reset form
│
├── admin/
│   ├── dashboard.blade.php — Admin Keuangan dashboard with charts
│   ├── rab/
│   │   ├── index.blade.php — List RABs with filters & modals
│   │   ├── create.blade.php — Create new RAB form
│   │   ├── edit.blade.php — Edit RAB form
│   │   ├── show.blade.php — Detail modal (AJAX response)
│   │   └── partials/
│   │       ├── operational-form.blade.php — Dynamic form for operasional items
│   │       ├── petty-cash-form.blade.php
│   │       ├── salary-form.blade.php
│   │       ├── monthly-form.blade.php
│   │       └── pnbp-form.blade.php
│   └── payment/
│       └── create.blade.php — Payment proof upload form
│
├── manajer/
│   ├── dashboard.blade.php — Manajer Keuangan dashboard
│   ├── rab/
│   │   ├── index.blade.php — Approval queue list
│   │   └── show.blade.php — Detail view for approval (non-editable)
│   └── cash-flow/
│       └── index.blade.php — Arus kas ledger (read/write)
│
├── direktur/
│   ├── dashboard.blade.php — Direktur dashboard
│   ├── rab/
│   │   ├── index.blade.php — Final approval queue
│   │   └── show.blade.php — Detail view for approval
│   ├── users/
│   │   ├── index.blade.php — User list with avatars & status
│   │   ├── create.blade.php — User registration form
│   │   ├── edit.blade.php — Edit user form
│   ├── cash-flow/
│   │   └── index.blade.php — Arus kas read-only view
│
├── reports/
│   ├── index.blade.php — Report selection page
│   ├── pdf.blade.php — Monthly report PDF template
│   └── cash-flow-pdf.blade.php — Arus kas PDF template
│
├── components/
│   ├── navbar.blade.php — Top navigation bar
│   ├── sidebar.blade.php — Role-based sidebar menu
│   ├── status-badge.blade.php — Status color badge component
│   ├── modal-rab-detail.blade.php — Reusable RAB detail modal
│   └── discussion-panel.blade.php — Discussion thread component
│
├── layouts/
│   ├── app.blade.php — Main authenticated layout
│   ├── auth.blade.php — Auth pages layout
│   └── guest.blade.php — Guest pages layout
│
├── profile/
│   └── edit.blade.php — User profile edit form
│
├── payments/
│   └── create.blade.php — Payment proof upload form (alternative location)
│
└── cash-flows/
    └── index.blade.php — Arus kas ledger view (alternative location)
```

### **Database Migrations** (`database/migrations/`)

| Migration | Purpose |
|-----------|---------|
| `0001_01_01_000000_create_users_table.php` | User accounts with roles |
| `0001_01_01_000001_create_cache_table.php` | Laravel cache storage |
| `0001_01_01_000002_create_jobs_table.php` | Job queue (optional) |
| `2026_05_10_000001_create_expense_types_table.php` | Budget categories |
| `2026_05_10_000002_create_settings_table.php` | System settings key-value |
| `2026_05_10_000003_create_rabs_table.php` | Main RAB documents |
| `2026_05_10_000004_create_operational_expense_items_table.php` | Operasional detail |
| `2026_05_10_000005_create_petty_cash_items_table.php` | Petty Cash detail |
| `2026_05_10_000006_create_salary_expense_items_table.php` | Salary detail |
| `2026_05_10_000007_create_monthly_expense_items_table.php` | Monthly billing detail |
| `2026_05_10_000008_create_rab_approvals_table.php` | Approval history |
| `2026_05_10_000009_create_rab_payments_table.php` | Payment records |
| `2026_05_10_000010_create_cash_flows_table.php` | Arus kas ledger |
| `2026_05_10_000011_create_report_exports_table.php` | Export history |
| `2026_05_10_000012_create_audit_logs_table.php` | Activity audit trail |
| `2026_05_15_000001_restructure_expense_items_tables.php` | Restructured item tables |
| `2026_05_15_041501_add_avatar_to_users_table.php` | Added avatar column |
| `2026_05_16_115332_add_letter_number_to_rabs_table.php` | (Rolled back) |
| `2026_05_16_120848_drop_letter_number_from_rabs_table.php` | (Cleanup) |
| `2026_05_16_124043_add_proof_and_rab_to_cash_flows_table.php` | Added proof storage |
| `2026_05_16_130000_create_rab_discussions_table.php` | Discussion notes table |
| `2026_05_16_130001_create_rab_notifications_table.php` | Notification table |
| `2026_05_17_050727_restructure_operational_expense_items_table.php` | Operational table restructure |
| `2026_05_25_081000_rename_manajer_operasional_to_manajer_keuangan.php` | Rename role (schema-safe) |
| `2026_06_05_045003_add_phone_number_to_users_table.php` | Phone for WhatsApp |
| `2026_06_07_130037_create_pnbp_expense_items_table.php` | PNBP payment type |

### **Routes** (`routes/web.php`)

```php
// Middleware: auth, EnsureUserIsActive

// SHARED (All authenticated users)
POST    /profile                              — Update profile
POST    /rab/{rab}/discussions                — Post discussion comment
GET     /rab-notifications/{notification}    — Open notification
GET     /dashboard/chart-data                 — AJAX chart data
GET     /file/{path}                          — File download/view

// ADMIN KEUANGAN
GET     /admin/dashboard                      — Dashboard
GET/POST/PUT/DELETE /rab                     — Full CRUD on RABs
POST    /rab/{rab}/submit                    — Submit for approval
GET/POST /rab/{rab}/payment/create,store    — Payment upload

// MANAJER KEUANGAN
GET     /manajer/dashboard                    — Dashboard
GET     /manajer/rab                          — Approval queue
GET     /manajer/rab/{rab}                   — Detail view
POST    /rab/{rab}/approve-manager           — Approve RAB
POST    /rab/{rab}/reject-manager            — Reject RAB
GET/POST /manajer/cash-flow                  — Arus kas management
GET     /report                               — Report page
GET     /report/export-pdf                    — Export PDF

// DIREKTUR
GET     /direktur/dashboard                   — Dashboard
GET     /direktur/rab                         — Approval queue
GET     /direktur/rab/{rab}                  — Detail view
POST    /rab/{rab}/approve-director          — Approve RAB
POST    /rab/{rab}/reject-director           — Reject RAB
GET/POST/PUT/DELETE /direktur/users         — User management
PATCH   /direktur/users/{id}/toggle          — Toggle user active
GET     /direktur/cash-flow                   — Arus kas read-only
GET     /report                               — Report page

// AUTH
GET     /login                                — Login form
POST    /login                                — Login post
GET/POST /forgot-password                    — Password reset request
GET     /reset-password/{token}               — Reset form
POST    /reset-password                       — Reset submit
POST    /logout                               — Logout
```

---

## CRITICAL BUSINESS LOGIC

### **1. RAB Number Generation**
```php
// Format: XXX/RAB/SBK/ROMAN_MONTH/YEAR
// Example: 001/RAB/SBK/V/2026 (May 2026)

function generateNumber(): string {
    $year = now()->year;
    $month = now()->month;
    $romanMonth = ['I', 'II', 'III', 'IV', 'V', 'VI', 
                   'VII', 'VIII', 'IX', 'X', 'XI', 'XII'][$month - 1];
    
    $count = Rab::whereYear('created_at', $year)
                 ->whereMonth('created_at', $month)
                 ->count() + 1;
    
    return sprintf('%03d/RAB/SBK/%s/%d', $count, $romanMonth, $year);
}
```

### **2. Race Condition Prevention (Database Locking)**
```php
DB::beginTransaction();
$rab = Rab::where('id', $rab->id)->lockForUpdate()->first();

// Check current state before proceeding
if ($rab->status !== RabStatus::DIAJUKAN) {
    DB::rollBack();
    return back()->with('error', 'Already processed by another manager');
}

// Only if validation passes, proceed with update
$rab->update(['status' => RabStatus::DISETUJUI_MANAJER]);
// ... more operations ...
DB::commit();
```

**Impact:** If Manager A and Manager B both approve simultaneously:
- Manager A's transaction locks the row first
- Manager A commits with status = DISETUJUI_MANAJER
- Manager B's transaction acquires lock but finds status ≠ DIAJUKAN
- Manager B's transaction rolls back with info message

### **3. Dynamic Expense Table Selection**
```javascript
// JavaScript listens to expense type select change
document.getElementById('expense_type').addEventListener('change', function() {
    const selectedType = this.value; // 'operasional', 'petty_cash', etc.
    
    // Hide all forms
    document.querySelectorAll('[data-form-type]').forEach(form => {
        form.style.display = 'none';
    });
    
    // Show selected form
    document.querySelector(`[data-form-type="${selectedType}"]`).style.display = 'block';
    
    // Clear invalid hidden inputs (prevents silent form rejection)
    document.querySelectorAll('[data-form-type][style*="display: none"] input[required]')
            .forEach(input => input.removeAttribute('required'));
});
```

### **4. Real-Time Total Calculation**
```javascript
function calculateTotal() {
    let grandTotal = 0;
    
    document.querySelectorAll('input[data-volume], input[data-unit-price]').forEach(row => {
        const volume = parseFloat(row.closest('tr').querySelector('[data-volume]').value) || 0;
        const unitPrice = parseFloat(row.closest('tr').querySelector('[data-unit-price]').value) || 0;
        const itemTotal = volume * unitPrice;
        
        row.closest('tr').querySelector('[data-item-total]').value = itemTotal.toLocaleString('id-ID');
        grandTotal += itemTotal;
    });
    
    document.getElementById('total_amount').value = grandTotal.toLocaleString('id-ID');
}

// Trigger on every input change
document.addEventListener('input', calculateTotal);
```

### **5. Currency Normalization (Rp Input)**
```php
// In controller, before validation
function normalizeMoney($value): string {
    // Remove 'Rp' prefix, spaces, and dots (thousand separators)
    return str_replace(['Rp', '.', ' '], '', $value);
}

// Example: "Rp 25.000.000" → "25000000"
$request->merge([
    'paid_amount' => $this->normalizeMoney($request->paid_amount),
]);
```

### **6. Session Keep-Alive (Prevent 419 Page Expired)**
```javascript
// Every 5 minutes, ping the app to keep session alive
setInterval(() => {
    fetch('/', { method: 'GET' });
}, 5 * 60 * 1000);
```

### **7. Double-Submit Prevention**
```javascript
// On form submission
form.addEventListener('submit', function(e) {
    const submitButton = form.querySelector('button[type="submit"]');
    
    if (submitButton.disabled) {
        e.preventDefault();
        return false;
    }
    
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="spinner"></i> Memproses...';
});
```

### **8. Arus Kas Auto-Recording**
```php
// In PaymentController::store()
CashFlow::create([
    'rab_id' => $rab->id,
    'payment_id' => $payment->id,
    'transaction_date' => $request->payment_date,
    'type' => CashFlowType::DANA_KELUAR,
    'description' => "Pembayaran kebutuhan RAB {$rab->rab_number}",
    'debit' => 0,
    'credit' => $request->paid_amount,  // Amount spent
    'balance' => $lastBalance - $request->paid_amount,  // Running balance
    'proof_file_path' => $proofPath,
    'created_by' => auth()->id(),
]);

// Update RAB status to SELESAI
$rab->update([
    'status' => RabStatus::SELESAI,
    'completed_at' => now(),
]);
```

### **9. Insufficient Balance Check**
```php
// Before recording payment
$lastBalance = CashFlow::orderBy('id', 'desc')->value('balance') ?? 0;

if ((float)$request->paid_amount > (float)$lastBalance) {
    return back()->withInput()->withErrors([
        'paid_amount' => 'Saldo tidak mencukupi. Saldo tersedia: Rp ' . 
                       number_format($lastBalance, 0, ',', '.')
    ]);
}
```

### **10. File Size & Type Validation**
```php
// Frontend validation (immediate feedback)
<input type="file" id="proof_file" accept=".jpg,.jpeg,.png,.pdf" 
       onchange="validateFileSize(this, 100)">

// Backend validation (security)
$request->validate([
    'proof_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:1024', // 1024KB = ~100KB after size normalization
]);
```

---

## KEY ARCHITECTURAL DECISIONS

### **Why Layered Architecture?**
- **Separation of Concerns:** Controllers handle HTTP only; Services handle business logic; Repositories handle data access
- **Testability:** Each layer can be unit tested independently
- **Maintainability:** Changes to database schema don't affect controllers if repository layer absorbs them
- **Scalability:** Easy to add caching layer, queue jobs, or microservices later

### **Why Pool System for Approval?**
- **Operational Efficiency:** No single manager becomes a bottleneck
- **Audit Trail:** `RabApproval` table records exactly who approved and when
- **Race Condition Safe:** Database transactions with state checking prevent conflicts
- **Future-Proof:** Can easily add role-division filtering later

### **Why 5 Separate Expense Tables?**
- **Database Normalization:** No NULL columns for unused fields
- **Custom Validation:** Each table enforces specific field requirements
- **Frontend Clarity:** Different UI forms per category improve UX
- **Query Performance:** Smaller tables = faster queries

### **Why Immutable Arus Kas & Payment Records?**
- **Audit Compliance:** Financial records cannot be modified post-creation
- **Legal Protection:** Tamper-proof ledger for compliance audits
- **Data Integrity:** Running balance calculations stay consistent

### **Why WhatsApp Integration?**
- **Faster Communication:** Notifications reach managers/directors immediately
- **Formatted Messages:** Auto-generated messages reduce typos
- **Direct Action:** Links in messages take directly to approval screens
- **Audit Trail:** Message sends are logged in system

---

## SUMMARY TABLE

| Aspect | Implementation |
|--------|-----------------|
| **Framework** | Laravel 12 (PHP 8.3+) |
| **Frontend** | Laravel Blade + Tailwind CSS + Vanilla JS |
| **Database** | MySQL with InnoDB, utf8mb4_unicode_ci |
| **Authentication** | Laravel Auth with email/password |
| **Authorization** | RBAC with 3 roles, middleware protection |
| **Key Feature 1** | Multi-stage approval with Pool System + DB transaction locking |
| **Key Feature 2** | Dynamic 5-category expense tables with real-time calculations |
| **Key Feature 3** | Auto cash flow integration on payment |
| **Key Feature 4** | WhatsApp notifications via Fonnte API |
| **Key Feature 5** | PDF reports with professional formatting |
| **Key Feature 6** | Discussion panel for collaboration & audit |
| **Key Feature 7** | Immutable arus kas for audit compliance |
| **Key Feature 8** | Session keep-alive & double-submit prevention |
| **Audit Trail** | AuditLog table + soft deletes + discussions |
| **Error Handling** | Try-catch with transaction rollback, user-friendly messages |
| **File Upload** | Max 100KB enforced frontend & backend |
| **File Storage** | Laravel Storage disk (public/storage/app/public/) |
| **Key Validation** | RAB status state machine, required field checks, currency normalization |

---

## PREPARATION TIPS FOR DEMONSTRATION/EXAM

### **Key Points to Emphasize:**
1. **Pool System Resilience:** Explain how database locking prevents race conditions
2. **Audit Immutability:** Explain why arus kas cannot be edited after creation
3. **User Role Isolation:** Show how each role sees different dashboards and features
4. **Business Process Alignment:** Show how system mirrors real PT SBK workflow
5. **Data Integrity:** Show transaction handling and validation layers

### **Demo Script:**
1. **Login as Admin** → Create new RAB → Submit to Manager
2. **Login as Manager** → Approve RAB (show pool queue logic) → Notifies Director
3. **Login as Director** → Final approval → Notifies Admin
4. **Login as Admin** → Upload payment proof → Auto-creates arus kas entry
5. **Login as Manajer** → View arus kas → See auto-recorded payment entry
6. **Show Reports** → Export PDF with formatting
7. **Show Audit Trail** → View AuditLog and Discussion panel

### **Questions to Prepare For:**
- "How do you prevent two managers from approving the same RAB simultaneously?"
- "Why 5 separate tables for expenses instead of one flexible table?"
- "How does the cash flow update automatically?"
- "What happens if a manager rejects a RAB that's already been approved by another manager?"
- "How are user roles protected at the code level?"
- "What validates that only Admin can create RABs?"
- "How is payment immutability enforced?"

---

**Document Generated:** June 10, 2026
**Application:** RAB (Rencana Anggaran Biaya)
**Organization:** PT Sertifikasi Bermutu Ketenagalistrikan (PT SBK)
