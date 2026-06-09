<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Post;

class OurExperienceController extends Controller
{
    /**
     * Display the public experience wall with stories.
     */
    public function index(Request $request)
    {
        $categories = Category::where('active', true)->orderBy('name')->get();
        
        $selectedCategoryId = $request->query('category_id');
        
        $query = Post::with(['user.profile', 'category']);
        
        if ($selectedCategoryId) {
            $query->where('category_id', $selectedCategoryId);
        }
        
        $posts = $query->latest('post_id')->paginate(10)->withQueryString();
        
        return view('ordinary.plan-your-visit.our-experience.our-experience', compact('categories', 'posts', 'selectedCategoryId'));
    }

    /**
     * Store a new visitor experience post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'category_id' => 'required|exists:categories,category_id',
            'body'        => 'required|string|min:5|max:1500',
            'featured_img'=> 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        // Verify that the category is active
        $category = Category::where('category_id', $validated['category_id'])
            ->where('active', true)
            ->firstOrFail();

        $featuredImgPath = null;
        if ($request->hasFile('featured_img')) {
            $featuredImgPath = $request->file('featured_img')->store('experiences', 'public');
        }

        Post::create([
            'user_id'      => auth()->id(),
            'category_id'  => $category->category_id,
            'title'        => $validated['title'],
            'body'         => $validated['body'],
            'featured_img' => $featuredImgPath,
        ]);

        return redirect()->route('visit.our-experience')
            ->with('success', 'Thank you for sharing your experience with the museum community!');
    }
}
