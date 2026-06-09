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
        <div class="admin-detail-header">
            <div>
                <h2 class="admin-title">{{ $title }}</h2>
                <p class="admin-subtitle">{{ $subtitle }}</p>
            </div>
            <div class="detail-actions">
                <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-primary">
                    Edit
                </a>
                <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary">
                    Back
                </a>
            </div>
        </div>

        <!-- Detail Container -->
        <div class="admin-detail-container">
            <!-- Department Information -->
            <div class="detail-card">
                <h3 class="detail-card-title">Department Information</h3>
                <div class="detail-content">
                    <div class="detail-row">
                        <div class="detail-label">Department Name</div>
                        <div class="detail-value">{{ $department->department_name }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Created At</div>
                        <div class="detail-value">{{ $department->created_at ? $department->created_at->format('d M Y H:i') : 'N/A' }}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Updated At</div>
                        <div class="detail-value">{{ $department->updated_at ? $department->updated_at->format('d M Y H:i') : 'N/A' }}</div>
                    </div>
                </div>
            </div>

            <!-- Associated Artworks -->
            <div class="detail-card">
                <h3 class="detail-card-title">Associated Artworks ({{ $department->artWorks->count() }})</h3>
                <div class="detail-content">
                    @if ($department->artWorks->count())
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Accession Number</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($department->artWorks as $artwork)
                                    <tr>
                                        <td>{{ $artwork->title }}</td>
                                        <td>{{ $artwork->accession_number }}</td>
                                        <td>
                                            <a href="{{ route('admin.artworks.show', $artwork) }}" class="btn-sm btn-info">View</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">No artworks associated with this department</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .admin-detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .detail-actions {
            display: flex;
            gap: 10px;
        }

        .admin-detail-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .detail-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .detail-card-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }

        .detail-content {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 20px;
            padding: 10px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #666;
            font-size: 14px;
        }

        .detail-value {
            color: #333;
            word-break: break-word;
        }

        .text-muted {
            color: #999;
            font-style: italic;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .admin-table thead {
            background-color: #f9f9f9;
        }

        .admin-table th {
            padding: 10px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }

        .admin-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-info {
            background-color: #17a2b8;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }
    </style>
@endsection
