@extends('admin.layout.layout')

@section('admin-title')
    {{ $title }}
@endsection

@section('admin-content')
<div class="orders-section" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1 style="font-size: 1.75rem; font-weight: 700; margin: 0 0 0.5rem 0; color: #333;">{{ $title }}</h1>
            <p class="page-subtitle" style="font-size: 0.95rem; color: #666; margin: 0;">{{ $subtitle }}</p>
        </div>
        <a href="{{ route('admin.categories.create') }}" style="background-color: #2196F3; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 600; text-decoration: none; transition: background 0.2s;">
            + Create Category
        </a>
    </div>

    <!-- Alerts -->
    @if ($message = Session::get('success'))
        <div class="alert alert-success" style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 6px;">
            <strong>Success!</strong> {{ $message }}
        </div>
    @endif

    <!-- Table -->
    <div class="table-wrapper" style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
        @if ($categories->count())
            <div style="overflow-x: auto;">
                <table class="orders-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="background-color: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 0.9rem;">ID</th>
                            <th style="background-color: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 0.9rem;">Category Name</th>
                            <th style="background-color: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 0.9rem;">Total Posts</th>
                            <th style="background-color: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 0.9rem;">Status</th>
                            <th style="background-color: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 0.9rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr style="border-bottom: 1px solid #e0e0e0;">
                                <td style="padding: 1rem; color: #555; font-size: 0.9rem;">{{ $category->category_id }}</td>
                                <td style="padding: 1rem; font-weight: 600; color: #111; font-size: 0.95rem;">{{ $category->name }}</td>
                                <td style="padding: 1rem; font-size: 0.9rem;">
                                    <span style="background-color: #f0f0f0; color: #555; padding: 0.3rem 0.8rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        {{ $category->posts_count }} posts
                                    </span>
                                </td>
                                <td style="padding: 1rem; font-size: 0.9rem;">
                                    @if($category->active)
                                        <span style="background-color: #e8f5e9; color: #2e7d32; padding: 0.3rem 0.8rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">Active</span>
                                    @else
                                        <span style="background-color: #f5f5f5; color: #757575; padding: 0.3rem 0.8rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">Disabled</span>
                                    @endif
                                </td>
                                <td style="padding: 1rem; display: flex; gap: 8px;">
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this category? All related posts will be permanently deleted.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background-color: #dc3545; color: white; border: none; padding: 0.5rem 1rem; font-size: 0.8rem; font-weight: 600; border-radius: 6px; cursor: pointer; transition: background 0.2s;">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($categories->hasPages())
                <div style="padding: 1.5rem; border-top: 1px solid #e0e0e0; display: flex; justify-content: center;">
                    {{ $categories->links() }}
                </div>
            @endif
        @else
            <div style="padding: 4rem 2rem; text-align: center; color: #666;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🏷️</div>
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.25rem; color: #333;">No Categories Found</h3>
                <p style="margin: 0 0 1.5rem 0; font-size: 0.95rem;">There are no emotion categories yet.</p>
                <a href="{{ route('admin.categories.create') }}" style="background-color: #2196F3; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; text-decoration: none; font-weight: 600;">
                    Create First Category
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
