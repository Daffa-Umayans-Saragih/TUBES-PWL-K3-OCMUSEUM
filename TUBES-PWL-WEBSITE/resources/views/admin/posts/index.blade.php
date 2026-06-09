@extends('admin.layout.layout')

@section('admin-title')
    Visitor Stories Moderation
@endsection

@section('admin-content')
<div class="orders-section" style="max-width: 1200px; margin: 0 auto; padding: 2rem 1rem;">
    <!-- Page Header -->
    <div class="page-header" style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.75rem; font-weight: 700; margin: 0 0 0.5rem 0; color: #333;">Visitor Stories Moderation</h1>
        <p class="page-subtitle" style="font-size: 0.95rem; color: #666; margin: 0;">Moderate the visitor experience feed entries</p>
    </div>

    <!-- Alerts -->
    @if ($message = Session::get('success'))
        <div class="alert alert-success" style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin-bottom: 20px; border-radius: 6px;">
            <strong>Success!</strong> {{ $message }}
        </div>
    @endif

    <!-- Filter / Search Bar -->
    <div class="table-wrapper" style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
        <form action="{{ route('admin.posts.index') }}" method="GET" style="display: flex; gap: 1rem; align-items: center; margin: 0;">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by content, visitor name, or emotion..." style="flex: 1; padding: 0.75rem 1rem; border: 1px solid #ddd; border-radius: 6px; outline: none; font-size: 0.95rem;">
            
            <button type="submit" style="background-color: #2196F3; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s;">
                Search
            </button>
            @if(request('search'))
                <a href="{{ route('admin.posts.index') }}" style="background-color: #f5f5f5; color: #333; border: 1px solid #ddd; padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 600; text-decoration: none; transition: background 0.2s;">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Table -->
    <div class="table-wrapper" style="background: white; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);">
        @if ($posts->count())
            <div style="overflow-x: auto;">
                <table class="orders-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="background-color: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 0.9rem;">ID</th>
                            <th style="background-color: #f5f5f5; padding: 1rem; text-align: center; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 0.9rem;">Image</th>
                            <th style="background-color: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 0.9rem;">Visitor</th>
                            <th style="background-color: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 0.9rem;">Emotion</th>
                            <th style="background-color: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 0.9rem;">Story Content</th>
                            <th style="background-color: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 0.9rem;">Posted</th>
                            <th style="background-color: #f5f5f5; padding: 1rem; text-align: left; font-weight: 600; color: #333; border-bottom: 2px solid #e0e0e0; font-size: 0.9rem;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $post)
                            <tr style="border-bottom: 1px solid #e0e0e0;">
                                <td style="padding: 1rem; color: #555; font-size: 0.9rem;">{{ $post->post_id }}</td>
                                <td style="padding: 1rem; text-align: center;">
                                    @if($post->featured_img)
                                        <img src="{{ asset('storage/' . $post->featured_img) }}" alt="Thumbnail" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd;">
                                    @else
                                        <div style="width: 48px; height: 48px; background-color: #f5f5f5; border-radius: 6px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #aaa; margin: 0 auto;">No Img</div>
                                    @endif
                                </td>
                                <td style="padding: 1rem; font-size: 0.9rem;">
                                    <div style="font-weight: 600; color: #111;">
                                        {{ optional($post->user->profile)->first_name ?? 'Visitor' }} {{ optional($post->user->profile)->last_name ?? '' }}
                                    </div>
                                    <div style="font-size: 0.8rem; color: #666; margin-top: 2px;">
                                        {{ $post->user->email }}
                                    </div>
                                    @if($post->user->premium_ended_at && \Carbon\Carbon::parse($post->user->premium_ended_at)->isFuture())
                                        <span style="display: inline-block; background-color: #fff3cd; color: #856404; font-size: 0.7rem; font-weight: bold; padding: 2px 6px; border-radius: 4px; margin-top: 4px;">
                                            👑 MEMBER
                                        </span>
                                    @endif
                                </td>
                                <td style="padding: 1rem; font-size: 0.9rem;">
                                    <span style="background-color: #e3f2fd; color: #1976D2; padding: 0.3rem 0.8rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        {{ $post->category->name }}
                                    </span>
                                </td>
                                <td style="padding: 1rem; font-size: 0.9rem; color: #333; line-height: 1.5; max-width: 300px;">
                                    <div style="font-weight: 600; margin-bottom: 4px; font-size: 0.95rem;">{{ $post->title }}</div>
                                    <div style="color: #666;">{{ Str::limit($post->body, 100) }}</div>
                                </td>
                                <td style="padding: 1rem; font-size: 0.85rem; color: #666;">
                                    {{ $post->created_at->format('M d, Y') }}<br>
                                    <span style="font-size: 0.8rem;">{{ $post->created_at->format('H:i') }}</span>
                                </td>
                                <td style="padding: 1rem;">
                                    <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to remove this story? This action cannot be undone.')">
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
            @if($posts->hasPages())
                <div style="padding: 1.5rem; border-top: 1px solid #e0e0e0; display: flex; justify-content: center;">
                    {{ $posts->links() }}
                </div>
            @endif
        @else
            <div style="padding: 4rem 2rem; text-align: center; color: #666;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
                <h3 style="margin: 0 0 0.5rem 0; font-size: 1.25rem; color: #333;">No Stories Found</h3>
                <p style="margin: 0; font-size: 0.95rem;">No visitor stories match your filter criteria.</p>
            </div>
        @endif
    </div>
</div>
@endsection
