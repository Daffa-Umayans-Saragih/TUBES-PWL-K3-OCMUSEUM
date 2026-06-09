# Quick Start Guide - Museum Admin Dashboard

## 🚀 Getting Started in 5 Minutes

### Prerequisites
- PHP 8.1+ installed
- Composer installed
- Node.js & npm installed
- MySQL/MariaDB running
- Laravel 11 project setup complete

---

## ⚡ Quick Setup Steps

### Step 1: Install Dependencies
```bash
# In project root
composer install
npm install
```

### Step 2: Database Setup
```bash
# Create new database (if needed)
# Update .env file with database credentials

# Run migrations
php artisan migrate

# This will create the 'status' column in orders table
```

### Step 3: Build Frontend Assets
```bash
# Compile CSS and JavaScript
npm run build

# Or for development with watch:
npm run dev
```

### Step 4: Access Dashboard
```
🌐 URL: http://localhost:8000/admin/dashboard
📧 Login: Use admin credentials
✅ Dashboard should load successfully
```

---

## 📁 File Locations Quick Reference

```
📂 Controller
└── app/Http/Controllers/Admin/DashboardController.php

📂 Views
├── resources/views/admin/dashboard/index.blade.php (Overview)
├── resources/views/admin/dashboard/transactions.blade.php
└── resources/views/admin/dashboard/artworks.blade.php

📂 Styling
└── resources/css/admin/dashboard/modern.css

📂 Models
├── app/Models/Order.php (Updated)
├── app/Models/ArtWork.php
├── app/Models/OrderDetail.php
└── app/Models/Ticket.php

📂 Routes
└── routes/web.php (Updated)

📂 Database
└── database/migrations/2026_05_10_000001_add_status_to_orders_table.php
```

---

## 🔗 Available Routes

```
GET    /admin/dashboard                      # Main dashboard overview
GET    /admin/dashboard/transactions         # Transactions module
GET    /admin/dashboard/artworks             # Artworks module
GET    /admin/dashboard/export-transactions  # Export CSV

POST   /admin/artworks                       # Create artwork
POST   /admin/artworks/{id}                  # Update artwork
DELETE /admin/artworks/{id}                  # Delete artwork
```

---

## 🎯 Key Features Overview

### Dashboard Overview Tab
```
📊 Statistics Cards:
   - Today's Ticket Sales
   - Total Tickets Sold Today
   - Monthly Revenue
   - Pending Orders
   - Total Artworks
   - Collections

📈 Charts:
   - 7-Day Sales Chart
   - Trending Items Widget
   - Recent Transactions Table
```

### Transactions Tab
```
💰 Features:
   - View all ticket orders
   - Real-time statistics
   - Weekly & monthly charts
   - Search by Order ID / Customer
   - Filter by status & date
   - Export to CSV
   - Pagination support
```

### Artworks Tab
```
🎨 Features:
   - Grid / List view toggle
   - Create artwork with images
   - Edit artwork details
   - Delete artwork
   - Search artwork
   - Filter by department
   - Sort options
   - Pagination
```

---

## 🧪 Testing Your Setup

### Verify Installation
```bash
# Check Laravel is working
php artisan --version

# Check routes are registered
php artisan route:list | grep dashboard

# Check database connection
php artisan tinker
# Type: DB::connection()->getPdo()
# Should return connection object
```

### Test Dashboard Access
```
1. Go to http://localhost:8000/admin/dashboard
2. Should show login page (if not authenticated)
3. Login with admin credentials
4. Dashboard should load with data
5. Navigate tabs: Overview → Transactions → Artworks
```

### Test Features
```
✅ Click search on transactions
✅ Apply filters and see results
✅ Export transactions as CSV
✅ Click "Add Artwork" button
✅ Upload images
✅ View artworks in grid/list
✅ Delete test artwork
```

---

## 🐛 Troubleshooting

### Issue: CSS Not Loading
```bash
Solution:
1. Run: npm run build
2. Check: resources/css/admin/dashboard/modern.css exists
3. Verify: @vite() is in the Blade template
```

### Issue: Charts Not Showing
```
Solution:
1. Check browser console for errors (F12)
2. Verify Chart.js CDN is loaded
3. Check canvas element has proper ID
4. Verify data is passed to view
```

### Issue: Database Migration Error
```bash
Solution:
1. Check: orders table exists
2. Run: php artisan migrate:status
3. If failed: php artisan migrate:rollback
4. Then: php artisan migrate
```

### Issue: Images Not Uploading
```
Solution:
1. Check: storage/app/public/ directory exists
2. Verify: php artisan storage:link was run
3. Check: public/storage symlink exists
4. Verify: File permissions (755 on directories)
```

