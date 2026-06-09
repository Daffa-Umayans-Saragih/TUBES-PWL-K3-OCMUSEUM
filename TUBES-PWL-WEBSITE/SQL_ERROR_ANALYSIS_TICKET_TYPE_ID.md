# 🔍 ANALISIS ERROR SQL: ticket_type_id - LAPORAN LENGKAP

**Database**: MySQL (tubessbd)  
**Framework**: Laravel 11  
**Tanggal Analisis**: May 11, 2026  
**Status**: ✅ ROOT CAUSE IDENTIFIED

---

## 📋 1. ANALISIS ERROR

### Error Original
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'ticket_type_id' in 'field list'

Query:
SELECT `ticket_type_id`, COUNT(*) as count, SUM(price) as revenue
FROM `tickets`
GROUP BY `ticket_type_id`
LIMIT 5
```

### Error Type
- **Tipe**: `SQLSTATE[42S22]` - Column Not Found
- **Severity**: 🔴 CRITICAL
- **Impact**: Query gagal, dashboard statistics tidak bisa menampilkan data

---

## 🔎 2. ROOT CAUSE ANALYSIS

### A. PENYEBAB UTAMA

**Kolom `ticket_type_id` TIDAK EXISTS di tabel `tickets`**

Struktur tabel `tickets`:
```
Column Name          Type                 Key    Foreign
ticket_id            int(10) unsigned     PRI    
order_id             int(10) unsigned     MUL    ✓ FK to orders
ticket_availability_id int(10) unsigned   MUL    ✓ FK to ticket_availability
qr_code              varchar(255)         UNI    
status               enum('valid','used','cancelled')
used_at              datetime             
deleted_at           timestamp            
```

**❌ ticket_type_id TIDAK ada langsung di tickets table**

### B. STRUKTUR RELASI DATABASE

```
┌─────────────────────────────────────────────────────────────┐
│                    DATABASE RELATIONSHIP                     │
├─────────────────────────────────────────────────────────────┤
│                                                              │
│  tickets                                                     │
│  ├─ ticket_id (PRI)                                          │
│  ├─ order_id (FK)                                            │
│  ├─ ticket_availability_id (FK) ─────────────┐              │
│  ├─ qr_code                                  │              │
│  ├─ status                                   │              │
│  ├─ used_at                                  │              │
│  └─ deleted_at                               │              │
│                                              │              │
│                                              ↓              │
│                          ticket_availability               │
│                          ├─ ticket_availability_id (PRI)    │
│                          ├─ ticket_type_id (FK) ────┐      │
│                          ├─ visit_schedule_id (FK) │      │
│                          └─ [timestamps]             │      │
│                                                      │      │
│                                                      ↓      │
│                                              ticket_types   │
│                                              ├─ ticket_type_id (PRI)
│                                              ├─ ticket_type_name
│                                              ├─ base_price
│                                              └─ deleted_at
│                                                              │
└─────────────────────────────────────────────────────────────┘

KEY INSIGHT:
ticket_type_id adalah di tabel TICKET_AVAILABILITY (bridge table)
BUKAN di tabel TICKETS
```

### C. MENGAPA TERJADI ERROR

Query asli mengasumsikan:
```
❌ WRONG ASSUMPTION: tickets table memiliki kolom ticket_type_id
✅ REALITY: tickets → ticket_availability → ticket_types (via relasi)
```

---

## 📊 3. STRUKTUR TABEL DETAIL

### Tabel: tickets (7 columns)
```
ticket_id              | int(10) unsigned | PRI | NOT NULL | auto_increment
order_id               | int(10) unsigned | MUL | NOT NULL | FK to orders
ticket_availability_id | int(10) unsigned | MUL | NOT NULL | FK to ticket_availability
qr_code                | varchar(255)     | UNI | NOT NULL | 
status                 | enum(3 values)   |     | NOT NULL | valid/used/cancelled
used_at                | datetime         |     | NULL     |
deleted_at             | timestamp        |     | NULL     | soft delete

