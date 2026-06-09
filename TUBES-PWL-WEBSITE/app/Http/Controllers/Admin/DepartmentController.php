<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of departments
     */
    public function index()
    {
        $departments = Department::withCount('artWorks')
            ->orderBy('department_name')
            ->paginate(20);

        return view('admin.departments.index', [
            'title'       => 'Departments',
            'subtitle'    => 'Manage museum departments',
            'activeNav'   => 'departments',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Departments', 'isCurrent' => true],
            ],
            'departments' => $departments,
        ]);
    }

    /**
     * Show the form for creating a new department
     */
    public function create()
    {
        return view('admin.departments.form', [
            'title'       => 'Create Department',
            'subtitle'    => 'Add a new department',
            'activeNav'   => 'departments',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Departments', 'href' => route('admin.departments.index')],
                ['label' => 'Create', 'isCurrent' => true],
            ],
            'department'  => null,
            'isEdit'      => false,
        ]);
    }

    /**
     * Store a newly created department
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_name'  => 'required|string|max:255|unique:departments,department_name',
            'department_image' => 'nullable|string|max:2048',
        ]);

        Department::create($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully');
    }

    /**
     * Show the specified department
     */
    public function show(Department $department)
    {
        $department->load(['artWorks' => function ($query) {
            $query->limit(10);
        }]);

        return view('admin.departments.show', [
            'title'       => 'Department Details',
            'subtitle'    => $department->department_name,
            'activeNav'   => 'departments',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Departments', 'href' => route('admin.departments.index')],
                ['label' => $department->department_name, 'isCurrent' => true],
            ],
            'department'  => $department,
        ]);
    }

    /**
     * Show the form for editing the department
     */
    public function edit(Department $department)
    {
        return view('admin.departments.form', [
            'title'       => 'Edit Department',
            'subtitle'    => 'Update department information',
            'activeNav'   => 'departments',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Departments', 'href' => route('admin.departments.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'department'  => $department,
            'isEdit'      => true,
        ]);
    }

    /**
     * Update the specified department
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'department_name'  => 'required|string|max:255|unique:departments,department_name,' . $department->department_id . ',department_id',
            'department_image' => 'nullable|string|max:2048',
        ]);

        $department->update($validated);

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully');
    }

    /**
     * Delete the specified department
     */
    public function destroy(Department $department)
    {
        // Check if department has artworks
        if ($department->artWorks()->exists()) {
            return redirect()->route('admin.departments.index')
                ->with('error', 'Cannot delete department with associated artworks');
        }

        $department->delete();

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department deleted successfully');
    }
}
