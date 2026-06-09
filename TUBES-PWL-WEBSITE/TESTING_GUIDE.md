# Admin Panel CRUD - Quick Start & Testing Guide

## Quick Start

### 1. Start the Application

```bash
# Navigate to project directory
cd c:\xampp\htdocs\TUBES-SBD-K3-METMUSEUM\TUBES-SBD-WEBSITE

# Start PHP development server
php artisan serve
```

Server akan berjalan di: `http://localhost:8000`

### 2. Access Admin Panel

```
URL: http://localhost:8000/admin
```

**Login Required:**
- Gunakan akun admin yang sudah ada
- Jika belum ada, buat user dengan role admin

### 3. Available Admin Pages

#### Master Data Management
```
http://localhost:8000/admin/departments
http://localhost:8000/admin/object-types
http://localhost:8000/admin/classifications
http://localhost:8000/admin/locations
http://localhost:8000/admin/repositories
http://localhost:8000/admin/materials
http://localhost:8000/admin/mediums
http://localhost:8000/admin/tags
http://localhost:8000/admin/cultures
http://localhost:8000/admin/periods
http://localhost:8000/admin/dynasties
http://localhost:8000/admin/reigns
http://localhost:8000/admin/portfolios
http://localhost:8000/admin/constituents  (Artists)
```

## Testing Checklist

### 1. Test Departments CRUD

#### ✓ Create Department
```
1. Navigate to: /admin/departments
2. Click: "+ Create Department"
3. Enter department_name: "Test Department"
4. Click: "Create"
5. Expected: Redirects to list with success message
```

#### ✓ View Department
```
1. On departments list
2. Click: "View" button on any department
3. Expected: Shows department details + related artworks
```

#### ✓ Edit Department
```
1. On departments list
2. Click: "Edit" button on any department
3. Change: department_name to "Updated Test"
4. Click: "Update"
5. Expected: Shows success message, displays updated name
```

#### ✓ Delete Department
```
1. On departments list (or detail page)
2. Click: "Delete" button
3. Confirm: Click "OK" on browser confirm dialog
4. Expected: Either success message OR error if department has artworks
```

### 2. Test Locations CRUD (Multi-field form)

#### ✓ Create Location
```
1. Navigate to: /admin/locations
2. Click: "+ Create Location"
3. Enter:
   - location_name: "Gallery 1"
   - address: "123 Main Street, New York"
   - capacity_limit: 500
4. Click: "Create"
5. Expected: Location created successfully
```

#### ✓ Edit Location
```
1. On locations list
2. Click: "Edit"
3. Update any field (e.g., address)
4. Click: "Update"
5. Expected: Changes saved successfully
```

### 3. Test Tags CRUD (With URL fields)

#### ✓ Create Tag
```
1. Navigate to: /admin/tags
2. Click: "+ Create Tag"
3. Enter:
   - tag_name: "Modern Art"
   - aat_url: "https://vocab.getty.edu/aat/300033469" (optional)
   - wikidata_url: "https://www.wikidata.org/wiki/Q3476656" (optional)
4. Click: "Create"
5. Expected: Tag created with URLs
```

#### ✓ Test URL Validation
```
1. Try to create tag with invalid URL (e.g., "not-a-url")
2. Expected: Error message "The aat_url field must be a valid URL"
```

### 4. Test Constituents (Artists) CRUD (Complex M2M)

#### ✓ Create Constituent
```
1. Navigate to: /admin/constituents
2. Click: "+ Create Constituent"
3. Enter required fields:
   - display_name: "Pablo Picasso"
   - display_bio: "Spanish painter..."
   - birth_year: 1881
   - death_year: 1973
   - gender: "Male"
   - birth_place: "Málaga"
   - death_place: "Mougins"
4. Select nationalities (hold Ctrl to select multiple):
   - Spanish
5. Click: "Create"
6. Expected: Constituent created with relationships
```

#### ✓ Test Search Filter
```
1. On constituents list
2. In search box: "Picasso"
3. Click: "Search"
4. Expected: Only Picasso appears in list
```

