{{--
	Icon Placeholder Usage:
	<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
	Use for navbar icons, notification shell, and user avatar placeholder.
	Notification Shell Structure:
	- .admin-navbar__notification: wraps notification button and dropdown
	- .admin-navbar__badge: unread indicator
	- .admin-navbar__dropdown: dropdown shell (placeholder only, no JS)
	- .admin-notification: notification preview with icon placeholder
--}}

<nav class="admin-navbar" aria-label="Admin">
	<div class="admin-navbar__brand">
		<span class="admin-navbar__logo">MET Museum</span>
		<span class="admin-navbar__title">Admin Console</span>
	</div>
	<div class="admin-navbar__actions">
		<button class="admin-navbar__button admin-navbar__button--icon" type="button">
			<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
			<span>Search</span>
		</button>
		<div class="admin-navbar__notification">
			<button class="admin-navbar__button admin-navbar__button--icon" type="button">
				<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
				<span>Notifications</span>
				<span class="admin-navbar__badge">3</span>
			</button>
			<div class="admin-navbar__dropdown">
				<div class="admin-navbar__dropdown-header">
					<span>Notifications</span>
					<span class="badge badge--info">3 New</span>
				</div>
				<div class="admin-navbar__dropdown-body">
					<div class="admin-notification">
						<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
						<div class="admin-notification__content">
							<div class="admin-notification__title">Payment review</div>
							<div class="admin-notification__meta">2 minutes ago</div>
						</div>
					</div>
					<div class="admin-notification">
						<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
						<div class="admin-notification__content">
							<div class="admin-notification__title">New ticket request</div>
							<div class="admin-notification__meta">Today</div>
						</div>
					</div>
					<div class="admin-notification">
						<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
						<div class="admin-notification__content">
							<div class="admin-notification__title">Exhibition update</div>
							<div class="admin-notification__meta">Yesterday</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="admin-navbar__user">
			<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
			<span>Admin</span>
		</div>
	</div>
</nav>
