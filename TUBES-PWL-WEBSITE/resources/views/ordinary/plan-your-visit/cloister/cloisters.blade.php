@extends('layouts.app')

@push('styles')
    @vite('resources/css/app.css')
    @vite('resources/css/ordinary/plan-your-visit/cloister/cloisters.css')
@endpush

@section('content')
    <div class="container">
        <div class="location-detail">
            <h1>The Cloisters</h1>

            <div class="location-info">
                <div class="info-section">
                    <h2>General Information</h2>
                    <p><strong>Address:</strong> 99 Margaret Corbin Drive, New York, NY 10040</p>
                    <p><strong>Phone:</strong> (212) 535-7710</p>
                    <p><strong>Admission:</strong> Included with Fifth Avenue ticket</p>
                </div>

                <div class="info-section">
                    <h2>Hours of Operation</h2>
                    <ul>
                        <li>Tuesday - Sunday: 10:00 AM - 4:30 PM</li>
                        <li>Closed Mondays and certain holidays</li>
                    </ul>
                </div>

                <div class="info-section">
                    <h2>About The Cloisters</h2>
                    <p>The Cloisters is a branch of The Met that houses an outstanding collection of medieval art,
                        architecture, and gardens. Located in upper Manhattan in Fort Tryon Park.</p>
                </div>

                <div class="info-section">
                    <h2>Getting Here</h2>
                    <p>The Cloisters is accessible by:</p>
                    <ul>
                        <li>Subway: A train (190th Street station)</li>
                        <li>Bus: M4 bus (North Avenue stop)</li>
                        <li>Walking: 10 minutes from the A train</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection