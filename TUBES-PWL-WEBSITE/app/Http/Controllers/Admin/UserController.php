<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $search = request()->query('search');
        $roleFilter = request()->query('role');
        $sourceFilter = request()->query('source');
        $sortBy = request()->query('sort_by', 'created_at');
        $sortOrder = request()->query('sort_order', 'desc');

        $userUsesSoftDeletes = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(\App\Models\User::class));
        $guestUsesSoftDeletes = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(\App\Models\Guest::class));

        // Fetch Users with profile
        $userQuery = \App\Models\User::with('profile');
        if ($userUsesSoftDeletes) {
            $userQuery->withTrashed();
        }
        $usersRaw = $userQuery->get()->map(function ($u) use ($userUsesSoftDeletes) {
            return [
                'id'                => $u->user_id,
                'name'              => $u->name ?: 'N/A',
                'email'             => $u->email ?: 'N/A',
                'role'              => $u->role_admin ?? ($u->is_admin ? 'admin' : 'user'),
                'source'            => 'Users',
                'status'            => ($userUsesSoftDeletes && $u->deleted_at) ? 'deleted' : 'active',
                'uses_soft_deletes' => $userUsesSoftDeletes,
                'created_at'        => $u->created_at ? $u->created_at->format('Y-m-d H:i:s') : 'N/A',
            ];
        });

        // Fetch Guests
        $guestQuery = \App\Models\Guest::query();
        if ($guestUsesSoftDeletes) {
            $guestQuery->withTrashed();
        }
        $guestsRaw = $guestQuery->get()->map(function ($g) use ($guestUsesSoftDeletes) {
            return [
                'id'                => $g->guest_id,
                'name'              => $g->name ?: 'N/A',
                'email'             => $g->email ?: 'N/A',
                'role'              => 'guest',
                'source'            => 'Guests',
                'status'            => ($guestUsesSoftDeletes && $g->deleted_at) ? 'deleted' : 'active',
                'uses_soft_deletes' => $guestUsesSoftDeletes,
                'created_at'        => $g->created_at ? $g->created_at->format('Y-m-d H:i:s') : 'N/A',
            ];
        });

        // Merge collections
        $merged = $usersRaw->concat($guestsRaw);

        // Apply filters & search
        if ($search) {
            $searchLower = strtolower($search);
            $merged = $merged->filter(function ($item) use ($searchLower) {
                return str_contains(strtolower($item['name']), $searchLower) ||
                       str_contains(strtolower($item['email']), $searchLower) ||
                       str_contains(strtolower($item['role']), $searchLower) ||
                       str_contains(strtolower($item['source']), $searchLower);
            });
        }

        if ($roleFilter) {
            $merged = $merged->filter(function ($item) use ($roleFilter) {
                return strtolower($item['role']) === strtolower($roleFilter);
            });
        }

        if ($sourceFilter) {
            $merged = $merged->filter(function ($item) use ($sourceFilter) {
                return strtolower($item['source']) === strtolower($sourceFilter);
            });
        }

        // Apply Sort
        if ($sortOrder === 'desc') {
            $merged = $merged->sortByDesc($sortBy);
        } else {
            $merged = $merged->sortBy($sortBy);
        }

        // Pagination
        $perPage = 10;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage() ?: 1;
        $currentItems = $merged->slice(($currentPage - 1) * $perPage, $perPage)->all();

        $users = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $merged->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );
        $users->appends(request()->query());

        // Quick Stats
        $totalUsers = \App\Models\User::count() + \App\Models\Guest::count();
        $adminCount = \App\Models\User::whereNotNull('role_admin')->orWhere('is_admin', true)->count();
        $activeToday = \App\Models\User::whereDate('created_at', \Carbon\Carbon::today())->count() +
                       \App\Models\Guest::whereDate('created_at', \Carbon\Carbon::today())->count();

        return view('admin.users.index', [
            'title'       => 'Users',
            'subtitle'    => 'Manage all users',
            'activeNav'   => 'users',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Users', 'isCurrent' => true],
            ],
            'users'       => $users,
            'totalUsers'  => $totalUsers,
            'adminCount'  => $adminCount,
            'activeToday' => $activeToday,
        ]);
    }

    public function edit($id)
    {
        $source = request()->query('source', 'Users');

        $userUsesSoftDeletes = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(\App\Models\User::class));
        $guestUsesSoftDeletes = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(\App\Models\Guest::class));

        if ($source === 'Guests') {
            $guestQuery = \App\Models\Guest::query();
            if ($guestUsesSoftDeletes) {
                $guestQuery->withTrashed();
            }
            $entity = $guestQuery->findOrFail($id);
            $name = $entity->name;
            $email = $entity->email;
            $role = 'guest';
            $is_admin = false;
        } else {
            $userQuery = \App\Models\User::with('profile');
            if ($userUsesSoftDeletes) {
                $userQuery->withTrashed();
            }
            $entity = $userQuery->findOrFail($id);
            $name = $entity->name;
            $email = $entity->email;
            $role = $entity->role_admin ?? ($entity->is_admin ? 'admin' : 'user');
            $is_admin = $entity->is_admin;
        }

        return view('admin.users.edit', [
            'title'       => 'Edit User',
            'subtitle'    => 'Edit user details and roles',
            'activeNav'   => 'users',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Users', 'href' => route('admin.users.index')],
                ['label' => 'Edit', 'isCurrent' => true],
            ],
            'id'          => $id,
            'source'      => $source,
            'name'        => $name,
            'email'       => $email,
            'role'        => $role,
            'is_admin'    => $is_admin,
            'firstName'   => $source === 'Guests' ? $entity->first_name : ($entity->profile?->first_name ?? ''),
            'lastName'    => $source === 'Guests' ? $entity->last_name : ($entity->profile?->last_name ?? ''),
        ]);
    }

    public function update(Request $request, $id)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Unauthorized action. Superadmin role required.');

        $source = $request->input('source', 'Users');

        $userUsesSoftDeletes = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(\App\Models\User::class));
        $guestUsesSoftDeletes = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(\App\Models\Guest::class));

        if ($source === 'Guests') {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name'  => 'nullable|string|max:255',
                'email'      => 'required|email|unique:guests,email,' . $id . ',guest_id',
            ]);

            $guestQuery = \App\Models\Guest::query();
            if ($guestUsesSoftDeletes) {
                $guestQuery->withTrashed();
            }
            $guest = $guestQuery->findOrFail($id);
            $guest->update([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
            ]);
        } else {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name'  => 'nullable|string|max:255',
                'email'      => 'required|email|unique:users,email,' . $id . ',user_id',
                'role_admin' => 'nullable|in:cashier,admin,superadmin,user',
            ]);

            $userQuery = \App\Models\User::query();
            if ($userUsesSoftDeletes) {
                $userQuery->withTrashed();
            }
            $user = $userQuery->findOrFail($id);
            $roleAdmin = $request->role_admin;
            if ($roleAdmin === 'user') {
                $roleAdmin = null;
            }

            $user->update([
                'email'      => $request->email,
                'role_admin' => $roleAdmin,
                'is_admin'   => $roleAdmin ? true : false,
            ]);

            $user->profile()->updateOrCreate(
                ['user_id' => $user->user_id],
                [
                    'first_name' => $request->first_name,
                    'last_name'  => $request->last_name,
                ]
            );
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Unauthorized action. Superadmin role required.');

        $source = request()->query('source', 'Users');

        $userUsesSoftDeletes = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(\App\Models\User::class));
        $guestUsesSoftDeletes = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(\App\Models\Guest::class));

        if ($source === 'Guests') {
            $guestQuery = \App\Models\Guest::query();
            if ($guestUsesSoftDeletes) {
                $guestQuery->withTrashed();
            }
            $entity = $guestQuery->findOrFail($id);
            $usesSoftDeletes = $guestUsesSoftDeletes;

            // Check relations
            $ordersCount = $entity->orders()->count();
            $ticketsCount = \App\Models\Ticket::whereIn('order_id', $entity->orders()->pluck('order_id'))->count();
            $paymentsCount = \App\Models\Payment::whereIn('order_id', $entity->orders()->pluck('order_id'))->count();
            $hasRelations = ($ordersCount > 0 || $ticketsCount > 0 || $paymentsCount > 0);
        } else {
            $userQuery = \App\Models\User::query();
            if ($userUsesSoftDeletes) {
                $userQuery->withTrashed();
            }
            $entity = $userQuery->findOrFail($id);
            $usesSoftDeletes = $userUsesSoftDeletes;

            // Check relations
            $ordersCount = $entity->orders()->count();
            $ticketsCount = \App\Models\Ticket::whereIn('order_id', $entity->orders()->pluck('order_id'))->count();
            $paymentsCount = \App\Models\Payment::whereIn('order_id', $entity->orders()->pluck('order_id'))->count();
            $hasRelations = ($ordersCount > 0 || $ticketsCount > 0 || $paymentsCount > 0);
        }

        if ($hasRelations && !$usesSoftDeletes) {
            return redirect()->route('admin.users.index')->with('error', 'Cannot hard delete user: user has active orders, payments, or tickets associated with their account.');
        }

        $entity->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function restore($id)
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Unauthorized action. Superadmin role required.');

        $source = request()->query('source', 'Users');

        $userUsesSoftDeletes = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(\App\Models\User::class));
        $guestUsesSoftDeletes = in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(\App\Models\Guest::class));

        if ($source === 'Guests') {
            if (!$guestUsesSoftDeletes) {
                return redirect()->route('admin.users.index')->with('error', 'Restore is not supported for this entity.');
            }
            $entity = \App\Models\Guest::onlyTrashed()->findOrFail($id);
        } else {
            if (!$userUsesSoftDeletes) {
                return redirect()->route('admin.users.index')->with('error', 'Restore is not supported for this entity.');
            }
            $entity = \App\Models\User::onlyTrashed()->findOrFail($id);
        }

        $entity->restore();

        return redirect()->route('admin.users.index')->with('success', 'User restored successfully.');
    }
}
