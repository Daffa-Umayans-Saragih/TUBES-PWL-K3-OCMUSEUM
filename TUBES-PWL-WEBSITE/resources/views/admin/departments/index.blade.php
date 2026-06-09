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

        <!-- Header with Create Button -->
        <div class="admin-header">
            <div>
                <h2 class="admin-title">{{ $title }}</h2>
                <p class="admin-subtitle">{{ $subtitle }}</p>
            </div>
            <a href="{{ route('admin.departments.create') }}" class="btn-primary">
                + Create Department
            </a>
        </div>

        <!-- Alert Messages -->
        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <strong>Success!</strong> {{ $message }}
            </div>
        @endif

        @if ($message = Session::get('error'))
            <div class="alert alert-danger">
                <strong>Error!</strong> {{ $message }}
            </div>
        @endif

        <!-- Table -->
        <div class="admin-table-container">
            @if ($departments->count())
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Department Name</th>
                            <th>Artworks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($departments as $department)
                            <tr>
                                <td>
                                    <strong>{{ $department->department_name }}</strong>
                                </td>
                                <td>
                                    <span class="badge">{{ $department->art_works_count }}</span>
                                </td>
                                <td class="admin-actions">
                                    <a href="{{ route('admin.departments.show', $department) }}" class="btn-sm btn-info">
                                        View
                                    </a>
                                    <a href="{{ route('admin.departments.edit', $department) }}" class="btn-sm btn-warning">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.departments.destroy', $department) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="admin-pagination">
                    {{ $departments->links() }}
                </div>
            @else
                <div class="admin-empty-state">
                    <p>No departments found</p>
                    <a href="{{ route('admin.departments.create') }}" class="btn-primary">
                        Create First Department
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .admin-table-container {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table thead {
            background-color: #f5f5f5;
            border-bottom: 2px solid #ddd;
        }

        .admin-table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
        }

        .admin-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .admin-table tbody tr:hover {
            background-color: #fafafa;
        }

        .admin-actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-info {
            background-color: #17a2b8;
            color: white;
        }

        .btn-warning {
            background-color: #ffc107;
            color: #333;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-sm:hover {
            opacity: 0.9;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            font-size: 12px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            border-left: 4px solid;
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #28a745;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }

        .admin-empty-state {
            padding: 60px 20px;
            text-align: center;
            color: #999;
        }

        .admin-pagination {
            padding: 20px;
            text-align: center;
            border-top: 1px solid #eee;
        }
    </style>
@endsection
