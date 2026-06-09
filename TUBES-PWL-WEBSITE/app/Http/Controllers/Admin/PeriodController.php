<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Period;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    public function index()
    {
        $periods = Period::withCount('artWorks')
            ->orderBy('period_name')
            ->paginate(20);

        return view('admin.periods.index', [
            'title'      => 'Periods',
            'subtitle'   => 'Manage artwork periods',
            'activeNav'  => 'periods',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Periods', 'isCurrent' => true],
            ],
            'periods'    => $periods,
        ]);
    }

    public function create()
    {
        return view('admin.periods.form', [
            'title'      => 'Create Period',
            'subtitle'   => 'Add a new period',
            'activeNav'  => 'periods',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Periods', 'href' => route('admin.periods.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'period'     => null,
            'isEdit'     => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'period_name' => 'required|string|max:255|unique:periods,period_name',
        ]);

        Period::create($validated);

        return redirect()->route('admin.periods.index')
            ->with('success', 'Period created successfully');
    }

    public function show(Period $period)
    {
        $period->load(['artWorks' => function ($query) {
            $query->limit(10);
        }]);

        return view('admin.periods.show', [
            'title'      => 'Period Details',
            'subtitle'   => $period->period_name,
            'activeNav'  => 'periods',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Periods', 'href' => route('admin.periods.index')],
                ['label' => $period->period_name, 'isCurrent' => true],
            ],
            'period'     => $period,
        ]);
    }

    public function edit(Period $period)
    {
        return view('admin.periods.form', [
            'title'      => 'Edit Period',
            'subtitle'   => 'Update period information',
            'activeNav'  => 'periods',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Periods', 'href' => route('admin.periods.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'period'     => $period,
            'isEdit'     => true,
        ]);
    }

    public function update(Request $request, Period $period)
    {
        $validated = $request->validate([
            'period_name' => 'required|string|max:255|unique:periods,period_name,' . $period->period_id . ',period_id',
        ]);

        $period->update($validated);

        return redirect()->route('admin.periods.index')
            ->with('success', 'Period updated successfully');
    }

    public function destroy(Period $period)
    {
        if ($period->artWorks()->exists()) {
            return redirect()->route('admin.periods.index')
                ->with('error', 'Cannot delete period with associated artworks');
        }

        $period->delete();

        return redirect()->route('admin.periods.index')
            ->with('success', 'Period deleted successfully');
    }
}