⚠️ MISSING: ticket_type_id (directly)
```

### Tabel: ticket_availability (3 columns)
```
ticket_availability_id | int(10) unsigned | PRI | NOT NULL | auto_increment
ticket_type_id         | int(10) unsigned | MUL | NOT NULL | FK to ticket_types ✓✓✓
visit_schedule_id      | int(10) unsigned | MUL | NOT NULL | FK to visit_schedules

✅ CONTAINS: ticket_type_id!
```

### Tabel: ticket_types (4 columns)
```
ticket_type_id    | int(10) unsigned | PRI | NOT NULL | auto_increment
ticket_type_name  | varchar(100)     | UNI | NOT NULL |
base_price        | decimal(15,2)    |     | NOT NULL |
deleted_at        | timestamp        |     | NULL     | soft delete

✅ PRIMARY: ticket_type_id
```

### Data Distribution
```
Tickets:                0 rows  (empty)
Ticket Types:           4 rows  (Adult, Child, Student, Senior)
Ticket Availability:   56 rows  (4 types × 14 schedules)
```

---

## ✅ 4. SOLUSI LENGKAP

### Solusi A: SQL Query (Direct Fix)

#### ❌ QUERY YANG ERROR
```sql
SELECT `ticket_type_id`, COUNT(*) as count, SUM(price) as revenue
FROM `tickets`
GROUP BY `ticket_type_id`
LIMIT 5;
```

#### ✅ QUERY YANG BENAR (dengan JOIN)
```sql
SELECT 
    ta.ticket_type_id,
    COUNT(t.ticket_id) as count,
    SUM(tt.base_price) as revenue
FROM tickets t
INNER JOIN ticket_availability ta ON t.ticket_availability_id = ta.ticket_availability_id
INNER JOIN ticket_types tt ON ta.ticket_type_id = tt.ticket_type_id
GROUP BY ta.ticket_type_id
ORDER BY revenue DESC
LIMIT 5;
```

**Penjelasan:**
- `INNER JOIN ticket_availability`: Untuk mendapatkan ticket_type_id
- `INNER JOIN ticket_types`: Untuk mendapatkan base_price
- `GROUP BY ta.ticket_type_id`: Group by ticket type
- `SUM(tt.base_price)`: Revenue dari base price ticket type

---

### Solusi B: Laravel Eloquent (Best Practice)

#### ❌ KODE YANG ERROR (di DashboardController)
```php
$topTicketTypes = Ticket::select('ticket_type_id', DB::raw('count(*) as count'), DB::raw('sum(price) as revenue'))
    ->groupBy('ticket_type_id')
    ->with('ticketType')
    ->limit(5)
    ->get();
