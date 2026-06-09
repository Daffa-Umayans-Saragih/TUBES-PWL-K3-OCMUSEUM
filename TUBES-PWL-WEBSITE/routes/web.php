<?php

// =========================
// IMPORTS (ALL AT TOP)
// =========================
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\ArtworkController as AdminArtworkController;
use App\Http\Controllers\Admin\ClassificationController;
use App\Http\Controllers\Admin\ConstituentController;
use App\Http\Controllers\Admin\CultureController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\DynastyController;
use App\Http\Controllers\Admin\ExhibitionController as AdminExhibitionController;
use App\Http\Controllers\Admin\LocationController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\MediumController;
use App\Http\Controllers\Admin\ObjectTypeController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\PeriodController;
use App\Http\Controllers\Admin\PortfolioController;
use App\Http\Controllers\Admin\ReignController;
use App\Http\Controllers\Admin\RepositoryController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\TicketAnalyticsController;
use App\Http\Controllers\Admin\TicketController as AdminTicketController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\ArtController;
use App\Http\Controllers\ArtWorkController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckAccountController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\GuestCheckoutController;
use App\Http\Controllers\GuestLoginController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\OurExperienceController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// =========================
// HOME ROUTES
// =========================
Route::get('/', function () {
    if (!\Illuminate\Support\Facades\Schema::hasTable('art_works')) {
        $nowOnView = collect();
    } else {
        $nowOnView = \App\Models\ArtWork::query()
            ->with(['images', 'constituents'])
            ->where('is_on_view', 1)
            ->whereHas('images')
            ->limit(10)
            ->get();

        // Fallback: If no artworks are set to on_view with images, load any artworks with images
        if ($nowOnView->isEmpty()) {
            $nowOnView = \App\Models\ArtWork::query()
                ->with(['images', 'constituents'])
                ->whereHas('images')
                ->limit(10)
                ->get();
        }
    }

    return view('ordinary.home.welcome.welcome', compact('nowOnView'));
})->name('home');
Route::get('/about', function () {
    return view('ordinary.about.about');
})->name('about');

// =========================
// ART COLLECTION ROUTES
// =========================
Route::prefix('art')->group(function () {
    Route::get('/collection', [ArtController::class, 'index'])->name('art.index');
    Route::get('/curatorial-areas', [ArtController::class, 'curatorialAreas'])->name('art.curatorial-areas');
    Route::get('/collection/search', [ArtController::class, 'search'])->name('art.search');
    Route::get('/collection/{id}', [ArtController::class, 'show'])->name('art.show');
});

// =========================
// VISIT ROUTES
// =========================
Route::prefix('plan-your-visit')->group(function () {
    Route::get('/', [VisitController::class, 'index'])->name('plan-your-visit.index');
    Route::get('/met-fifth-avenue', [VisitController::class, 'fifth'])->name('plan-your-visit.fifth');
    Route::get('/met-cloisters', [VisitController::class, 'cloisters'])->name('plan-your-visit.cloisters');
});

// =========================
// AUTHENTICATION ROUTES
// =========================
Route::prefix('account')->group(function () {
    // Guest routes (only for non-authenticated users)
    Route::middleware('guest')->group(function () {
        Route::get('/account-check', [CheckAccountController::class, 'show'])->name('account.account-check');
        Route::post('/account-check', [CheckAccountController::class, 'check'])->name('account.account-check.submit');
        Route::get('/register', [AuthController::class, 'register'])->name('account.register');
        Route::get('/login', [LoginController::class, 'show'])->name('account.login');
        Route::post('/login', [LoginController::class, 'login'])->name('account.login.submit');
        Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('account.forgot-password');
        Route::post('/forgot-password', [AuthController::class, 'handleForgotPassword'])->name('account.forgot-password.submit');
    });
    // Reset password routes (must be accessible even if logged in as another user)
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

    // Logout route (accessible to both authenticated and guest users)
    Route::post('/logout', [AuthController::class, 'logout'])->name('account.logout');
    // Protected routes (only for authenticated users)
    Route::middleware('auth')->group(function () {
        Route::get('/', [AuthController::class, 'account'])->name('account.index');
    });
});
// Register routes (supports both GET and POST)
Route::get('/register', [AuthController::class, 'register'])
    ->middleware('guest')
    ->name('register');
Route::post('/register', [RegisterController::class, 'store'])
    ->middleware('guest')
    ->name('register.store');
// Canonical login route (required by Laravel auth middleware - redirects here when unauthenticated)
Route::get('/login', [LoginController::class, 'show'])->middleware('guest')->name('login');
Route::post('/guest-login', [GuestLoginController::class, 'store'])->name('guest.login');
Route::post('/guest-checkout', [GuestCheckoutController::class, 'store'])->name('guest.checkout');

