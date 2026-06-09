
@extends('admin.layout.layout')

@push('styles')
@vite('resources/css/admin/reports/index.css')
@endpush

@section('admin-title')
    Reports
@endsection

@section('admin-content')
<section class="admin-section admin-section--reports admin-page">
	@include('admin.components.toolbar.page-toolbar', [
		'title' => 'Reports',
		'subtitle' => 'Generate and review operational reports.',
		'breadcrumbs' => [
			['label' => 'Dashboard', 'href' => '#'],
			['label' => 'Reports', 'isCurrent' => true],
		],
		'status' => ['label' => 'Draft', 'tone' => 'draft'],
		'badges' => [
			['label' => 'Exports', 'tone' => 'neutral'],
			['label' => 'Summary', 'tone' => 'info'],
		],
		'filters' => ['Weekly', 'Monthly', 'Annual'],
		'actions' => [
			['label' => 'Export', 'variant' => 'secondary'],
			['label' => 'Generate Report', 'variant' => 'primary'],
		],
	])

	<div class="reports-summary admin-grid admin-grid--cards">
		@include('admin.components.cards.stat-card')
		@include('admin.components.cards.stat-card')
		@include('admin.components.cards.skeleton-card')
	</div>

	<div class="admin-section__body admin-stack">
		<div class="reports-cards admin-grid admin-grid--three">
			<div class="admin-panel">
				<div class="admin-panel__header">
					<h3 class="admin-panel__title">Weekly Report</h3>
					<span class="admin-panel__meta">Placeholder card</span>
				</div>
				<div class="admin-panel__body">
					<div class="admin-placeholder-block admin-placeholder-block--skeleton">
						<div class="skeleton skeleton--title"></div>
						<div class="skeleton skeleton--text"></div>
					</div>
				</div>
			</div>
			<div class="admin-panel">
				<div class="admin-panel__header">
					<h3 class="admin-panel__title">Monthly Report</h3>
					<span class="admin-panel__meta">Placeholder card</span>
				</div>
				<div class="admin-panel__body">
					<div class="admin-placeholder-block admin-placeholder-block--skeleton">
						<div class="skeleton skeleton--title"></div>
						<div class="skeleton skeleton--text"></div>
					</div>
				</div>
			</div>
			<div class="admin-panel">
				<div class="admin-panel__header">
					<h3 class="admin-panel__title">Annual Report</h3>
					<span class="admin-panel__meta">Placeholder card</span>
				</div>
				<div class="admin-panel__body">
					<div class="admin-placeholder-block admin-placeholder-block--skeleton">
						<div class="skeleton skeleton--title"></div>
						<div class="skeleton skeleton--text"></div>
					</div>
				</div>
			</div>
		</div>

		<div class="reports-layout admin-grid admin-grid--main">
			<div class="admin-panel">
				<div class="admin-panel__header">
					<h3 class="admin-panel__title">Report Table</h3>
					<span class="admin-panel__meta">Placeholder list</span>
				</div>
				@include('admin.components.tables.data-table')
			</div>
			<div class="reports-side admin-stack">
				<div class="admin-panel">
					<div class="admin-panel__header">
						<h3 class="admin-panel__title">Export Options</h3>
						<span class="admin-panel__meta">Placeholder actions</span>
					</div>
					<div class="admin-panel__body admin-action-list">
						<button class="admin-button admin-button--ghost" type="button">Download PDF</button>
						<button class="admin-button admin-button--ghost" type="button">Download CSV</button>
						<button class="admin-button admin-button--ghost" type="button">Share</button>
					</div>
				</div>
				<div class="admin-panel">
					<div class="admin-panel__header">
						<h3 class="admin-panel__title">Date Range</h3>
						<span class="admin-panel__meta">Placeholder range</span>
					</div>
					<div class="admin-panel__body">
						<div class="admin-placeholder-block admin-placeholder-block--skeleton">
							<div class="skeleton skeleton--text"></div>
							<div class="skeleton skeleton--text"></div>
						</div>
					</div>
				</div>
				<div class="admin-panel">
					<div class="admin-panel__header">
						<h3 class="admin-panel__title">Summary</h3>
						<span class="admin-panel__meta">Placeholder overview</span>
					</div>
					<div class="admin-panel__body">
						<div class="admin-placeholder-block admin-placeholder-block--skeleton">
							<div class="skeleton skeleton--title"></div>
							<div class="skeleton skeleton--text"></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="admin-empty-state admin-empty-state--compact">
			<div class="admin-empty-state__icon">
				<span class="icon-placeholder icon-placeholder--sm" aria-hidden="true"></span>
			</div>
			<div class="admin-empty-state__content">
				<h3 class="admin-empty-state__title">No reports generated</h3>
				<p class="admin-empty-state__text">Reports will appear here once created.</p>
			</div>
			<button class="admin-button admin-button--ghost" type="button">Create Report</button>
		</div>
	</div>
</section>
@endsection
