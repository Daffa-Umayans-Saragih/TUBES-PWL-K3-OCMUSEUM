<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index', [
            'title'       => 'Reports',
            'subtitle'    => 'View and export reports',
            'activeNav'   => 'reports',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Reports', 'isCurrent' => true],
            ],
        ]);
    }
}
