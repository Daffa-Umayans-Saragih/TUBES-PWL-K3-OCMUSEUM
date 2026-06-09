<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArtWork;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Display dashboard overview with statistics and charts
     * ✅ BUG FIX #1: Fixed Ticket query using whereHas with order relationship
     */
    public function index()
    {
        // Get today's date
        $today = now()->startOfDay();
        $endToday = now()->endOfDay();

        // 1. Total Revenue (Real Revenue, Paid/Completed only)
        $totalRevenue = Order::whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed'])
            ->sum('total_amount');

        // 2. Today's Revenue (Paid/Completed only)
        $totalRevenueToday = Order::whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed'])
            ->whereBetween('order_date', [$today, $endToday])
            ->sum('total_amount');

        // 3. Tickets Sold Today (Valid Paid/Completed only)
        $ticketsSoldToday = Ticket::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$today, $endToday])
            ->whereHas('order', function ($q) {
                $q->whereRaw('LOWER(order_status) IN (?, ?)', ['paid', 'completed']);
            })
            ->count();

        // 4. Total Orders (All Time)
        $totalOrders = Order::count();

        // 5. Today's Orders
        $todayOrders = Order::whereBetween('order_date', [$today, $endToday])->count();

        // 6. Pending Orders (pending_payment / pending only)
        $pendingOrders = Order::whereRaw('LOWER(order_status) IN (?, ?)', ['pending', 'pending_payment'])
            ->count();

        // 7. Total Users
        $totalUsers = User::count();

        // 8. Total Artworks
        $totalArtworks = ArtWork::count();

        return view('admin.dashboard.index', [
            'title'              => 'Dashboard',
            'subtitle'           => 'Welcome to the admin dashboard',
            'activeNav'          => 'dashboard',
            'breadcrumbs'        => [
                ['label' => 'Dashboard', 'isCurrent' => true],
            ],
            'totalRevenue'       => $totalRevenue,
            'totalRevenueToday'  => $totalRevenueToday,
            'ticketsSoldToday'   => $ticketsSoldToday,
            'totalOrders'        => $totalOrders,
            'todayOrders'        => $todayOrders,
            'pendingOrders'      => $pendingOrders,
            'totalUsers'         => $totalUsers,
            'totalArtworks'      => $totalArtworks,
        ]);
    }

    /**
     * Display transactions list with filtering and pagination
     */
    public function transactions(Request $request)
    {
        $perPage = $request->get('perPage', 15);
        $search  = $request->get('search', '');
        $status  = $request->get('status', 'all');
        $month   = $request->get('month', null);

        $query = Order::query()->with(['user', 'orderDetails.ticket.ticketType', 'payment']);

        // Search by order ID or user email
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($subQ) use ($search) {
                        $subQ->where('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        // Filter by month
        if ($month) {
            $query->whereMonth('order_date', $month)
                ->whereYear('order_date', now()->year);
        }

        $transactions = $query->orderBy('order_date', 'desc')->paginate($perPage);

        // Sales Chart - Weekly
        $weeklySales = collect(range(6, 0))->map(function ($day) {
            $date = now()->subDays($day);
            return [
                'date'  => $date->format('M d'),
                'sales' => Order::whereDate('order_date', $date)
                    ->where('status', 'completed')
                    ->sum('total_amount'),
            ];
        });

        // Sales Chart - Monthly
        $monthlySales = collect(range(11, 0))->map(function ($month) {
            $date = now()->subMonths($month);
            return [
                'month' => $date->format('M'),
                'sales' => Order::whereMonth('order_date', $date->month)
                    ->whereYear('order_date', $date->year)
                    ->where('status', 'completed')
                    ->sum('total_amount'),
            ];
        });

        return view('admin.dashboard.transactions', [
            'transactions' => $transactions,
            'search'       => $search,
            'status'       => $status,
            'month'        => $month,
            'weeklySales'  => $weeklySales,
            'monthlySales' => $monthlySales,
        ]);
    }

    /**
     * Display artworks list with filtering, searching, and sorting
     * ✅ BUG FIX #2: Fixed sorting using art_work_id instead of non-existent created_at
     */
    public function artworks(Request $request)
    {
        $perPage    = $request->get('perPage', 20);
        $search     = $request->get('search', '');
        $department = $request->get('department', null);
        $sortBy     = $request->get('sort', 'latest');

        $query = ArtWork::query();

        // Search by title or description
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('accession_number', 'like', "%{$search}%");
            });
        }

        // Filter by department
        if ($department) {
            $query->where('department_id', $department);
        }

        // ✅ BUG FIX #2: Use art_work_id for sorting (not created_at which doesn't exist)
        switch ($sortBy) {
            case 'oldest':
                $query->orderBy('art_work_id', 'asc');
                break;
            case 'title_az':
                $query->orderBy('title', 'asc');
                break;
            case 'title_za':
                $query->orderBy('title', 'desc');
                break;
            default:
                $query->orderBy('art_work_id', 'desc');
        }

        $artworks = $query->with(['department', 'images', 'constituents'])->paginate($perPage);

        // Statistics
        $stats = [
            'total_artworks'    => ArtWork::count(),
            'total_departments' => ArtWork::distinct('department_id')->count(),
            'total_images'      => DB::table('art_work_images')->count(),
            'total_artists'     => DB::table('constituents')->count(),
        ];

        // Get departments for filter
        $departments = DB::table('departments')->get();

        return view('admin.dashboard.artworks', [
            'artworks'    => $artworks,
            'stats'       => $stats,
            'departments' => $departments,
            'search'      => $search,
            'department'  => $department,
            'sortBy'      => $sortBy,
        ]);
    }

    /**
     * Export transactions to CSV
     */
    public function exportTransactions(Request $request)
    {
        $status = $request->get('status', 'all');
        $month  = $request->get('month', null);

        $query = Order::query()->with(['user', 'orderDetails.ticket.ticketType', 'payment']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($month) {
            $query->whereMonth('order_date', $month)
                ->whereYear('order_date', now()->year);
        }

        $transactions = $query->orderBy('order_date', 'desc')->get();

        $csv = fopen('php://output', 'w');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="transactions_' . now()->format('Y-m-d') . '.csv"');

        // Header row
        fputcsv($csv, ['Order ID', 'User Email', 'Total Amount', 'Status', 'Order Date']);

        // Data rows
        foreach ($transactions as $transaction) {
            fputcsv($csv, [
                $transaction->order_id,
                $transaction->user->email ?? 'N/A',
                $transaction->total_amount,
                $transaction->status,
                $transaction->order_date->format('Y-m-d H:i:s'),
            ]);
        }

        fclose($csv);
        exit;
    }

    /**
     * Store new artwork in database
     * ✅ BUG FIX #3 & #5: Fixed invalid field names and added all required NOT NULL fields
     */
    public function storeArtwork(Request $request)
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:255',
            'accession_number'  => 'nullable|string|max:255|unique:art_works,accession_number',
            'description'       => 'nullable|string',
            'department_id'     => 'required|integer',
            'type_id'           => 'nullable|integer',
            'repository_id'     => 'nullable|integer',
            'classification_id' => 'nullable|integer',
            'location_id'       => 'nullable|integer',
            'year_created'      => 'nullable|integer',
            'images'            => 'nullable|array',
            'images.*'          => 'image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        try {
            // ✅ BUG FIX #5: Provide all required NOT NULL fields with sensible defaults
            $artwork = ArtWork::create([
                'title'             => $validated['title'],
                'description'       => $validated['description'] ?? null,
                'department_id'     => $validated['department_id'],
                'accession_number'  => $validated['accession_number'] ?? 'ACC-' . time(),
                'type_id'           => $validated['type_id'] ?? 1,
                'repository_id'     => $validated['repository_id'] ?? 1,
                'classification_id' => $validated['classification_id'] ?? 1,
                'location_id'       => $validated['location_id'] ?? 1,
                'met_object_id'     => rand(100000, 999999),
                // ✅ BUG FIX #3: Use accession_year instead of date_created
                'accession_year'    => $validated['year_created'] ?? null,
                'is_on_view'        => 0,
                'is_highlight'      => 0,
                'is_public_domain'  => 0,
                'is_timeline_work'  => 0,
            ]);

            // Handle images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('artworks', 'public');
                    $artwork->images()->create(['image_url' => $path]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Artwork created successfully',
                'data'    => $artwork->load('images'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create artwork: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update existing artwork
     * ✅ BUG FIX #4: Fixed invalid field name and use safe partial update pattern
     */
    public function updateArtwork(Request $request, $id)
    {
        try {
            $artwork = ArtWork::findOrFail($id);

            $validated = $request->validate([
                'title'             => 'sometimes|string|max:255',
                'accession_number'  => 'nullable|string|max:255|unique:art_works,accession_number,' . $id . ',art_work_id',
                'description'       => 'nullable|string',
                'department_id'     => 'sometimes|integer',
                'type_id'           => 'nullable|integer',
                'repository_id'     => 'nullable|integer',
                'classification_id' => 'nullable|integer',
                'location_id'       => 'nullable|integer',
                'year_created'      => 'nullable|integer',
                'images'            => 'nullable|array',
                'images.*'          => 'image|mimes:jpeg,png,jpg,gif|max:5120',
            ]);

            // Safe partial update pattern
            $updateData = [];
            if (isset($validated['title'])) {
                $updateData['title'] = $validated['title'];
            }

            if (isset($validated['description'])) {
                $updateData['description'] = $validated['description'];
            }

            if (isset($validated['department_id'])) {
                $updateData['department_id'] = $validated['department_id'];
            }

            if (isset($validated['accession_number'])) {
                $updateData['accession_number'] = $validated['accession_number'];
            }

            if (isset($validated['type_id'])) {
                $updateData['type_id'] = $validated['type_id'];
            }

            if (isset($validated['repository_id'])) {
                $updateData['repository_id'] = $validated['repository_id'];
            }

            if (isset($validated['classification_id'])) {
                $updateData['classification_id'] = $validated['classification_id'];
            }

            if (isset($validated['location_id'])) {
                $updateData['location_id'] = $validated['location_id'];
            }

            // ✅ BUG FIX #4: Use accession_year instead of date_created
            if (isset($validated['year_created'])) {
                $updateData['accession_year'] = $validated['year_created'];
            }

            $artwork->update($updateData);

            // Handle new images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('artworks', 'public');
                    $artwork->images()->create(['image_url' => $path]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Artwork updated successfully',
                'data'    => $artwork->load('images'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update artwork: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete artwork and related images
     */
    public function destroyArtwork($id)
    {
        try {
            $artwork = ArtWork::findOrFail($id);

            // Delete associated images from storage
            foreach ($artwork->images as $image) {
                if (Storage::disk('public')->exists($image->image_url)) {
                    Storage::disk('public')->delete($image->image_url);
                }
            }

            // Delete artwork record
            $artwork->delete();

            return response()->json([
                'success' => true,
                'message' => 'Artwork deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete artwork: ' . $e->getMessage(),
            ], 500);
        }
    }
}
