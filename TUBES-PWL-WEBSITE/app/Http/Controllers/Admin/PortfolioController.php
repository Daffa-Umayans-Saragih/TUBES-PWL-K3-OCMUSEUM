<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index()
    {
        $portfolios = Portfolio::withCount('artWorks')
            ->orderBy('portfolio_name')
            ->paginate(20);

        return view('admin.portfolios.index', [
            'title'      => 'Portfolios',
            'subtitle'   => 'Manage artwork portfolios',
            'activeNav'  => 'portfolios',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Portfolios', 'isCurrent' => true],
            ],
            'portfolios' => $portfolios,
        ]);
    }

    public function create()
    {
        return view('admin.portfolios.form', [
            'title'      => 'Create Portfolio',
            'subtitle'   => 'Add a new portfolio',
            'activeNav'  => 'portfolios',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Portfolios', 'href' => route('admin.portfolios.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'portfolio'  => null,
            'isEdit'     => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'portfolio_name' => 'required|string|max:255|unique:portfolios,portfolio_name',
        ]);

        Portfolio::create($validated);

        return redirect()->route('admin.portfolios.index')
            ->with('success', 'Portfolio created successfully');
    }

    public function show(Portfolio $portfolio)
    {
        $portfolio->load(['artWorks' => function ($query) {
            $query->limit(10);
        }]);

        return view('admin.portfolios.show', [
            'title'      => 'Portfolio Details',
            'subtitle'   => $portfolio->portfolio_name,
            'activeNav'  => 'portfolios',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Portfolios', 'href' => route('admin.portfolios.index')],
                ['label' => $portfolio->portfolio_name, 'isCurrent' => true],
            ],
            'portfolio'  => $portfolio,
        ]);
    }

    public function edit(Portfolio $portfolio)
    {
        return view('admin.portfolios.form', [
            'title'      => 'Edit Portfolio',
            'subtitle'   => 'Update portfolio information',
            'activeNav'  => 'portfolios',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Portfolios', 'href' => route('admin.portfolios.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'portfolio'  => $portfolio,
            'isEdit'     => true,
        ]);
    }

    public function update(Request $request, Portfolio $portfolio)
    {
        $validated = $request->validate([
            'portfolio_name' => 'required|string|max:255|unique:portfolios,portfolio_name,' . $portfolio->portfolio_id . ',portfolio_id',
        ]);

        $portfolio->update($validated);

        return redirect()->route('admin.portfolios.index')
            ->with('success', 'Portfolio updated successfully');
    }

    public function destroy(Portfolio $portfolio)
    {
        if ($portfolio->artWorks()->exists()) {
            return redirect()->route('admin.portfolios.index')
                ->with('error', 'Cannot delete portfolio with associated artworks');
        }

        $portfolio->delete();

        return redirect()->route('admin.portfolios.index')
            ->with('success', 'Portfolio deleted successfully');
    }
}