#### ✓ Test Gender Filter
```
1. On constituents list
2. Select gender: "Female"
3. Expected: Shows only female constituents
```

#### ✓ Edit M2M Relationships
```
1. Click "Edit" on any constituent
2. In nationalities select, add or remove nations
3. Click: "Update"
4. Expected: Relationships updated, orphaned records not left behind
```

### 5. Test Validation Errors

#### ✓ Unique Field Validation
```
1. Try to create 2 departments with same name
2. On second create:
   - Enter: "Test Department"
   - Click: "Create"
3. Expected: Error "The department_name has already been taken"
```

#### ✓ Required Field Validation
```
1. Try to submit form without filling required field
2. Expected: Error highlighting field: "The field is required"
```

#### ✓ URL Validation
```
1. In tags, enter invalid URL like "not a url"
2. Click: "Create"
3. Expected: Error "The field must be a valid URL"
```

### 6. Test Pagination

```
1. Navigate to departments (if > 20 exist)
2. Expected: Show 20 items per page with pagination links
3. Click next page
4. Expected: Shows items 21-40
5. Click previous
6. Expected: Back to first page
```

### 7. Test Alerts & Messages

#### ✓ Success Message
```
1. Create/Update/Delete any record
2. Expected: Green alert shows success message
3. Message auto-dismisses or requires close
```

#### ✓ Error Message
```
1. Try to delete department with artworks
2. Expected: Red alert shows error message
3. "Cannot delete. Has associated records."
```

### 8. Test Empty States

```
1. Navigate to any table with no records
2. Expected: Shows "No items found" message
3. Shows "+ Create First" button
4. Clicking button takes to create form
```

## Common Issues & Solutions

### Issue: "404 Not Found" when accessing /admin pages
**Solution:**
- Check routes are added to `routes/web.php`
- Run: `php artisan route:list` to see all routes
- Ensure you're logged in (go to /login first)

### Issue: Form doesn't submit, page stays on form
**Solution:**
- Check browser console for JavaScript errors
- Verify CSRF token is in form: `@csrf`
- Check server error log: `storage/logs/laravel.log`

### Issue: Delete button does nothing
**Solution:**
- Check browser console for errors
- Verify form has method="POST" with @method('DELETE')
- Check user has permission to delete

### Issue: Search doesn't filter
**Solution:**
- Check constituentController has search implementation
- Verify search box name matches controller code
- Check table has searchable fields

### Issue: M2M dropdown not showing options
**Solution:**
- Check Nationality model has records
- Verify SelectController syncing relationships correctly
- Check `<select>` has `multiple` and `name="nationalities[]"`

## Database Seeding (Optional)

If you need test data, create seeders:

```bash
php artisan make:seeder DepartmentSeeder
php artisan make:seeder TagSeeder
```

Then run:
```bash
php artisan db:seed --class=DepartmentSeeder
```

## Performance Testing

### Check query count
Add to any controller:
```php
\DB::enableQueryLog();
// ... code ...
dd(\DB::getQueryLog());
```

Expected:
- Index page: ~5-10 queries
- Create/Update: ~3-5 queries
- Delete: ~2-3 queries

### Monitor performance
```bash
php artisan queue:work  # For any async tasks
php artisan tinker     # Interactive shell to test queries
```

## Security Checklist

✅ All routes protected with auth middleware
✅ All routes require admin role
✅ CSRF protection on all forms
✅ Input validation on all fields
✅ Mass assignment protection on models
✅ Soft deletes prevent permanent data loss
✅ Relationship checks before delete

## Summary

Admin panel CRUD adalah fully functional sekarang. Semua 14 master data tables dapat di-manage langsung dari browser dengan:

- ✅ Create new records
- ✅ View record details
- ✅ Edit existing records  
- ✅ Delete with safety checks
- ✅ Search & filter (for complex tables)
- ✅ Validation & error handling
- ✅ Pagination for large datasets
- ✅ Soft delete untuk data safety

**Start testing now!** 🚀

```bash
php artisan serve
# Open: http://localhost:8000/admin
```
