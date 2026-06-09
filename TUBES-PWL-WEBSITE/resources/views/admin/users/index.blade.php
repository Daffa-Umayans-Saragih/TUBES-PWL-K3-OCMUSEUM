
@extends('admin.layout.layout')

@section('admin-title')
    User Management
@endsection

@section('admin-content')
<div class="admin-page-section">
    <!-- Session Notifications -->
    @if(session('success'))
        <div style="background: #e8f5e9; border: 1px solid #c8e6c9; color: #2e7d32; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500;">
            ✓ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: #ffebee; border: 1px solid #ffcdd2; color: #c62828; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500;">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <div class="page-header">
        <h1>User Management</h1>
        <p class="page-subtitle">Manage system users and permissions</p>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats-grid">
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Total Users',
            'value' => $totalUsers ?? 0,
            'icon' => '👥',
            'trend' => 'registered',
            'color' => 'primary'
        ])
        
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Admins',
            'value' => $adminCount ?? 0,
            'icon' => '🔐',
            'trend' => 'active',
            'color' => 'warning'
        ])
        
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Active Today',
            'value' => $activeToday ?? 0,
            'icon' => '✓',
            'trend' => 'online',
            'color' => 'success'
        ])
    </div>

    <!-- Search & Filter Bar -->
    <div class="filter-bar" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; background: white; padding: 1.25rem; border-radius: 8px; border: 1px solid #e0e0e0; align-items: center; justify-content: space-between; flex-wrap: wrap; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
        <form action="{{ route('admin.users.index') }}" method="GET" style="display: flex; gap: 1rem; flex: 1; flex-wrap: wrap; margin: 0; width: 100%;">
            <div style="flex: 1; min-width: 250px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or role..." style="width: 100%; padding: 0.6rem 1rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem;">
            </div>
            
            <div style="min-width: 150px;">
                <select name="role" style="width: 100%; padding: 0.6rem 1rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: white; cursor: pointer;">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                    <option value="guest" {{ request('role') === 'guest' ? 'selected' : '' }}>Guest</option>
                </select>
            </div>

            <div style="min-width: 150px;">
                <select name="source" style="width: 100%; padding: 0.6rem 1rem; border: 1px solid #ccc; border-radius: 6px; font-size: 0.9rem; background: white; cursor: pointer;">
                    <option value="">All Sources</option>
                    <option value="Users" {{ request('source') === 'Users' ? 'selected' : '' }}>Users Table</option>
                    <option value="Guests" {{ request('source') === 'Guests' ? 'selected' : '' }}>Guests Table</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="action-btn" style="background: #2196F3; color: white; border-color: #2196F3; padding: 0.6rem 1.2rem;">Apply Filters</button>
                @if(request()->has('search') || request()->has('role') || request()->has('source'))
                    <a href="{{ route('admin.users.index') }}" class="action-btn" style="text-decoration: none; padding: 0.6rem 1.2rem; display: flex; align-items: center; justify-content: center; background: #eee; border-color: #ccc; color: #333;">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <section class="table-section">
        <h2 class="section-title">Users Directory</h2>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $user)
                        <tr>
                            <td><strong>{{ $user['id'] ?? 'N/A' }}</strong></td>
                            <td>{{ $user['name'] ?? 'N/A' }}</td>
                            <td>{{ $user['email'] ?? 'N/A' }}</td>
                            <td>
                                <span class="badge" style="background: {{ strtolower($user['role']) === 'admin' ? '#ffebee' : (strtolower($user['role']) === 'user' ? '#e3f2fd' : '#f3e5f5') }}; color: {{ strtolower($user['role']) === 'admin' ? '#c62828' : (strtolower($user['role']) === 'user' ? '#0d47a1' : '#4a148c') }}; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                    {{ ucfirst($user['role'] ?? 'user') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background: {{ $user['source'] === 'Users' ? '#e8f5e9' : '#fff3e0' }}; color: {{ $user['source'] === 'Users' ? '#2e7d32' : '#e65100' }}; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: 600;">
                                    {{ $user['source'] ?? 'Users' }}
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $user['status'] ?? 'active' }}">
                                    {{ ucfirst($user['status'] ?? 'active') }}
                                </span>
                            </td>
                            <td>{{ $user['created_at'] ?? 'N/A' }}</td>
                            <td class="actions" style="display: flex; gap: 0.5rem; align-items: center;">
                                @if($user['status'] === 'deleted')
                                    @if($user['uses_soft_deletes'])
                                        @if(auth()->user()->isSuperAdmin())
                                        <form action="{{ route('admin.users.restore', $user['id']) }}?source={{ $user['source'] }}" method="POST" style="display: inline-block; margin: 0;">
                                            @csrf
                                            <button type="submit" class="action-btn" style="border-color: #4cAF50; color: #4cAF50;">Restore</button>
                                        </form>
                                        @endif
                                    @endif
                                @else
                                    <a href="{{ route('admin.users.edit', $user['id']) }}?source={{ $user['source'] }}" class="action-btn" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center;">
                                        {{ auth()->user()->isSuperAdmin() ? 'Edit' : 'View' }}
                                    </a>
                                    @if(auth()->user()->isSuperAdmin())
                                    <button type="button" class="action-btn action-danger" onclick="openDeleteModal('{{ $user['id'] }}', '{{ $user['source'] }}')">Delete</button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem; color: #666;">No users or guests matched the active filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->hasPages())
            <div class="pagination-wrapper" style="margin-top: 1.5rem; display: flex; justify-content: center;">
                {{ $users->links() }}
            </div>
        @endif
    </section>
