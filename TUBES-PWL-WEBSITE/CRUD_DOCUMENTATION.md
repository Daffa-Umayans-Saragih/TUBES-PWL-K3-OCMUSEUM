# CRUD Implementation Documentation

## Overview
Semua CRUD operations untuk master data sudah diimplementasikan. Berikut adalah struktur dan pola yang digunakan.

## Tabel yang Sudah Diimplementasikan dengan CRUD Lengkap

### Tier 1 - Core Data
1. **Departments** ✅
   - Controller: `DepartmentController`
   - Routes: `admin.departments.*`
   - Views: index, form (create/edit), show

2. **Object Types** ✅
   - Controller: `ObjectTypeController`
   - Routes: `admin.object-types.*`
   - Views: index, form (create/edit), show

3. **Classifications** ✅
   - Controller: `ClassificationController`
   - Routes: `admin.classifications.*`
   - Views: index, form (create/edit), show

4. **Locations** ✅
   - Controller: `LocationController`
   - Routes: `admin.locations.*`
   - Views: index, form (create/edit), show
   - Note: Memiliki multiple fields (address, capacity_limit)

5. **Repositories** ✅
   - Controller: `RepositoryController`
   - Routes: `admin.repositories.*`
   - Views: index, form (create/edit), show

### Tier 2 - Attributes
6. **Materials** ✅
7. **Mediums** ✅
8. **Tags** ✅ (dengan URL fields)
9. **Cultures** ✅
10. **Periods** ✅
11. **Dynasties** ✅
12. **Reigns** ✅
13. **Portfolios** ✅

### Tier 3 - Complex Relationships
14. **Constituents (Artists)** ✅
    - M2M Nationalities relationship
    - Controller: `ConstituentController`
    - Routes: `admin.constituents.*`
    - Views: index (dengan search filter), form (dengan nationality multi-select), show

## Pattern untuk Simple Tables

### Controller Methods
```php
// All simple tables follow this pattern:
public function index()        // List dengan pagination
public function create()       // Show create form
public function store()        // Save to DB
public function show()         // Detail view
public function edit()         // Show edit form
public function update()       // Update in DB
public function destroy()      // Soft delete
```

### Validation Rules
```php
// Untuk field nama (unik):
'field_name' => 'required|string|max:255|unique:table_name,field_name'

// Untuk edit (exclude current record):
'field_name' => 'required|string|max:255|unique:table_name,field_name,' . $model->id . ',id'

// URL fields (optional):
'url_field' => 'nullable|url'

// Numeric fields:
'number_field' => 'nullable|integer|min:0|max:9999'
```

### View Structure
Semua views mengikuti struktur yang sama:

```
admin/{resource}/
├── index.blade.php    - List view dengan tabel
├── form.blade.php     - Create/Edit form (reusable untuk keduanya)
└── show.blade.php     - Detail view
```

## Cara Membuat Views untuk Tabel Lainnya