// =========================
// TICKET ROUTES
// =========================
Route::prefix('tickets')->group(function () {
    Route::get('/', [TicketController::class, 'index'])->name('ticket.index');
    Route::get('/{schedule}', [TicketController::class, 'show'])->name('ticket.select');
    Route::post('/scan', [TicketController::class, 'scan'])->name('ticket.scan')->middleware('admin');
});
Route::get('/admission', [TicketController::class, 'index'])
    ->name('ticket.admission')
    ->middleware('user.or.guest');
Route::match(['get', 'post'], '/cart', [CartController::class, 'index'])->name('ticket.cart')->middleware(['no.cache']);
Route::delete('/cart/group/{id}', [CartController::class, 'removeGroup'])->name('cart.group.remove');
Route::get('/cart/group/{id}/modify', [CartController::class, 'modifyGroup'])->name('cart.group.modify');
Route::get('/cart/modify/cancel', [CartController::class, 'cancelModify'])->name('cart.modify.cancel');
Route::post('/cart/add', [CartController::class, 'add'])->name('ticket.add');
Route::post('/admission/cart/store', [CartController::class, 'storeAdmission'])->name('admission.cart.store');
// Fallback for missing checkout GET route to prevent RouteNotFoundException
Route::get('/checkout', function () {
    $cartItems = collect();
    $customer  = ['name' => '', 'email' => ''];
    return view('ordinary.checkout.form', compact('cartItems', 'customer'));
})->name('ticket.checkout')->middleware('user.or.guest');
Route::post('/checkout', [CheckoutController::class, 'checkout'])->name('ticket.checkout.process')->middleware('user.or.guest');
Route::get('/checkout/payments/{order}', [CheckoutController::class, 'paymentPage'])->name('checkout.payments')->middleware(['no.cache', 'user.or.guest']);
Route::post('/checkout/pay/{order}', [CheckoutController::class, 'pay'])->name('ticket.checkout.pay')->middleware('user.or.guest');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('ticket.checkout.success')->middleware(['no.cache', 'user.or.guest']);

// =========================
// MEMBERSHIP ROUTES
// =========================
Route::prefix('members')->group(function () {
    Route::get('/membership', [MembershipController::class, 'index'])->name('membership.index');
    Route::get('/membership/{id}', [MembershipController::class, 'show'])->name('membership.show');
});
// Purchase requires authentication
Route::post('/members/membership/purchase', [MembershipController::class, 'purchase'])
    ->middleware('auth')
    ->name('membership.purchase');

Route::prefix('member')->group(function () {
    Route::get('/add-member', [MembershipController::class, 'addMember'])
        ->middleware(['no.cache', 'user.or.guest'])
        ->name('member.add-member');

    Route::post('/add-member', [MembershipController::class, 'purchase'])
        ->middleware('user.or.guest')
        ->name('member.add-member.submit');

    // Activation link from confirmation email — must be public (no auth)
    Route::get('/activate/{token}', [MembershipController::class, 'activate'])
        ->name('member.activate');
});

// =========================
// LEGACY COMPATIBILITY ROUTES
// =========================
Route::get('/art/{slug}', [ArtWorkController::class, 'show'])->name('artwork.show');
Route::get('/order/create', [OrderController::class, 'create'])->name('order.create');
Route::post('/order/store', [OrderController::class, 'store'])->name('order.store');
Route::get('/order/show', [OrderController::class, 'index'])->name('order.show');
Route::get('/order/show/{order}', [OrderController::class, 'show'])->name('order.show.detail');

