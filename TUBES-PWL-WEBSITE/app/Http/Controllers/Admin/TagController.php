<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        $tags = Tag::withCount('artWorks')
            ->orderBy('tag_term')
            ->paginate(20);

        return view('admin.tags.index', [
            'title'      => 'Tags',
            'subtitle'   => 'Manage artwork tags',
            'activeNav'  => 'tags',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Tags', 'isCurrent' => true],
            ],
            'tags'       => $tags,
        ]);
    }

    public function create()
    {
        return view('admin.tags.form', [
            'title'      => 'Create Tag',
            'subtitle'   => 'Add a new tag',
            'activeNav'  => 'tags',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Tags', 'href' => route('admin.tags.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'tag'        => null,
            'isEdit'     => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tag_term'     => 'required|string|max:255|unique:tags,tag_term',
            'aat_url'      => 'nullable|url',
            'wikidata_url' => 'nullable|url',
        ]);

        Tag::create($validated);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag created successfully');
    }

    public function show(Tag $tag)
    {
        $tag->load(['artWorks' => function ($query) {
            $query->limit(10);
        }]);

        return view('admin.tags.show', [
            'title'      => 'Tag Details',
            'subtitle'   => $tag->tag_term,
            'activeNav'  => 'tags',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Tags', 'href' => route('admin.tags.index')],
                ['label' => $tag->tag_term, 'isCurrent' => true],
            ],
            'tag'        => $tag,
        ]);
    }

    public function edit(Tag $tag)
    {
        return view('admin.tags.form', [
            'title'      => 'Edit Tag',
            'subtitle'   => 'Update tag information',
            'activeNav'  => 'tags',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Tags', 'href' => route('admin.tags.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'tag'        => $tag,
            'isEdit'     => true,
        ]);
    }

    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'tag_term'     => 'required|string|max:255|unique:tags,tag_term,' . $tag->tag_id . ',tag_id',
            'aat_url'      => 'nullable|url',
            'wikidata_url' => 'nullable|url',
        ]);

        $tag->update($validated);

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag updated successfully');
    }

    public function destroy(Tag $tag)
    {
        if ($tag->artWorks()->exists()) {
            return redirect()->route('admin.tags.index')
                ->with('error', 'Cannot delete tag with associated artworks');
        }

        $tag->delete();

        return redirect()->route('admin.tags.index')
            ->with('success', 'Tag deleted successfully');
    }
}