</div>

<style>
.admin-page-section {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.page-subtitle {
    font-size: 0.95rem;
    color: #666;
    margin: 0;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.quick-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.table-section {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
}

.table-wrapper {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background-color: #f5f5f5;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e0e0e0;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #e0e0e0;
}

.data-table tbody tr:hover {
    background-color: #f9f9f9;
}

.status-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-active {
    background-color: #d4edda;
    color: #155724;
}

.status-inactive {
    background-color: #f8d7da;
    color: #721c24;
}

.status-deleted {
    background-color: #ffebee;
    color: #c62828;
}

.actions {
    display: flex;
    gap: 0.5rem;
}

.action-btn {
    padding: 0.4rem 0.8rem;
    border: 1px solid #ddd;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.8rem;
    transition: all 0.3s ease;
}

.action-btn:hover {
    border-color: #2196F3;
    color: #2196F3;
}

.action-danger {
    color: #f44336;
}

.action-danger:hover {
    border-color: #f44336;
}
</style>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 9999; padding: 1rem;">
    <div style="background: white; border-radius: 12px; max-width: 500px; width: 100%; box-shadow: 0 10px 25px rgba(0,0,0,0.15); overflow: hidden; animation: modalFadeIn 0.2s ease;">
        <div style="padding: 1.5rem; border-bottom: 1px solid #eaeaea; display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-size: 1.5rem;">⚠️</span>
            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #111;">Confirm Deletion</h3>
        </div>
        <div style="padding: 1.5rem; color: #444; font-size: 0.95rem; line-height: 1.5;">
            Are you sure you want to delete this user? This action will permanently remove the record or soft-delete it depending on database support.
        </div>
        <div style="padding: 1.5rem; border-top: 1px solid #eaeaea; display: flex; gap: 0.75rem; justify-content: flex-end; background: #fafafa;">
            <button type="button" class="action-btn" onclick="closeDeleteModal()" style="padding: 0.5rem 1.25rem;">Cancel</button>
            <form id="deleteForm" action="" method="POST" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="action-btn action-danger" style="background: #f44336; color: white; border-color: #f44336; padding: 0.5rem 1.25rem;">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function openDeleteModal(id, source) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        if (modal && form) {
            form.action = `/admin/users/${id}?source=${source}`;
            modal.style.display = 'flex';
        }
    }
    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
</script>

<style>
    @keyframes modalFadeIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>
@endsection
