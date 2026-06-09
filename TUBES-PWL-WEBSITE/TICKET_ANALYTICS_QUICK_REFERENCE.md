# Ticket Sales Analytics Dashboard - Quick Reference

## 🚀 Quick Start

### Access Dashboard
- URL: `/admin/ticket-analytics`
- Route Name: `admin.ticket-analytics.index`
- Middleware: `auth`, `admin`

### Route List
```bash
php artisan route:list --name=ticket-analytics
```

### Build Assets
```bash
npm run build
```

---

## 📁 File Manifest

### Backend Files
| File | Purpose | Lines |
|------|---------|-------|
| `app/Http/Controllers/Admin/TicketAnalyticsController.php` | Main controller | 340 |
| `routes/web.php` | Route definitions | 20 lines added |

### Frontend Files
| File | Purpose | Lines |
|------|---------|-------|
| `resources/views/admin/ticket-analytics/index.blade.php` | Main view | 380 |
| `resources/views/admin/ticket-analytics/components/filter-bar.blade.php` | Filter component | 18 |
| `resources/views/admin/ticket-analytics/components/stat-card.blade.php` | Stat card component | 12 |
| `resources/css/admin/ticket-analytics/index.css` | Styling | 650 |
| `resources/js/admin/ticket-analytics/index.js` | Charts & interactions | 350 |

### Configuration Files
| File | Purpose |
|------|---------|
| `resources/views/admin/layout/layout.blade.php` | Sidebar updated |
| `vite.config.js` | Asset pipeline configured |

---

## 🗄️ Database Tables Used

```
payments
  ├── payment_status (Pending, Paid, Failed, Refunded)
  ├── amount
  └── order_id → orders

orders
  ├── status (pending, completed, cancelled, failed)
  ├── total_amount
  ├── user_id (XOR)
  ├── guest_id (XOR)
  └── created_at

tickets
  ├── status (valid, used, cancelled)
  ├── ticket_availability_id → ticket_availability
  └── order_id → orders

ticket_availability
  ├── ticket_type_id → ticket_types
  └── visit_schedule_id → visit_schedules

visit_schedules
  ├── visit_date
  ├── location_id → locations
  └── capacity_limit

ticket_types
  ├── name
  └── price
```

---

## 📊 Analytics Data Sources

### Overview Analytics
| Metric | Source | Filter |
|--------|--------|--------|
| Revenue Today | payments (Paid, today) | - |
| Revenue This Month | payments (Paid, this month) | - |
| Tickets Sold Today | tickets (!=cancelled, today) | - |
| Total Visitors | orders + tickets (distinct user/guest) | date range |
| Pending Payments | payments (Pending) | date range |
| Conversion Rate | completed / total orders | date range |
| Active Sessions | visit_schedules (visit_date >= today) | - |
| Sold Out Sessions | visit_schedules (visit_date >= today) | - |

### Chart Data
| Chart | Data Source | Period |
|-------|-------------|--------|
| Revenue Trend | payments (Paid) | 30 days daily |
| Monthly Revenue | payments (Paid) | 12 months |
| Payment Status | payments grouped | date range |
| Ticket Sales | tickets (!=cancelled) | 7 days |
| Distribution | tickets grouped by type | date range |
| Validation | tickets grouped by status | date range |

---

## 🎯 Key Controller Methods

### `TicketAnalyticsController::index()`
**Purpose**: Main dashboard view
**Returns**: View with 25+ variables
**Parameters**: 
- `start_date` - Format: Y-m-d (default: 30 days ago)
- `end_date` - Format: Y-m-d (default: today)

### `TicketAnalyticsController::getAnalyticsData()`
**Purpose**: AJAX API for data refresh
**Returns**: JSON response
**Endpoint**: `/admin/ticket-analytics/data`

---

## 💾 Session Storage

### View Data Variables
```php
// Overview
$totalRevenueToday
$totalRevenueMonth
$ticketsSoldToday
$totalVisitors
$pendingPayments
$conversionRate
$activeVisitSessions
$soldOutSessions

// Revenue
$revenueTrend          // Array of dates and amounts
$monthlyRevenue        // Array of months and amounts
$paymentStatusBreakdown // Collection with counts

// Tickets
$bestSellingTickets    // Array of ticket types with sales
$ticketSalesTrend      // Array of dates and sales
$ticketTypeDistribution // Array of types with percentages

// Capacity
$capacityOverview      // Array of schedules with details

// Visitors
$repeatVisitors        // Count of repeat visitors
$visitorTypes          // Array of registered/guest counts

// Validation
$ticketStatusBreakdown // Array of statuses with counts
$validationSuccessRate // Percentage

// Transactions
$latestTransactions    // Array of 10 orders
$latestPayments        // Array of 10 payments

// Meta
$startDate             // Y-m-d format
$endDate               // Y-m-d format
```

---

## 🎨 CSS Classes

