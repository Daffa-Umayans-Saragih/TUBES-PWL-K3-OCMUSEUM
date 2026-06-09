@extends('admin.admin')

@section('title', $title)
@section('page_title', $title)

@section('content')
    <div class="admin-section">
        <!-- Breadcrumbs -->
        <div class="admin-breadcrumbs">
            @foreach ($breadcrumbs as $breadcrumb)
                @if ($breadcrumb['isCurrent'] ?? false)
                    <span class="breadcrumb-current">{{ $breadcrumb['label'] }}</span>
                @else
                    <a href="{{ $breadcrumb['href'] }}" class="breadcrumb-link">{{ $breadcrumb['label'] }}</a>
                @endif
            @endforeach
        </div>

        <!-- Header -->
        <div class="admin-form-header">
            <h2 class="admin-title">{{ $title }}</h2>
            <p class="admin-subtitle">{{ $subtitle }}</p>
        </div>

        <!-- Form -->
        <div class="admin-form-container">
            <form action="{{ $isEdit ? route('admin.constituents.update', $constituent) : route('admin.constituents.store') }}" method="POST" class="admin-form">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <!-- Display Name -->
                <div class="form-group">
                    <label for="display_name" class="form-label">Display Name <span class="required">*</span></label>
                    <input type="text" id="display_name" name="display_name" class="form-control @error('display_name') is-invalid @enderror"
                        placeholder="e.g., Leonardo da Vinci"
                        value="{{ old('display_name', $constituent?->display_name ?? '') }}" required>
                    @error('display_name')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Alpha Sort -->
                <div class="form-group">
                    <label for="alpha_sort" class="form-label">Alpha Sort</label>
                    <input type="text" id="alpha_sort" name="alpha_sort" class="form-control @error('alpha_sort') is-invalid @enderror"
                        placeholder="e.g., Vinci, Leonardo da"
                        value="{{ old('alpha_sort', $constituent?->alpha_sort ?? '') }}">
                    @error('alpha_sort')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Display Bio -->
                <div class="form-group">
                    <label for="display_bio" class="form-label">Biography</label>
                    <textarea id="display_bio" name="display_bio" class="form-control @error('display_bio') is-invalid @enderror"
                        placeholder="Enter biography" rows="4">{{ old('display_bio', $constituent?->display_bio ?? '') }}</textarea>
                    @error('display_bio')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Gender -->
                <div class="form-group">
                    <label for="gender" class="form-label">Gender</label>
                    <select id="gender" name="gender" class="form-control @error('gender') is-invalid @enderror">
                        <option value="">-- Select Gender --</option>
                        <option value="Male" {{ old('gender', $constituent?->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender', $constituent?->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Unknown" {{ old('gender', $constituent?->gender ?? '') == 'Unknown' ? 'selected' : '' }}>Unknown</option>
                    </select>
                    @error('gender')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Birth Information -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="birth_year" class="form-label">Birth Year</label>
                        <input type="number" id="birth_year" name="birth_year" class="form-control @error('birth_year') is-invalid @enderror"
                            placeholder="e.g., 1452"
                            value="{{ old('birth_year', $constituent?->birth_year ?? '') }}" min="1000" max="2100">
                        @error('birth_year')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="birth_date_display" class="form-label">Birth Date (Display)</label>
                        <input type="text" id="birth_date_display" name="birth_date_display" class="form-control @error('birth_date_display') is-invalid @enderror"
                            placeholder="e.g., April 15, 1452"
                            value="{{ old('birth_date_display', $constituent?->birth_date_display ?? '') }}">
                        @error('birth_date_display')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Birth Place -->
                <div class="form-group">
                    <label for="birth_place" class="form-label">Birth Place</label>
                    <input type="text" id="birth_place" name="birth_place" class="form-control @error('birth_place') is-invalid @enderror"
                        placeholder="e.g., Vinci, Italy"
                        value="{{ old('birth_place', $constituent?->birth_place ?? '') }}">
                    @error('birth_place')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Death Information -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="death_year" class="form-label">Death Year</label>
                        <input type="number" id="death_year" name="death_year" class="form-control @error('death_year') is-invalid @enderror"
                            placeholder="e.g., 1519"
                            value="{{ old('death_year', $constituent?->death_year ?? '') }}" min="1000" max="2100">
                        @error('death_year')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="death_date_display" class="form-label">Death Date (Display)</label>
                        <input type="text" id="death_date_display" name="death_date_display" class="form-control @error('death_date_display') is-invalid @enderror"
                            placeholder="e.g., May 2, 1519"
                            value="{{ old('death_date_display', $constituent?->death_date_display ?? '') }}">
                        @error('death_date_display')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Death Place -->
                <div class="form-group">
                    <label for="death_place" class="form-label">Death Place</label>
                    <input type="text" id="death_place" name="death_place" class="form-control @error('death_place') is-invalid @enderror"
                        placeholder="e.g., Amboise, France"
                        value="{{ old('death_place', $constituent?->death_place ?? '') }}">
                    @error('death_place')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Nationalities -->
                <div class="form-group">
                    <label for="nationalities" class="form-label">Nationalities</label>
                    <select id="nationalities" name="nationality_ids[]" multiple class="form-control @error('nationality_ids') is-invalid @enderror">
                        @foreach ($nationalities as $nationality)
                            <option value="{{ $nationality->nationality_id }}"
                                {{ in_array($nationality->nationality_id, $selectedNationalities ?? []) ? 'selected' : '' }}>
                                {{ $nationality->nationality_name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text">Hold Ctrl (or Cmd on Mac) to select multiple nationalities</small>
                    @error('nationality_ids')
                        <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>

                <!-- URLs -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="ulan_url" class="form-label">ULAN URL</label>
                        <input type="url" id="ulan_url" name="ulan_url" class="form-control @error('ulan_url') is-invalid @enderror"
                            placeholder="https://example.com"
                            value="{{ old('ulan_url', $constituent?->ulan_url ?? '') }}">
                        @error('ulan_url')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="wikidata_url" class="form-label">Wikidata URL</label>
                        <input type="url" id="wikidata_url" name="wikidata_url" class="form-control @error('wikidata_url') is-invalid @enderror"
                            placeholder="https://example.com"
                            value="{{ old('wikidata_url', $constituent?->wikidata_url ?? '') }}">
                        @error('wikidata_url')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Buttons -->
                <div class="form-group">
                    <div class="form-buttons">
                        <button type="submit" class="btn btn-primary">
                            {{ $isEdit ? 'Update Artist' : 'Create Artist' }}
                        </button>
                        <a href="{{ route('admin.constituents.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .admin-form-header {
            margin-bottom: 30px;
        }

        .admin-form-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            max-width: 700px;
        }

        .admin-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }

        .required {
            color: #dc3545;
        }

        .form-control {
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        textarea.form-control {
            resize: vertical;
        }

        select[multiple].form-control {
            min-height: 120px;
        }

        .form-text {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
            display: block;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 12px;
            margin-top: 4px;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: opacity 0.2s;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn:hover {
            opacity: 0.9;
        }
    </style>
@endsection
