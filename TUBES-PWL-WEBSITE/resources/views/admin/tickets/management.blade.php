@extends('admin.layout.layout')

@section('admin-title')
    Ticket Stock Management
@endsection

@section('admin-content')
<div class="admin-page-section">
    <div class="page-header">
        <h1>Ticket Stock Management</h1>
        <p class="page-subtitle">Add stock, manage prices, and ticket types</p>
    </div>

    <!-- Dynamic Session Alerts -->
    @if(session('success'))
    <div style="background-color: #e6f4ea; color: #137333; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #c3e6cb; font-weight: 500;">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background-color: #fce8e6; color: #c5221f; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #fadbd8; font-weight: 500;">
        {{ session('error') }}
    </div>
    @endif
    @if($errors->any())
    <div style="background-color: #fce8e6; color: #c5221f; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #fadbd8; font-weight: 500;">
        <ul style="margin: 0; padding-left: 1.25rem;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Quick Stats -->
    <div class="quick-stats-grid">
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Total Stock',
            'value' => $totalStock ?? 0,
            'icon' => '📦',
            'trend' => 'tickets',
            'color' => 'primary'
        ])
        
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Tickets Sold',
            'value' => $ticketsSold ?? 0,
            'icon' => '✓',
            'trend' => 'this month',
            'color' => 'success'
        ])
        
        @include('admin.ticket-analytics.components.stat-card', [
            'title' => 'Available',
            'value' => $availableStock ?? 0,
            'icon' => '✓',
            'trend' => 'in stock',
            'color' => 'info'
        ])
    </div>

    <!-- Ticket Types Management -->
    <section class="management-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 class="section-title" style="margin: 0;">Ticket Types & Prices</h2>
            <button class="btn btn-primary" onclick="openAddTypeModal()">+ Add Ticket Type</button>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ticket Type</th>
                        <th>Price</th>
                        <th>Member Discount</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ticketTypes ?? [] as $type)
                    <tr>
                        <td><strong>{{ $type->ticket_type_name }}</strong></td>
                        <td>${{ number_format($type->base_price, 2) }}</td>
                        <td>
                            @if($type->is_membership_discount_active)
                                <span style="color: #137333; font-weight: bold;">Yes</span> 
                                ({{ $type->membership_discount_type == 'percentage' ? $type->membership_discount_value . '%' : '$' . number_format($type->membership_discount_value, 2) }})
                            @else
                                <span style="color: #c5221f;">No</span>
                            @endif
                        </td>
                        <td>Museum admission</td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td class="actions">
                            <button class="action-btn" onclick="openEditTypeModal({{ $type->ticket_type_id }}, '{{ addslashes($type->ticket_type_name) }}', {{ $type->base_price }}, {{ $type->is_membership_discount_active ? 'true' : 'false' }}, '{{ $type->membership_discount_type }}', {{ $type->membership_discount_value }})">Edit</button>
                            <form action="{{ route('admin.tickets.types.destroy', $type->ticket_type_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this ticket type?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn" style="color: #c5221f; border-color: rgba(197, 34, 31, 0.2);">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem;">No ticket types available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Real Stock Selection & Management -->
    <section class="management-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <h2 class="section-title" style="margin: 0;">Real Ticket Stock</h2>
            <form method="GET" action="{{ route('admin.tickets.management') }}" id="scheduleFilterForm">
                <label style="font-weight: 600; margin-right: 0.5rem; color: #444;">Select Date & Venue:</label>
                <select name="schedule_id" onchange="document.getElementById('scheduleFilterForm').submit()" class="form-input" style="padding: 0.5rem 2rem 0.5rem 1rem; font-weight: 600; border-color: #2196F3; cursor: pointer;">
                    @foreach($schedules ?? [] as $sch)
                    <option value="{{ $sch->visit_schedule_id }}" @if($selectedSchedule && $selectedSchedule->visit_schedule_id == $sch->visit_schedule_id) selected @endif>
                        {{ $sch->visit_date->format('M d, Y') }} — {{ $sch->location->location_name ?? 'Unknown Location' }}
                    </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ticket Type</th>
                        <th>Base Price</th>
                        <th>Total Stock</th>
                        <th>Remaining</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($availabilities ?? [] as $avail)
                    <tr>
                        <td><strong>{{ $avail['name'] }}</strong></td>
                        <td>${{ number_format($avail['price'], 2) }}</td>
                        <td><strong>{{ $avail['total_stock'] }}</strong></td>
                        <td>
                            @if($avail['remaining'] > 20)
                            <strong style="color: #137333;">{{ $avail['remaining'] }} left</strong>
                            @elseif($avail['remaining'] >= 1)
                            <strong style="color: #b06000;">{{ $avail['remaining'] }} left</strong>
                            @else
                            <strong style="color: #c5221f;">Sold Out</strong>
                            @endif
                        </td>
                        <td>
                            @if($avail['status'] === 'Available')
                            <span class="status-badge" style="background-color: #e6f4ea; color: #137333; display: inline-flex; align-items: center; gap: 0.25rem;">
                                ✓ Available
                            </span>
                            @elseif($avail['status'] === 'Low Stock')
                            <span class="status-badge" style="background-color: #fef7e0; color: #b06000; display: inline-flex; align-items: center; gap: 0.25rem;">
                                ⚠ Low Stock
                            </span>
                            @else
                            <span class="status-badge" style="background-color: #fce8e6; color: #c5221f; display: inline-flex; align-items: center; gap: 0.25rem;">
                                ✗ Sold Out
                            </span>
                            @endif
                        </td>
                        <td class="actions">
                            <button class="action-btn" onclick="openUpdateStockModal({{ $avail['ticket_availability_id'] }}, '{{ addslashes($avail['name']) }}', '{{ addslashes($selectedSchedule ? $selectedSchedule->visit_date->format('M d, Y') : '') }}', {{ $avail['total_stock'] }})">Update Stock</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">No ticket types configured for this schedule</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Add Stock Form -->
    <section class="management-section">
        <h2 class="section-title">Add Stock</h2>
        <form action="{{ route('admin.tickets.stock.add') }}" method="POST" class="add-stock-form">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Date</label>
                    <input type="date" name="date" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Museum Location</label>
                    <select class="form-input" name="location_id" required>
                        <option value="">-- Select Location --</option>
                        @foreach($locations ?? [] as $loc)
                        <option value="{{ $loc->location_id }}">{{ $loc->location_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Ticket Type</label>
                    <select class="form-input" name="ticket_type_id" required>
                        <option value="">-- Select Ticket Type --</option>
                        @forelse($ticketTypes ?? [] as $type)
                        <option value="{{ $type->ticket_type_id }}">{{ $type->ticket_type_name }} (${{ number_format($type->base_price, 2) }})</option>
                        @empty
                        <option disabled>No ticket types available</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-input" min="1" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Add Stock</button>
        </form>
    </section>
</div>

<!-- ==============================================
     MODAL POPUPS
     ============================================== -->

<!-- Add Ticket Type Modal -->
<div id="addTypeModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center; backdrop-filter: blur(4px);">
    <div style="background:white; padding:2rem; border-radius:8px; width:450px; box-shadow:0 4px 20px rgba(0,0,0,0.15);">
        <h3 style="margin-top:0; margin-bottom:1.5rem; font-size:1.25rem; font-weight:600; color:#333;">+ Add New Ticket Type</h3>
        <form action="{{ route('admin.tickets.types.store') }}" method="POST">
            @csrf
            <div style="margin-bottom:1rem;">
                <label style="display:block; font-weight:600; margin-bottom:0.5rem; color:#444;">Ticket Type Name</label>
                <input type="text" name="ticket_type_name" class="form-input" style="width:100%; box-sizing:border-box;" required placeholder="e.g. VIP, Premium">
            </div>
            <div style="margin-bottom:1.5rem;">
                <label style="display:block; font-weight:600; margin-bottom:0.5rem; color:#444;">Base Price ($)</label>
                <input type="number" name="base_price" step="0.01" min="0" class="form-input" style="width:100%; box-sizing:border-box;" required placeholder="0.00">
            </div>
            <div style="margin-bottom:1.5rem; border-top: 1px solid #eee; padding-top: 1rem;">
                <label style="display:flex; align-items:center; font-weight:600; margin-bottom:0.5rem; color:#444;">
                    <input type="checkbox" name="is_membership_discount_active" value="1" style="margin-right: 0.5rem; transform: scale(1.2);">
                    Enable Membership Discount
                </label>
            </div>
            <div style="display:flex; gap: 1rem; margin-bottom:1.5rem;">
                <div style="flex:1;">
                    <label style="display:block; font-weight:600; margin-bottom:0.5rem; color:#444;">Discount Type</label>
                    <select name="membership_discount_type" class="form-input" style="width:100%; box-sizing:border-box;">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount ($)</option>
                    </select>
                </div>
                <div style="flex:1;">
                    <label style="display:block; font-weight:600; margin-bottom:0.5rem; color:#444;">Discount Value</label>
                    <input type="number" name="membership_discount_value" step="0.01" min="0" class="form-input" style="width:100%; box-sizing:border-box;" placeholder="0">
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" class="btn" style="background:#eee; color:#333; padding: 0.5rem 1rem; border-radius: 4px; border: none; cursor: pointer;" onclick="closeAddTypeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Save Ticket Type</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Ticket Type Modal -->
<div id="editTypeModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center; backdrop-filter: blur(4px);">
    <div style="background:white; padding:2rem; border-radius:8px; width:450px; box-shadow:0 4px 20px rgba(0,0,0,0.15);">
        <h3 style="margin-top:0; margin-bottom:1.5rem; font-size:1.25rem; font-weight:600; color:#333;">Edit Ticket Type</h3>
        <form id="editTypeForm" method="POST">
            @csrf
            @method('PUT')
            <div style="margin-bottom:1rem;">
                <label style="display:block; font-weight:600; margin-bottom:0.5rem; color:#444;">Ticket Type Name</label>
                <input type="text" id="edit_ticket_type_name" name="ticket_type_name" class="form-input" style="width:100%; box-sizing:border-box;" required>
            </div>
            <div style="margin-bottom:1.5rem;">
                <label style="display:block; font-weight:600; margin-bottom:0.5rem; color:#444;">Base Price ($)</label>
                <input type="number" id="edit_base_price" name="base_price" step="0.01" min="0" class="form-input" style="width:100%; box-sizing:border-box;" required>
            </div>
            <div style="margin-bottom:1.5rem; border-top: 1px solid #eee; padding-top: 1rem;">
                <label style="display:flex; align-items:center; font-weight:600; margin-bottom:0.5rem; color:#444;">
                    <input type="checkbox" id="edit_is_membership_discount_active" name="is_membership_discount_active" value="1" style="margin-right: 0.5rem; transform: scale(1.2);">
                    Enable Membership Discount
                </label>
            </div>
            <div style="display:flex; gap: 1rem; margin-bottom:1.5rem;">
                <div style="flex:1;">
                    <label style="display:block; font-weight:600; margin-bottom:0.5rem; color:#444;">Discount Type</label>
                    <select id="edit_membership_discount_type" name="membership_discount_type" class="form-input" style="width:100%; box-sizing:border-box;">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed Amount ($)</option>
                    </select>
                </div>
                <div style="flex:1;">
                    <label style="display:block; font-weight:600; margin-bottom:0.5rem; color:#444;">Discount Value</label>
                    <input type="number" id="edit_membership_discount_value" name="membership_discount_value" step="0.01" min="0" class="form-input" style="width:100%; box-sizing:border-box;" placeholder="0">
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" class="btn" style="background:#eee; color:#333; padding: 0.5rem 1rem; border-radius: 4px; border: none; cursor: pointer;" onclick="closeEditTypeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Update Stock Modal -->
<div id="updateStockModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; justify-content:center; align-items:center; backdrop-filter: blur(4px);">
    <div style="background:white; padding:2rem; border-radius:8px; width:450px; box-shadow:0 4px 20px rgba(0,0,0,0.15);">
        <h3 style="margin-top:0; margin-bottom:0.5rem; font-size:1.25rem; font-weight:600; color:#333;">Update Stock Quantity</h3>
        <p id="updateStockSubtitle" style="margin-top:0; margin-bottom:1.5rem; color:#666; font-size:0.9rem; font-weight: 500;"></p>
        <form action="{{ route('admin.tickets.stock.update') }}" method="POST">
            @csrf
            <input type="hidden" id="update_ticket_availability_id" name="ticket_availability_id">
            
            <div style="margin-bottom:1.5rem;">
                <label style="display:block; font-weight:600; margin-bottom:0.5rem; color:#444;">Stock Capacity</label>
                <input type="number" id="update_capacity_limit" name="capacity_limit" min="0" class="form-input" style="width:100%; box-sizing:border-box;" required>
            </div>

            <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" class="btn" style="background:#eee; color:#333; padding: 0.5rem 1rem; border-radius: 4px; border: none; cursor: pointer;" onclick="closeUpdateStockModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddTypeModal() {
    document.getElementById('addTypeModal').style.display = 'flex';
}
function closeAddTypeModal() {
    document.getElementById('addTypeModal').style.display = 'none';
}

function openEditTypeModal(id, name, price, hasDiscount, discountType, discountValue) {
    const form = document.getElementById('editTypeForm');
    form.action = `/admin/tickets/types/${id}`;
    document.getElementById('edit_ticket_type_name').value = name;
    document.getElementById('edit_base_price').value = price;
    document.getElementById('edit_is_membership_discount_active').checked = hasDiscount;
    document.getElementById('edit_membership_discount_type').value = discountType || 'percentage';
    document.getElementById('edit_membership_discount_value').value = discountValue || 0;
    document.getElementById('editTypeModal').style.display = 'flex';
}
function closeEditTypeModal() {
    document.getElementById('editTypeModal').style.display = 'none';
}

function openUpdateStockModal(availabilityId, typeName, dateText, currentStock) {
    document.getElementById('update_ticket_availability_id').value = availabilityId;
    document.getElementById('updateStockSubtitle').innerText = typeName + ' — ' + dateText;
    document.getElementById('update_capacity_limit').value = currentStock;
    document.getElementById('updateStockModal').style.display = 'flex';
}
function closeUpdateStockModal() {
    document.getElementById('updateStockModal').style.display = 'none';
}
</script>

<style>
.admin-page-section {
    max-width: 1200px;
    margin: 0 auto;
}

.page-header {
    margin-bottom: 2rem;
}

.page-header h1 {
    font-size: 1.75rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
}

.page-subtitle {
    font-size: 0.95rem;
    color: #666;
    margin: 0;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.quick-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.management-section {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.table-wrapper {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    background-color: #f5f5f5;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: #333;
    border-bottom: 2px solid #e0e0e0;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #e0e0e0;
}

.data-table tbody tr:hover {
    background-color: #f9f9f9;
}

.status-badge {
    padding: 0.35rem 0.75rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-active {
    background-color: #d4edda;
    color: #155724;
}

.status-warning {
    background-color: #fef7e0;
    color: #b06000;
}

.status-inactive {
    background-color: #fce8e6;
    color: #c5221f;
}

.actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.action-btn {
    padding: 0.4rem 0.8rem;
    border: 1px solid #ddd;
    background: white;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.8rem;
    transition: all 0.3s ease;
}

.action-btn:hover {
    border-color: #2196F3;
    color: #2196F3;
}

.add-stock-form {
    background-color: #f9f9f9;
    padding: 1.5rem;
    border-radius: 6px;
    border: 1px solid #eee;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}

.form-input {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.95rem;
}

.form-input:focus {
    outline: none;
    border-color: #2196F3;
    box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.1);
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #2196F3;
    color: white;
}

.btn-primary:hover {
    background-color: #1976D2;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>
@endsection