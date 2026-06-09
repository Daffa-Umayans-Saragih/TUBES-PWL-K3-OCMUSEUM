<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VisitController extends Controller
{
    /**
     * Display main visit planning page with all locations
     */
    public function index()
    {
        return view('ordinary.plan-your-visit.visit.visit', [
            'title' => 'Plan Your Visit',
        ]);
    }

    /**
     * Display The Met Fifth Avenue location details
     */
    public function fifth()
    {
        return view('ordinary.plan-your-visit.fifth.fifth', [
            'title'    => 'The Met - Fifth Avenue',
            'location' => 'Fifth Avenue',
        ]);
    }

    /**
     * Display The Cloisters location details
     */
    public function cloisters()
    {
        return view('ordinary.plan-your-visit.cloister.cloisters', [
            'title'    => 'The Met - The Cloisters',
            'location' => 'The Cloisters',
        ]);
    }
}
