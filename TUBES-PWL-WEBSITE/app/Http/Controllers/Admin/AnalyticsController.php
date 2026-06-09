<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AnalyticsController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.dashboard');
    }
}