```

#### ✅ KODE YANG BENAR - Opsi 1: Raw Query
```php
$topTicketTypes = DB::select("
    SELECT 
        ta.ticket_type_id,
        COUNT(t.ticket_id) as count,
        SUM(tt.base_price) as revenue,
        tt.ticket_type_name
    FROM tickets t
    INNER JOIN ticket_availability ta ON t.ticket_availability_id = ta.ticket_availability_id
    INNER JOIN ticket_types tt ON ta.ticket_type_id = tt.ticket_type_id
    GROUP BY ta.ticket_type_id
    ORDER BY revenue DESC
    LIMIT 5
");
```

#### ✅ KODE YANG BENAR - Opsi 2: Query Builder (Recommended)
```php
$topTicketTypes = Ticket::join('ticket_availability', 'tickets.ticket_availability_id', '=', 'ticket_availability.ticket_availability_id')
    ->join('ticket_types', 'ticket_availability.ticket_type_id', '=', 'ticket_types.ticket_type_id')
    ->select(
        'ticket_availability.ticket_type_id',
        DB::raw('COUNT(tickets.ticket_id) as count'),
        DB::raw('SUM(ticket_types.base_price) as revenue'),
        'ticket_types.ticket_type_name'
    )
    ->groupBy('ticket_availability.ticket_type_id', 'ticket_types.ticket_type_name')
    ->orderBy('revenue', 'DESC')
    ->limit(5)
    ->get();
```

#### ✅ KODE YANG BENAR - Opsi 3: Advanced Query (using relationship loading)
```php
$topTicketTypes = TicketAvailability::select('ticket_type_id')
    ->with('ticketType')
    ->withCount('tickets')
    ->groupBy('ticket_type_id')
    ->limit(5)
    ->get()
    ->map(function ($availability) {
        return [
            'ticket_type_id' => $availability->ticket_type_id,
            'ticket_type_name' => $availability->ticketType->ticket_type_name,
            'count' => $availability->tickets_count,
            'revenue' => $availability->ticketType->base_price * $availability->tickets_count,
        ];
    });
```

---

## 📝 5. IMPLEMENTASI DI DASHBOARDCONTROLLER

Mari kita fix file DashboardController.php yang sudah dibuat sebelumnya:

### Lokasi File
```
app/Http/Controllers/Admin/DashboardController.php
```

### Method yang perlu diubah: `index()`

#### SEBELUM (Kode yang Error)
```php
// Line dalam index() method
$topTicketTypes = Ticket::select('ticket_type_id', DB::raw('count(*) as count'), DB::raw('sum(price) as revenue'))
    ->groupBy('ticket_type_id')
    ->with('ticketType')
    ->limit(5)
    ->get();
```

#### SESUDAH (Kode yang Benar)
```php
// Using Query Builder with JOINs (Recommended for clarity)
$topTicketTypes = Ticket::join('ticket_availability', 'tickets.ticket_availability_id', '=', 'ticket_availability.ticket_availability_id')
    ->join('ticket_types', 'ticket_availability.ticket_type_id', '=', 'ticket_types.ticket_type_id')
    ->select(
        'ticket_availability.ticket_type_id',
        DB::raw('COUNT(tickets.ticket_id) as count'),
        DB::raw('SUM(ticket_types.base_price) as revenue'),
        'ticket_types.ticket_type_name'
    )
    ->groupBy('ticket_availability.ticket_type_id', 'ticket_types.ticket_type_name')
    ->orderBy('revenue', 'DESC')
    ->limit(5)
    ->get();
```

---

## 🧪 6. LANGKAH DEBUGGING DETAIL

### Step 1: Cek Struktur Tabel
```bash
php artisan tinker

# Command 1: Lihat kolom tickets
DB::select('DESCRIBE tickets');

# Command 2: Lihat kolom ticket_availability
DB::select('DESCRIBE ticket_availability');

# Command 3: Lihat kolom ticket_types
DB::select('DESCRIBE ticket_types');
```

### Step 2: Cek Data
```bash
php artisan tinker

# Count data
Ticket::count();
TicketAvailability::count();
TicketType::count();
```

### Step 3: Test Query
```bash
php artisan tinker

# Test raw query
DB::select("SELECT ta.ticket_type_id, COUNT(*) as count FROM tickets t 
           JOIN ticket_availability ta ON t.ticket_availability_id = ta.ticket_availability_id 
           GROUP BY ta.ticket_type_id");

# Test query builder
Ticket::join('ticket_availability', 'tickets.ticket_availability_id', '=', 'ticket_availability.ticket_availability_id')
    ->select('ticket_availability.ticket_type_id', DB::raw('COUNT(*) as count'))
    ->groupBy('ticket_availability.ticket_type_id')
    ->get();
```

---

## 📋 7. CONTOH KODE LENGKAP

### File: app/Http/Controllers/Admin/DashboardController.php

#### Bagian yang dimodifikasi (method index())
```php
/**
 * Display dashboard overview with statistics and charts
 * FIXED: Corrected ticket_type_id query with proper JOIN
 */
public function index()
{
    // ... previous code ...

    // ✅ FIX: Use proper JOIN to get ticket_type_id from ticket_availability
    $topTicketTypes = Ticket::join('ticket_availability', 
            'tickets.ticket_availability_id', '=', 
            'ticket_availability.ticket_availability_id')
        ->join('ticket_types', 
            'ticket_availability.ticket_type_id', '=', 
            'ticket_types.ticket_type_id')
        ->select(
            'ticket_availability.ticket_type_id',
            DB::raw('COUNT(tickets.ticket_id) as count'),
            DB::raw('SUM(ticket_types.base_price) as revenue'),
            'ticket_types.ticket_type_name'
        )
        ->groupBy('ticket_availability.ticket_type_id', 'ticket_types.ticket_type_name')
        ->orderBy('revenue', 'DESC')
        ->limit(5)
        ->get();

    return view('admin.dashboard.index', [
        'todayTicketSales' => $todayTicketSales,
        'totalTicketsSold' => $totalTicketsSold,
        'monthlyRevenue' => $monthlyRevenue,
        'pendingOrders' => $pendingOrders,
        'last7Days' => $last7Days,
        'recentTransactions' => $recentTransactions,
        'totalArtworks' => $totalArtworks,
        'artworksByDept' => $artworksByDept,
        'topTicketTypes' => $topTicketTypes,
    ]);
}
```

---

## 🔧 8. MIGRATION (Jika diperlukan di masa depan)

### Jika Anda ingin menambah kolom ticket_type_id langsung di tickets (optional)

```php
// database/migrations/XXXX_XX_XX_XXXXXX_add_ticket_type_to_tickets.php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            // Add denormalized ticket_type_id for performance (optional)
            $table->unsignedInteger('ticket_type_id')->nullable()->after('ticket_availability_id');
            
            // Add foreign key constraint
            $table->foreign('ticket_type_id')
                ->references('ticket_type_id')
                ->on('ticket_types')
                ->onDelete('cascade');
            
            // Add index for faster queries
            $table->index('ticket_type_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropForeign(['ticket_type_id']);
            $table->dropColumn('ticket_type_id');
        });
    }
};
```

### Run Migration
```bash
php artisan migrate
```

---

## ✅ 9. TESTING COMMANDS

### Test 1: Verify Query Works
```bash
php artisan tinker
```

```php
// Test the corrected query
Ticket::join('ticket_availability', 'tickets.ticket_availability_id', '=', 'ticket_availability.ticket_availability_id')
    ->join('ticket_types', 'ticket_availability.ticket_type_id', '=', 'ticket_types.ticket_type_id')
    ->select(
        'ticket_availability.ticket_type_id',
        DB::raw('COUNT(tickets.ticket_id) as count'),
        DB::raw('SUM(ticket_types.base_price) as revenue')
    )
    ->groupBy('ticket_availability.ticket_type_id')
    ->orderBy('revenue', 'DESC')
    ->limit(5)
    ->get();