### Grid & Layout
```css
.ticket-analytics-dashboard    /* Main container */
.analytics-section             /* Section wrapper */
.analytics-cards-grid          /* Stat cards grid */
.filter-bar-container          /* Filter bar */
```

### Cards
```css
.stat-card                     /* Stat card */
.stat-card--primary            /* Color variant */
.stat-card--success
.stat-card--info
.stat-card--warning
.stat-card--danger
```

### Charts
```css
.chart-card                    /* Chart wrapper */
.chart-header                  /* Chart title area */
.chart-body                    /* Chart area */
```

### Tables
```css
.analytics-table               /* Data table */
.table-card                    /* Table wrapper */
.badge-completed               /* Status badge */
.badge-pending
.badge-failed
```

---

## 🎬 JavaScript Functions

### Chart Initialization
```javascript
initRevenueTrendChart()           // Line chart
initMonthlyRevenueChart()         // Bar chart
initPaymentStatusChart()          // Doughnut chart
initTicketSalesTrendChart()       // Bar chart
initTicketDistributionChart()     // Pie chart
initTicketStatusChart()           // Horizontal bar
```

### Utilities
```javascript
enableSmoothScroll()              // Anchor smooth scroll
refreshAnalyticsData(from, to)    // AJAX data refresh
```

---

## 🔄 Data Flow

```
User Access → /admin/ticket-analytics
                        ↓
        TicketAnalyticsController::index()
                        ↓
        Database Queries (8 simultaneous)
                        ↓
        Data Aggregation & Transformation
                        ↓
        View Rendering (Blade template)
                        ↓
        Asset Loading (CSS + JS)
                        ↓
        Chart.js Initialization
                        ↓
        Dashboard Display
```

---

## 🧪 Testing Checklist

### Backend Tests
- [ ] Route registered: `php artisan route:list --name=ticket-analytics`
- [ ] Controller loads: `php -l app/Http/Controllers/Admin/TicketAnalyticsController.php`
- [ ] No database errors in logs
- [ ] Date filtering works correctly
- [ ] XOR constraint respected in queries

### Frontend Tests
- [ ] CSS compiles: `npm run build`
- [ ] No console errors in DevTools
- [ ] Charts render on page load
- [ ] Filter buttons work
- [ ] Responsive layout on mobile
- [ ] Tables display correctly
- [ ] All badges and icons visible

### Integration Tests
- [ ] Can navigate to dashboard from sidebar
- [ ] Active state shows correct menu item
- [ ] Browser back/forward work
- [ ] Page title appears correctly
- [ ] Breadcrumbs display properly

---

## 🐛 Troubleshooting

### Charts Not Rendering
- Check browser console for errors
- Verify Chart.js CDN is accessible
- Check canvas element IDs match
- Verify data-* attributes in HTML

### Styling Issues
- Run `npm run build` to rebuild CSS
- Clear browser cache (Ctrl+Shift+Delete)
- Check CSS file is loaded in DevTools
- Verify color variables are defined

### Data Not Showing
- Check database queries in logs
- Verify date range is correct
- Check model relationships
- Verify user has admin role

### Route Not Found
- Run `php artisan route:list`
- Check routes/web.php syntax
- Verify middleware is loaded
- Clear route cache: `php artisan route:clear`

---

## 📈 Performance Tips

### Database Optimization
- Use pagination for large datasets
- Add database indexes on frequently queried columns
- Cache aggregation results if data is historical
- Monitor slow query log

### Frontend Optimization
- Charts are lazy-loaded on viewport
- CSS is minified in production
- JavaScript is bundled efficiently
- Use browser caching for assets

### Server Optimization
- Enable gzip compression
- Use CDN for static assets
- Implement query result caching
- Monitor server resources

---

## 🔐 Security Notes

1. All routes protected by `auth` and `admin` middleware
2. User context available via `Auth::user()`
3. XOR constraint prevents data leakage between users/guests
4. Date filtering prevents injection attacks
5. JSON responses sanitized

---

## 📞 Support

### Common Issues & Solutions

**Issue**: Route not found
- **Solution**: Clear cache `php artisan route:clear`

**Issue**: 404 on dashboard
- **Solution**: Check admin middleware in `/app/Http/Kernel.php`

**Issue**: Charts not displaying
- **Solution**: Check browser console, verify Chart.js loaded

**Issue**: Data seems old
- **Solution**: Clear query cache `php artisan cache:clear`

**Issue**: CSS not updating
- **Solution**: Run `npm run build` and clear browser cache

---

## ✅ Deployment Checklist

Before going to production:

- [ ] Run `npm run build` for asset optimization
- [ ] Run `php artisan cache:config`
- [ ] Run `php artisan route:cache`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Enable HTTPS on production
- [ ] Set up automated backups
- [ ] Monitor error logs
- [ ] Test all analytics endpoints
- [ ] Verify mobile responsiveness
- [ ] Check page load performance

---

**Last Updated**: May 12, 2026
**Version**: 1.0.0 - Production Ready
**Status**: ✅ Complete