// =========================
// ADMIN ROUTES (Modern Admin Skeleton)
// =========================
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Cashier, Admin, Superadmin Routes
    Route::middleware(['role:cashier,admin,superadmin'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        // Orders & Tickets (Operational)
        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index');
        Route::post('/tickets/checkout', [AdminTicketController::class, 'checkout'])->name('tickets.checkout');
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::post('/orders/search-ticket', [AdminOrderController::class, 'searchTicket'])->name('orders.search-ticket');
        Route::post('/orders/validate-ticket', [AdminOrderController::class, 'validateTicket'])->name('orders.validate-ticket');
        Route::post('orders/{order}/cancel', [AdminOrderController::class, 'cancel'])->name('orders.cancel');
        Route::resource('orders', AdminOrderController::class);

        // Payments
        Route::prefix('payment')->name('payment.')->group(function () {
            Route::get('/', [AdminPaymentController::class, 'index'])->name('index');
            Route::get('/data', [AdminPaymentController::class, 'getData'])->name('data');
            Route::post('/{payment}/refund', [AdminPaymentController::class, 'refund'])->name('refund');
        });
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    });

    // Admin & Superadmin Routes (Master Data & Analytics)
    Route::middleware(['role:admin,superadmin'])->group(function () {
        Route::get('/tickets/management', [AdminTicketController::class, 'management'])->name('tickets.management');
        
        // Dynamic Ticket Type CRUD
        Route::post('/tickets/types', [AdminTicketController::class, 'storeType'])->name('tickets.types.store');
        Route::put('/tickets/types/{id}', [AdminTicketController::class, 'updateType'])->name('tickets.types.update');
        Route::delete('/tickets/types/{id}', [AdminTicketController::class, 'deleteType'])->name('tickets.types.destroy');

        // Dynamic Ticket Stock management
        Route::post('/tickets/stock', [AdminTicketController::class, 'addStock'])->name('tickets.stock.add');
        Route::post('/tickets/stock/update', [AdminTicketController::class, 'updateStock'])->name('tickets.stock.update');
        
        // Tickets API endpoints for dynamic data
        Route::get('/api/tickets/available-dates', [AdminTicketController::class, 'getAvailableDates'])->name('api.tickets.available-dates');
        Route::get('/api/tickets/types-for-date/{visitScheduleId}', [AdminTicketController::class, 'getTicketTypesForDate'])->name('api.tickets.types-for-date');
        
        Route::get('/exhibitions', [AdminExhibitionController::class, 'index'])->name('exhibitions.index');
        Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');

        // ==== CRUD RESOURCE ROUTES FOR MASTER DATA ====
        // Departments
        Route::resource('departments', DepartmentController::class);

        // Object Types
        Route::resource('object-types', ObjectTypeController::class);

        // Classifications
        Route::resource('classifications', ClassificationController::class);

        // Locations
        Route::resource('locations', LocationController::class);

        // Repositories
        Route::resource('repositories', RepositoryController::class);

        // Materials
        Route::resource('materials', MaterialController::class);

        // Mediums
        Route::resource('mediums', MediumController::class);

        // Tags
        Route::resource('tags', TagController::class);

        // Cultures
        Route::resource('cultures', CultureController::class);

        // Periods
        Route::resource('periods', PeriodController::class);

        // Dynasties
        Route::resource('dynasties', DynastyController::class);

        // Reigns
        Route::resource('reigns', ReignController::class);

        // Portfolios
        Route::resource('portfolios', PortfolioController::class);

        // Constituents (Artists)
        Route::resource('constituents', ConstituentController::class);

        // Artworks
        Route::post('artworks/{id}/restore', [AdminArtworkController::class, 'restore'])->name('artworks.restore');
        Route::resource('artworks', AdminArtworkController::class);

        // Ticket Analytics Dashboard
        Route::prefix('ticket-analytics')->name('ticket-analytics.')->group(function () {
            Route::get('/', [TicketAnalyticsController::class, 'index'])->name('index');
            Route::get('/data', [TicketAnalyticsController::class, 'getAnalyticsData'])->name('data');
        });

        // Users (View Only for Admin)
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{id}/edit', [AdminUserController::class, 'edit'])->name('users.edit');

        // Emotion Categories & Visitor Stories Moderation
        Route::resource('categories', AdminCategoryController::class)->except(['edit', 'update']);
        Route::resource('posts', AdminPostController::class)->only(['index', 'destroy']);
    });

    // Superadmin Only Routes
    Route::middleware(['role:superadmin'])->group(function () {
        Route::delete('artworks/{id}/force-delete', [AdminArtworkController::class, 'forceDelete'])->name('artworks.force-delete');
        Route::put('/users/{id}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{id}/restore', [AdminUserController::class, 'restore'])->name('users.restore');
    });
});
Route::get('/force-logout', function () {
    Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return 'logged out';
});
Route::get('/visit-guides/accessibility', function () {
    return view('ordinary.plan-your-visit.accessibility.accessibility');
})->name('visit.accessibility');
Route::get('/visit-guides/our-experience', [OurExperienceController::class, 'index'])->name('visit.our-experience');
Route::post('/visit-guides/our-experience', [OurExperienceController::class, 'store'])->name('visit.our-experience.store')->middleware('auth');
Route::get('/member/membership', function () {
    return view('ordinary.member.membership.membership');
})->name('member.membership');
Route::get('/plan-your-visit/fifth/learn-more', function () {
    return view('ordinary.plan-your-visit.fifth.learn-more');
})->name('learn.more');
Route::get('/plan-your-visit/cloister/learn-more', function () {
    return view('ordinary.plan-your-visit.cloister.learn-more');
})->name('cloister.learn.more');
Route::get('/plan-your-visit/accessibility/cloisters', function () {
    return view('ordinary.plan-your-visit.accessibility.accessibility-cloisters');
})->name('accessibility.cloisters');
Route::get('/plan-your-visit/accessibility', function () {
    return view('ordinary.plan-your-visit.accessibility.accessibility');
})->name('accessibility.main');
Route::get('/admin-preview', function () {
    return view('admin.dashboard.index');
});