### 1. Index View Template
```blade
@extends('admin.admin')

@section('title', $title)
@section('page_title', $title)

@section('content')
    <div class="admin-section">
        <!-- Breadcrumbs -->
        <div class="admin-breadcrumbs">
            @foreach ($breadcrumbs as $breadcrumb)
                @if ($breadcrumb['isCurrent'] ?? false)
                    <span class="breadcrumb-current">{{ $breadcrumb['label'] }}</span>
                @else
                    <a href="{{ $breadcrumb['href'] }}" class="breadcrumb-link">{{ $breadcrumb['label'] }}</a>
                @endif
            @endforeach
        </div>

        <!-- Header -->
        <div class="admin-header">
            <div>
                <h2 class="admin-title">{{ $title }}</h2>
                <p class="admin-subtitle">{{ $subtitle }}</p>
            </div>
            <a href="{{ route('admin.{resource}.create') }}" class="btn-primary">
                + Create {ResourceName}
            </a>
        </div>

        <!-- Alerts -->
        @if ($message = Session::get('success'))
            <div class="alert alert-success">{{ $message }}</div>
        @endif

        <!-- Table -->
        <div class="admin-table-container">
            @if ($items->count())
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Field 1</th>
                            <th>Field 2</th>
                            <th>Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item->field_1 }}</td>
                                <td>{{ $item->field_2 }}</td>
                                <td><span class="badge">{{ $item->relationship_count }}</span></td>
                                <td class="admin-actions">
                                    <a href="{{ route('admin.{resource}.show', $item) }}" class="btn-sm btn-info">View</a>
                                    <a href="{{ route('admin.{resource}.edit', $item) }}" class="btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('admin.{resource}.destroy', $item) }}" method="POST" style="display:inline;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-sm btn-danger" onclick="return confirm('Sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="admin-pagination">{{ $items->links() }}</div>
            @else
                <div class="admin-empty-state">
                    <p>No items found</p>
                    <a href="{{ route('admin.{resource}.create') }}" class="btn-primary">Create First</a>
                </div>
            @endif
        </div>
    </div>
@endsection
```

### 2. Form View Pattern
- Form harus support kedua create dan edit mode (menggunakan `$isEdit` flag)
- Form harus menampilkan validation errors
- Form harus prefill data saat edit
- Gunakan `old()` helper untuk retain values saat validation gagal

### 3. Show View Pattern
- Display readonly data
- Show related data dalam card sections
- Provide edit dan back buttons

## Database Relationships untuk Soft Delete

Semua table memiliki `deleted_at` field untuk soft delete. Query otomatis mengabaikan soft-deleted records.

Jika perlu restore atau force delete, gunakan:
```php
// Restore
$model->restore();

// Force delete
$model->forceDelete();

// Include soft deleted
Model::withTrashed()->find($id);

// Only soft deleted
Model::onlyTrashed()->get();
```

## Accessing Related Data

### BelongsTo Relationships
```blade
{{ $artwork->department->department_name }}
```

### HasMany Relationships
```blade
@foreach ($department->artWorks as $artwork)
    {{ $artwork->title }}
@endforeach
```

### BelongsToMany Relationships
```blade
<!-- For Constituents -->
@foreach ($constituent->nationalities as $nationality)
    {{ $nationality->nationality_name }}
@endforeach

<!-- For ArtWorks -->
@foreach ($artwork->constituents as $constituent)
    {{ $constituent->display_name }}
@endforeach
```

## Error Handling

### In Controller
```php
if ($model->relationships()->exists()) {
    return redirect()->back()
        ->with('error', 'Cannot delete. Has associated records.');
}
```

### In View
```blade
@error('field_name')
    <span class="invalid-feedback">{{ $message }}</span>
@enderror
```

## Validation Error Display

All forms automatically display Bootstrap-style validation errors:
```blade
<div class="form-group">
    <label>Field Name</label>
    <input class="form-control @error('field_name') is-invalid @enderror" ...>
    @error('field_name')
        <span class="invalid-feedback">{{ $message }}</span>
    @enderror
</div>
```

## Pagination

All index views use Laravel's built-in pagination:
```blade
{{ $items->links() }}
```

## Search & Filter Example

See `ConstituentController@index` for example of search and filter implementation.

## Next Steps

1. ✅ Controllers created for all master tables
2. ✅ Routes configured
3. ✅ Views created for core tables (Departments, Locations, Constituents)
4. ⏳ Create views for remaining tables following the same pattern
5. ⏳ Create views for complex CRUD (ArtWorks, Orders, Tickets, Users)
6. ⏳ Test all CRUD operations
7. ⏳ Add sidebar navigation items

## Quick Command to Create Missing Views

Follow the pattern from existing views for:
- Object Types
- Classifications  
- Repositories
- Materials, Mediums, Tags
- Cultures, Periods, Dynasties, Reigns, Portfolios

All use the same pattern - change field names and model names accordingly.
