@extends('layouts.app')

@push('styles')
    @vite('resources/css/app.css')
    @vite('resources/css/ordinary/plan-your-visit/fifth/fifth.css')
@endpush

@section('content')
    <div class="container">
        <div class="location-detail">
            <h1>Main Building - Fifth Avenue</h1>

            <div class="location-info">
                <div class="info-section">
                    <h2>General Information</h2>
                    <p><strong>Address:</strong> 1000 Fifth Avenue, New York, NY 10028</p>
                    <p><strong>Phone:</strong> (212) 535-7710</p>
                    <p><strong>Website:</strong> www.OurCivilization.org</p>
                </div>

                <div class="info-section">
                    <h2>Hours of Operation</h2>
                    <ul>
                        <li>Tuesday - Thursday: 10:00 AM - 5:30 PM</li>
                        <li>Friday - Saturday: 10:00 AM - 9:00 PM</li>
                        <li>Sunday: 10:00 AM - 5:30 PM</li>
                        <li>Closed Mondays and certain holidays</li>
                    </ul>
                </div>

                <div class="info-section">
                    <h2>Getting Here</h2>
                    <p>The Fifth Avenue location is easily accessible by:</p>
                    <ul>
                        <li>Subway: Lexington Avenue Line (4, 5, 6 trains)</li>
                        <li>Bus: M1, M2, M3, M4 buses</li>
                        <li>Parking: Nearby parking lots available</li>
                    </ul>
                </div>

                <div class="info-section">
                    <h2>Facilities</h2>
                    <ul>
                        <li>Dining options</li>
                        <li>Gift shop</li>
                        <li>Accessible facilities</li>
                        <li>First aid</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection