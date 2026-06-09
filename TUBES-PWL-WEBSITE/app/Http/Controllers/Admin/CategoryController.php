<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('posts')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.categories.index', [
            'title'       => 'Emotion Categories',
            'subtitle'    => 'Manage categories for visitor experiences',
            'activeNav'   => 'categories',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Categories', 'isCurrent' => true],
            ],
            'categories'  => $categories,
        ]);
    }

    public function create()
    {
        return view('admin.categories.form', [
            'title'       => 'Create Category',
            'subtitle'    => 'Add a new experience emotion category',
            'activeNav'   => 'categories',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Categories', 'href' => route('admin.categories.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'category'    => null,
            'isEdit'      => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255|unique:categories,name',
            'active'    => 'boolean',
        ]);

        $validated['active'] = $request->has('active');

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Emotion category created successfully!');
    }



    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Emotion category deleted successfully!');
    }
}
