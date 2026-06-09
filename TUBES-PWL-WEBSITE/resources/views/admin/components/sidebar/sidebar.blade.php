{{--
	Icon Placeholder Usage:
	<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
	Use for sidebar nav icons, scalable and themeable.
--}}

<aside class="admin-sidebar" aria-label="Primary navigation" tabindex="-1" role="navigation">
	<div class="admin-sidebar__header">
		<span class="admin-sidebar__label">Navigation</span>
	</div>
	<nav class="admin-sidebar__nav" aria-label="Sidebar sections">
		<div class="admin-sidebar__group" aria-label="Core">
			<span class="admin-sidebar__group-label">Core</span>
			<a class="admin-sidebar__link is-active" href="#" tabindex="0" aria-current="page">
				<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
				<span class="admin-sidebar__text">Dashboard</span>
			</a>
			<a class="admin-sidebar__link" href="#" tabindex="0">
				<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
				<span class="admin-sidebar__text">Tickets</span>
			</a>
			<a class="admin-sidebar__link" href="#" tabindex="0">
				<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
				<span class="admin-sidebar__text">Orders</span>
			</a>
			<a class="admin-sidebar__link" href="#" tabindex="0">
				<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
				<span class="admin-sidebar__text">Payments</span>
			</a>
		</div>
		@if(auth()->user()->hasRole(['admin', 'superadmin']))
		<div class="admin-sidebar__group" aria-label="Collection">
			<span class="admin-sidebar__group-label">Collection</span>
			<a class="admin-sidebar__link" href="#" tabindex="0">
				<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
				<span class="admin-sidebar__text">Users</span>
			</a>
			<a class="admin-sidebar__link" href="#" tabindex="0">
				<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
				<span class="admin-sidebar__text">Artworks</span>
			</a>
			<a class="admin-sidebar__link" href="#" tabindex="0">
				<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
				<span class="admin-sidebar__text">Exhibitions</span>
			</a>
		</div>
		<div class="admin-sidebar__group" aria-label="Insights">
			<span class="admin-sidebar__group-label">Insights</span>
			<a class="admin-sidebar__link" href="#" tabindex="0">
				<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
				<span class="admin-sidebar__text">Analytics</span>
			</a>
			@if(auth()->user()->isSuperAdmin())
			<a class="admin-sidebar__link" href="#" tabindex="0">
				<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
				<span class="admin-sidebar__text">Settings</span>
			</a>
			@endif
		</div>
		@endif
	</nav>
</aside>