### Issue: 403 Forbidden Error
```
Solution:
1. Verify: User is logged in (auth middleware)
2. Check: User has admin role (admin middleware)
3. Verify: Routes have correct middleware
```

---

## 💡 Common Tasks

### Add New Transaction Type
```php
// In database, add to ticket_types table
// Or modify filtering logic in DashboardController

$filter = request('filter', 'all');
if ($filter !== 'all') {
    $query->where('status', $filter);  // Add custom status
}
```

### Customize Chart Colors
```css
/* In modern.css, change chart colors */
borderColor: '#3498db',           /* Change line color */
backgroundColor: 'rgba(...)',     /* Change fill color */
```

### Modify Statistics Cards
```php
// In DashboardController index() method
$todayTicketSales = Order::whereDate('order_date', today())
    ->where('status', 'completed')
    ->sum('total_amount');
// Modify the query as needed
```

### Add More Filter Options
```php
// In transactions() method, add new filter:
if (request('payment_method')) {
    $query->whereHas('payment', function($q) {
        $q->where('payment_method', request('payment_method'));
    });
}
```

---

## 📚 Learning Resources

### File Structure Understanding
1. **Controller** handles business logic
2. **Views** display data to users
3. **CSS** provides styling
4. **Models** interact with database
5. **Routes** define URL endpoints

### How Data Flows
```
User Access → Route → Controller
    ↓
Controller fetches data → Model/DB
    ↓
Controller prepares data → View
    ↓
View renders with CSS/JS → User sees dashboard
```

### Key Methods in Controller
- `index()` - Dashboard overview
- `transactions()` - List transactions
- `artworks()` - List artworks
- `storeArtwork()` - Create artwork
- `updateArtwork()` - Modify artwork
- `destroyArtwork()` - Delete artwork
- `exportTransactions()` - Download CSV

---

## 🔒 Important Security Notes

### Never
- ❌ Expose sensitive data in JSON responses
- ❌ Trust user input without validation
- ❌ Commit .env with real credentials
- ❌ Allow unauthorized file uploads
- ❌ Expose database errors to users

### Always
- ✅ Validate all input data
- ✅ Use CSRF tokens on forms
- ✅ Check authentication & authorization
- ✅ Escape output in views
- ✅ Use parameterized queries (Eloquent)
- ✅ Handle errors gracefully

---

## 📞 Support & Next Steps

### For Further Development
1. Read DASHBOARD_DOCUMENTATION.md for complete API
2. Check TESTING_QA_REPORT.md for all tested features
3. Review Laravel documentation for advanced features
4. Check code comments for implementation details

### Recommended Next Steps
- [ ] Set up CI/CD pipeline
- [ ] Add automated tests
- [ ] Implement activity logging
- [ ] Add real-time WebSocket updates
- [ ] Create mobile app API
- [ ] Add advanced analytics
- [ ] Implement caching layer
- [ ] Set up monitoring & alerts

---

## 🎓 Code Example: Adding New Dashboard Widget

```php
// In DashboardController index()
$topArtists = DB::table('constituents')
    ->select('name', DB::raw('count(*) as count'))
    ->groupBy('name')
    ->limit(5)
    ->get();

return view('admin.dashboard.index', [
    // ... existing data ...
    'topArtists' => $topArtists,  // Add new data
]);
```

```blade
<!-- In index.blade.php -->
<div class="chart-card trending-card">
    <div class="chart-header">
        <h4>Top Artists</h4>
    </div>
    <div class="chart-body">
        <div class="trending-list">
            @forelse($topArtists as $index => $artist)
                <div class="trending-item">
                    <div class="trending-rank">{{ $index + 1 }}</div>
                    <div class="trending-details">
                        <h5>{{ $artist->name }}</h5>
                    </div>
                    <div class="trending-value">
                        <span class="badge">{{ $artist->count }}</span>
                    </div>
                </div>
            @empty
                <p class="empty-state">No artists yet</p>
            @endforelse
        </div>
    </div>
</div>
```

---

## ✅ Final Checklist Before Going Live

- [ ] Database migrations applied
- [ ] Assets built (npm run build)
- [ ] .env configured correctly
- [ ] Admin user account created
- [ ] Storage symlink created
- [ ] Routes accessible
- [ ] Dashboard loads without errors
- [ ] All features tested
- [ ] Browser console clear of errors
- [ ] Images uploading correctly
- [ ] Charts displaying data
- [ ] Export CSV working
- [ ] Pagination functioning
- [ ] Search/filter working
- [ ] Mobile responsive verified

---

**Version**: 1.0.0
**Last Updated**: May 10, 2026
**Status**: ✅ Ready to Deploy
