<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        
        $query = Post::with(['user.profile', 'category']);

        if ($search) {
            $query->where('body', 'like', "%{$search}%")
                  ->orWhereHas('user.profile', function ($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('category', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $posts = $query->latest('post_id')->paginate(20)->withQueryString();

        return view('admin.posts.index', [
            'title'       => 'Visitor Stories Moderation',
            'subtitle'    => 'Moderate community experience wall entries',
            'activeNav'   => 'posts',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Visitor Stories', 'isCurrent' => true],
            ],
            'posts'       => $posts,
        ]);
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Visitor experience story has been successfully removed by moderation.');
    }
}
