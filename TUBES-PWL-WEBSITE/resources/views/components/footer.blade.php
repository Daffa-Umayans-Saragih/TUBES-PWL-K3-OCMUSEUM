@push('styles')
@vite('resources/css/app.css')
@vite('resources/css/components/footer.css')
@endpush

<footer class="footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3>The Metropolitan Museum of Art</h3>
            <ul>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Mission</a></li>
                <li><a href="#">History</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Visit</h3>
            <ul>
                <li><a href="#">Hours</a></li>
                <li><a href="#">Directions</a></li>
                <li><a href="#">Admission</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Collections</h3>
            <ul>
                <li><a href="#">Explore</a></li>
                <li><a href="{{ route('art.search') }}">Search</a></li>
                <li><a href="#">Departments</a></li>
            </ul>
        </div>

        <div class="footer-section">
            <h3>Support</h3>
            <ul>
                <li><a href="#">Donate</a></li>
                <li><a href="#">Membership</a></li>
                <li><a href="#">Contact</a></li>
            </ul>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} The Metropolitan Museum of Art. All rights reserved.</p>
        <div class="social-links">
            <a href="#" class="social-link">Facebook</a>
            <a href="#" class="social-link">Twitter</a>
            <a href="#" class="social-link">Instagram</a>
        </div>
    </div>
</footer>