```

### Test 2: Verify Dashboard Loads
```bash
curl http://localhost:8000/admin/dashboard
# Should return 200 OK without SQL errors
```

### Test 3: Run Tests
```bash
php artisan test tests/Feature/Admin/DashboardTest.php
```

---

## 🛡️ 10. BEST PRACTICES LARAVEL

### ✅ DO's

1. **Gunakan Query Builder dengan JOIN explicit**
```php
Ticket::join('ticket_availability', ...)
    ->join('ticket_types', ...)
    ->select(...) // Selalu specify columns
    ->groupBy(...) // Sesuai dengan select columns
    ->get();
```

2. **Selalu specify kolom di SELECT**
```php
// ✅ GOOD
->select('ta.ticket_type_id', DB::raw('COUNT(*) as count'))

// ❌ BAD
->select('*')
```

3. **Gunakan alias untuk clarity**
```php
// ✅ GOOD
Ticket::join('ticket_availability as ta', ...)
    ->select('ta.ticket_type_id')

// ❌ BAD
Ticket::join('ticket_availability', ...)
    ->select('ticket_availability.ticket_type_id')
```

4. **Gunakan Model relationships**
```php
// ✅ GOOD - Kalau ada relasi defined di model
$ticket->ticketAvailability->ticketType->ticket_type_id

