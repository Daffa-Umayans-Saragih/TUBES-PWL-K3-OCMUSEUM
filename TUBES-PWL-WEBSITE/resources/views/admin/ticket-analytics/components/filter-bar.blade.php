<!-- Filter Bar Component -->
<div class="filter-bar-container">
    <div class="filter-bar">
        <form method="GET" action="{{ route('admin.ticket-analytics.index') }}" class="filter-form">
            <div class="filter-group">
                <label for="start_date" class="filter-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="filter-input" value="{{ $startDate }}">
            </div>
            
            <div class="filter-group">
                <label for="end_date" class="filter-label">End Date</label>
                <input type="date" id="end_date" name="end_date" class="filter-input" value="{{ $endDate }}">
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-filter-apply">🔍 Apply Filters</button>
                <a href="{{ route('admin.ticket-analytics.index') }}" class="btn-filter-reset">↻ Reset</a>
            </div>
        </form>
    </div>
</div>
