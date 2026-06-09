
@extends('admin.layout.layout')

@push('styles')
@vite('resources/css/admin/settings/index.css')
@endpush

@section('admin-title')
    Settings
@endsection

@section('admin-content')
<section class="admin-section admin-section--settings admin-page">
	@include('admin.components.toolbar.page-toolbar', [
		'title' => 'Settings',
		'subtitle' => 'Configure admin preferences and system defaults.',
		'breadcrumbs' => [
			['label' => 'Dashboard', 'href' => '#'],
			['label' => 'Settings', 'isCurrent' => true],
		],
		'status' => ['label' => 'Warning', 'tone' => 'warning'],
		'badges' => [
			['label' => 'System', 'tone' => 'neutral'],
			['label' => 'Configuration', 'tone' => 'accent'],
		],
		'actions' => [
			['label' => 'Reset', 'variant' => 'warning'],
			['label' => 'Save Changes', 'variant' => 'primary'],
		],
	])

	<div class="admin-settings admin-grid admin-grid--main">
		<aside class="admin-panel admin-settings__sidebar">
			<div class="admin-panel__header">
				<h3 class="admin-panel__title">Settings Areas</h3>
				<span class="admin-panel__meta">Navigation</span>
			</div>
			<div class="admin-panel__body admin-list">
				<div class="admin-list__item is-active">General</div>
				<div class="admin-list__item">Access</div>
				<div class="admin-list__item">Payments</div>
				<div class="admin-list__item">Notifications</div>
				<div class="admin-list__item">Integrations</div>
			</div>
		</aside>
		<div class="admin-settings__content admin-stack">
			<div class="admin-panel">
				<div class="admin-panel__header">
					<h3 class="admin-panel__title">General Settings</h3>
					<span class="admin-panel__meta">Placeholder form</span>
				</div>
				<div class="admin-panel__body">
					<div class="admin-form-grid">
						<div class="admin-field"></div>
						<div class="admin-field"></div>
						<div class="admin-field admin-field--tall"></div>
					</div>
				</div>
			</div>
			<div class="admin-grid admin-grid--two">
				<div class="admin-panel">
					<div class="admin-panel__header">
						<h3 class="admin-panel__title">System Settings</h3>
						<span class="admin-panel__meta">Placeholder blocks</span>
					</div>
					<div class="admin-panel__body">
						<div class="admin-placeholder-block"></div>
					</div>
				</div>
				<div class="admin-panel">
					<div class="admin-panel__header">
						<h3 class="admin-panel__title">Payment Settings</h3>
						<span class="admin-panel__meta">Placeholder blocks</span>
					</div>
					<div class="admin-panel__body">
						<div class="admin-placeholder-block"></div>
					</div>
				</div>
			</div>
			<div class="admin-panel">
				<div class="admin-panel__header">
					<h3 class="admin-panel__title">Configuration Blocks</h3>
					<span class="admin-panel__meta">Placeholder layout</span>
				</div>
				<div class="admin-panel__body">
					<div class="admin-placeholder-block admin-placeholder-block--tall"></div>
				</div>
			</div>
		</div>
	</div>
</section>
@endsection