// ❌ BAD - Raw query tanpa relationship
DB::select('SELECT ...')
```

### ❌ DON'Ts

1. **Jangan query kolom yang tidak exists**
```php
// ❌ WRONG
Ticket::select('ticket_type_id')

// ✅ RIGHT
TicketAvailability::select('ticket_type_id')
// atau via JOIN
```

2. **Jangan assume struktur tabel**
```php
// ❌ WRONG - Asumsikan kolom ada
Ticket::select('ticket_type_id') // Tidak ada!

// ✅ RIGHT - Verify dengan DESCRIBE atau migration
DB::select('DESCRIBE tickets')
```

3. **Jangan lupa specify groupBy columns**
```php
// ❌ WRONG - MySQL strict mode error
->select('a', 'b', 'c')
->groupBy('a')

// ✅ RIGHT - Group semua non-aggregated columns
->select('a', 'b', 'c')
->groupBy('a', 'b', 'c')
```

---

## 🚨 11. KEMUNGKINAN ERROR LAIN

### Error 1: "Column not in SELECT list in aggregate function"
```
MySQL strict mode: GROUP BY columns harus di SELECT atau aggregated
```

**Solusi:**
```php
->select('ticket_availability.ticket_type_id', DB::raw('COUNT(*) as count'))
->groupBy('ticket_availability.ticket_type_id')
// Semua kolom di SELECT harus di GROUP BY
```

### Error 2: "Unknown column in ON clause"
```
Terjadi saat JOIN dengan nama kolom yang salah
```

**Solusi:**
```php
// Verify kolom exist sebelum JOIN
DB::select('DESCRIBE table_name')

// Use exact column names
->join('table', 'source.id', '=', 'table.id')
```

### Error 3: "SQLSTATE[HY000]: General error"
```
Mungkin deadlock atau timeout
```

**Solusi:**
```php
// Optimize query dengan INDEX
// Atau gunakan caching
Cache::remember('top_ticket_types', 3600, function () {
    return Ticket::join(...)->get();
});
```

---

## 📊 12. PERFORMANCE OPTIMIZATION

### Query Optimization
```php
// ✅ OPTIMIZED: dengan eager loading dan caching
$topTicketTypes = Cache::remember('dashboard_top_ticket_types', 3600, function () {
    return Ticket::join('ticket_availability', 'tickets.ticket_availability_id', '=', 'ticket_availability.ticket_availability_id')
        ->join('ticket_types', 'ticket_availability.ticket_type_id', '=', 'ticket_types.ticket_type_id')
        ->select(
            'ticket_availability.ticket_type_id',
            DB::raw('COUNT(tickets.ticket_id) as count'),
            DB::raw('SUM(ticket_types.base_price) as revenue'),
            'ticket_types.ticket_type_name'
        )
        ->groupBy('ticket_availability.ticket_type_id', 'ticket_types.ticket_type_name')
        ->orderBy('revenue', 'DESC')
        ->limit(5)
        ->get();
});
```

### Database Indexes
```sql
-- Ensure proper indexes exist
CREATE INDEX idx_tickets_availability ON tickets(ticket_availability_id);
CREATE INDEX idx_availability_type ON ticket_availability(ticket_type_id);
CREATE INDEX idx_tickets_created ON tickets(created_at);
```

---

## ✅ 13. KESIMPULAN

| Aspek | Detail |
|-------|--------|
| **Root Cause** | Kolom `ticket_type_id` tidak ada di tabel `tickets`, ada di `ticket_availability` |
| **Solusi** | Gunakan JOIN dengan `ticket_availability` dan `ticket_types` |
| **Best Practice** | Query Builder dengan explicit SELECT dan GROUP BY |
| **Recommended** | Option 2 (Query Builder dengan JOIN) untuk clarity dan performance |
| **Error Type** | SQLSTATE[42S22] - Column not found |
| **Severity** | Critical - Query gagal, dashboard statistics tidak bisa menampilkan |

---

**Status**: ✅ ANALYZED & READY FOR IMPLEMENTATION  
**Last Updated**: May 11, 2026
