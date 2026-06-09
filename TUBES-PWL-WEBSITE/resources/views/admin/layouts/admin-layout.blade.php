
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>@yield('title', 'Admin')</title>
	@vite([
		'resources/css/admin/layouts/admin-layout.css',
		'resources/css/admin/components/navbar/navbar.css',
		'resources/css/admin/components/sidebar/sidebar.css',
		'resources/css/admin/components/footer/footer.css',
		'resources/css/admin/components/cards/skeleton-card.css',
		'resources/css/admin/components/toolbar/page-toolbar.css',
		'resources/css/admin/components/toolbar/breadcrumbs.css',
		'resources/css/admin/components/toolbar/quick-actions.css',
		'resources/css/admin/components/icon-placeholder.css',
		'resources/css/admin/components/navbar/notification-shell.css'
	])
	@stack('styles')
</head>
<body class="admin-body">
	<div class="admin-shell">
		@include('admin.components.navbar.navbar')

		<div class="admin-shell__body">
			@include('admin.components.sidebar.sidebar')

			<main class="admin-content">
				@yield('content')
			</main>
		</div>

		@include('admin.components.footer.footer')
	</div>
</body>
</html>
