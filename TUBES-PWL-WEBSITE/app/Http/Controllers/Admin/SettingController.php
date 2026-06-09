<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'title'       => 'Settings',
            'subtitle'    => 'Manage admin settings',
            'activeNav'   => 'settings',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Settings', 'isCurrent' => true],
            ],
        ]);
    }
}
