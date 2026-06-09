<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Repository;
use Illuminate\Http\Request;

class RepositoryController extends Controller
{
    public function index()
    {
        $repositories = Repository::withCount('artWorks')
            ->orderBy('repository_name')
            ->paginate(20);

        return view('admin.repositories.index', [
            'title'        => 'Repositories',
            'subtitle'     => 'Manage artwork repositories',
            'activeNav'    => 'repositories',
            'breadcrumbs'  => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Repositories', 'isCurrent' => true],
            ],
            'repositories' => $repositories,
        ]);
    }

    public function create()
    {
        return view('admin.repositories.form', [
            'title'      => 'Create Repository',
            'subtitle'   => 'Add a new repository',
            'activeNav'  => 'repositories',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Repositories', 'href' => route('admin.repositories.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'repository' => null,
            'isEdit'     => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'repository_name' => 'required|string|max:255|unique:repositories,repository_name',
        ]);

        Repository::create($validated);

        return redirect()->route('admin.repositories.index')
            ->with('success', 'Repository created successfully');
    }

    public function show(Repository $repository)
    {
        $repository->load(['artWorks' => function ($query) {
            $query->limit(10);
        }]);

        return view('admin.repositories.show', [
            'title'      => 'Repository Details',
            'subtitle'   => $repository->repository_name,
            'activeNav'  => 'repositories',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Repositories', 'href' => route('admin.repositories.index')],
                ['label' => $repository->repository_name, 'isCurrent' => true],
            ],
            'repository' => $repository,
        ]);
    }

    public function edit(Repository $repository)
    {
        return view('admin.repositories.form', [
            'title'      => 'Edit Repository',
            'subtitle'   => 'Update repository information',
            'activeNav'  => 'repositories',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Repositories', 'href' => route('admin.repositories.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'repository' => $repository,
            'isEdit'     => true,
        ]);
    }

    public function update(Request $request, Repository $repository)
    {
        $validated = $request->validate([
            'repository_name' => 'required|string|max:255|unique:repositories,repository_name,' . $repository->repository_id . ',repository_id',
        ]);

        $repository->update($validated);

        return redirect()->route('admin.repositories.index')
            ->with('success', 'Repository updated successfully');
    }

    public function destroy(Repository $repository)
    {
        if ($repository->artWorks()->exists()) {
            return redirect()->route('admin.repositories.index')
                ->with('error', 'Cannot delete repository with associated artworks');
        }

        $repository->delete();

        return redirect()->route('admin.repositories.index')
            ->with('success', 'Repository deleted successfully');
    }
}
