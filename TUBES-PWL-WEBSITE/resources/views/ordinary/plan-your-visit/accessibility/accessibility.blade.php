<!DOCTYPE html>
<html lang="en">
@extends('layouts.main')

@section('title', 'Home')

@section('content')

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Accessibility - Our Civilization of Art</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])

    </head>

    <body>
        <nav class="max-w-screen-xl mx-auto px-6 md:px-10 py-4 flex items-center bg-white" aria-label="Breadcrumb">
            <!-- Icon Home -->
            <a href="/" class="text-black hover:text-black transition-colors duration-200">
                <svg class="w-3 h-3 transition-all duration-200 fill-none hover:fill-black" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
            </a>

            <!-- Separator -->
            <span class="mx-2.5 text-black font-extralight text-sm">/</span>

            <!-- Link Plan Your Visit -->
            <a href="/plan-your-visit" class="text-black text-xs hover:underline tracking-wide transition-all duration-200">
                Plan Your Visit
            </a>

            <!-- Separator -->
            <span class="mx-2.5 text-gray-400 font-extralight text-sm">/</span>

            <!-- Active Page -->
            <span class="text-black text-xs font-semibold">Accessibility</span>
        </nav>

        <section class="hero-section">
            <!-- Bagian Title -->
            <h1 class="page-title">
                Accessibility
            </h1>

            <!-- Bagian Subtitle -->
            <p class="page-subtitle">
                The Museum is committed to making its collection, buildings,
                programs, and services accessible to all audiences. Learn
                more about services and accessibility for visitors with
                disabilities at OC Fifth Avenue.
            </p>
        </section>

        <div class="jump-to-wrapper">
            <nav class="jump-to-container" aria-label="Jump to section">
                <span class="jump-to-label">Jump to:</span>

                <a href="#know-before" class="jump-to-link">Know Before You Go</a>
                <a href="#events" class="jump-to-link">Events and Activities</a>
                <a href="#stay-updated" class="jump-to-link">Stay Updated!</a>
            </nav>
        </div>

        <!-- Contoh target section-nya -->
        <div class="max-w-screen-xl mx-auto px-6 md:px-10 mt-5">
            <section id="know-before" class="py-8">
                <h2 class="section-title">Know Before You Go</h2>

                <!-- Grid Container -->
                <div class="section-grid">

                    <!-- LEFT SIDE (Konten & Accordion) - Mengambil 2 kolom di layar besar -->
                    <div class="md:col-span-2">
                        <div class="content-body">
                            <p class="mb-8">
                                This information is specific to OC Fifth Avenue. For information about our other
                                location, please visit
                                <a href="{{ route('accessibility.cloisters') }}" class="inline-link">accessibility at OC
                                    Cloisters</a>.
                            </p>

                            <p>
                                The principles of diversity, equity, inclusion, and accessibility apply to all aspects of
                                OC's operations, across all categories of individuals. Please see the
                                Museum's Institutional Diversity, Inclusion, and Equal
                                Access Policy Statement.
                        </div>

                        <!-- Accordion Items -->
                        <details class="accordion-item group mt-12" open>
                            <summary class="accordion-header">
                                <h3 class="accordion-question">Where are the accessible entrances?</h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>Accessible entrances are located at Fifth Avenue and 81st Street and through the parking
                                    garage at Fifth Avenue and 80th Street.</p>
                            </div>
                        </details>

                        <details class="accordion-item group">
                            <summary class="accordion-header">
                                <h3 class="accordion-question">Are assistive listening devices available for exhibitions,
                                    tours, and programs?</h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>Hearing loops are installed at all Ticketing and Information Desks in the Great Hall and
                                    Burke Hall in the Uris Center for Education. Look for signage about assistive listening
                                    options in exhibitions with audio components. Assistive listening devices with headsets
                                    or neck loops are available for Museum tours and programs. In Grace Rainey Rogers
                                    auditorium, ask an usher. In classrooms in the Ruth and Harold D. Uris Center for
                                    Education, ask staff.</p>
                            </div>
                        </details>

                        <details class="accordion-item group">
                            <summary class="accordion-header">
                                <h3 class="accordion-question">Are Audio Guides and transcripts available?</h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>OC Fifth Avenue <a href="#" class="inline-link">Audio Guide stops and
                                        transcripts</a> are free on our website.</p>
                            </div>
                        </details>

                        <details class="accordion-item group">
                            <summary class="accordion-header">
                                <h3 class="accordion-question">Is captioning and Real-Time captioning available?</h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>Closed captions are available for virtual Museum events and most online features and
                                    resources. If you require Real-Time captioning for live events, contact us at least two
                                    weeks in advance (subject to the availability of captioners). We can have videos without
                                    captions on our website captioned upon request. See contact information above.</p>
                            </div>
                        </details>

                        <details class="accordion-item group">
                            <summary class="accordion-header">
                                <h3 class="accordion-question">Where can I eat and drink?</h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>Outside food and drinks are prohibited except for visitors with food related medical
                                    concerns. Water in a secure bottle is allowed. Visitors will be asked to dispose of food
                                    and drinks before entering the Museum. Food and drink (other than water in a secure
                                    bottle) is not permitted in the galleries. <a href="#" class="inline-link">Dining
                                        options</a> are also available at the Museum.</p>
                            </div>
                        </details>

                        <details class="accordion-item group">
                            <summary class="accordion-header">
                                <h3 class="accordion-question">Where can I find Large Print Information?</h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>Large print maps are available upon request from any Information Desk. Large print label
                                    booklets for select exhibitions are available at exhibition entrances or via a QR code
                                    on the website.</p>
                            </div>
                        </details>

                        <details class="accordion-item group">
                            <summary class="accordion-header">
                                <h3 class="accordion-question">Where can I park?</h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>The Museum parking garage is located at Fifth Avenue and 80th Street.</p>
                            </div>
                            <div class="accordion-content">
                                <p>Designated spaces are available in the parking garage for disabled parking permit
                                    holders. The clearance is six feet, six inches (6' 6"). Alternate arrangements can be
                                    made in advance for visitors with disabilities traveling in oversized vehicles. Please
                                    call 212-570-1437 for more information.</p>
                            </div>
                            <div class="mt-8">
                                <button class="visit-link">
                                    <span>Visit this link for more information about parking.</span>
                                </button>
                            </div>
                        </details>

                        <details class="accordion-item group">
                            <summary class="accordion-header">
                                <h3 class="accordion-question">Are there accessible routes within the museum for wheelchair
                                    users?</h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>The Museum is accessible to wheelchair users and others who need to avoid stairs. Ask
                                    staff if you need assistance locating elevators or step-free routes. You can find
                                    step-free routes through the Museum <a href="#" class="inline-link">using the
                                        interactive map</a>.</p>
                            </div>
                            <div class="accordion-content">
                                <p>There may be lines to enter the Museum or to visit an exhibition. If you are unable to
                                    stand in line, please speak to a staff member.</p>
                            </div>
                        </details>

                        <details class="accordion-item group">
                            <summary class="accordion-header">
                                <h3 class="accordion-question">Is seating available in the galleries?</h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>Seating is available throughout the Museum galleries.</p>
                            </div>
                            <div class="accordion-content">
                                <p>Visitors with disabilities may borrow a stool in Burke Hall inside the 81st Street
                                    entrance or ask at the Admissions Desk.</p>
                            </div>
                        </details>

                        <details class="accordion-item group">
                            <summary class="accordion-header">
                                <h3 class="accordion-question">Are service animals welcome?</h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>Service animals are welcome. Pets and emotional support animals are not allowed.</p>
                            </div>
                        </details>

                        <details class="accordion-item group">
                            <summary class="accordion-header">
                                <h3 class="accordion-question">Are there Sign Language services available?</h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>Sign Language interpreters may be requested for Museum programs. At least two weeks'
                                    notice is required. Email <a href="mailto:ourcivilization@gmail.com"
                                        class="inline-link">ourcivilization@gmail.com</a> for more information.</p>
                            </div>
                            <div class="mt-8">
                                <button class="visit-link">
                                    <span>Upcoming Events for Visitor who are Deaf and Hard of Hearing</span>
                                </button>
                            </div>
                        </details>

                        <details class="accordion-item group">
                            <summary class="accordion-header">
                                <h3 class="accordion-question">Does my accompanying care partner have to purchase a ticket?
                                </h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>A visitor with a disability qualifies for a discounted ticket price of $22. This ticket
                                    must be purchased in person. A care partner accompanying a visitor with a disability is
                                    eligible for complimentary admission.</p>
                            </div>
                        </details>

                        <details class="accordion-item group">
                            <summary class="accordion-header">
                                <h3 class="accordion-question">Are verbal description and imaging tours available?</h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>Verbal description is available for Museum events with two weeks' notice. Verbal imaging
                                    tours are available free of charge to visitors who are blind or partially sighted, with
                                    advance notice. For more information, call 212-650-2010, or email <a
                                        href="mailto:ourcivilization@gmail.com"
                                        class="inline-link">ourcivilization@gmail.com</a>.</p>
                            </div>
                            <div class="mt-8">
                                <button class="visit-link">
                                    <span>Upcoming Events for Visitor who are Blind or Partially Sighted</span>
                                </button>
                            </div>
                        </details>

                        <details class="accordion-item group">
                            <summary class="accordion-header">
                                <h3 class="accordion-question">What Wheelchair and mobility devices are allowed?</h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>Visitors with disabilities may use mobility devices, including manual and electric
                                    wheelchairs, mobility scooters, and manually powered mobility aids (such as walkers,
                                    canes, and crutches) in all areas open to public pedestrian use. You may also use
                                    certain electronic personal assistance mobility devices (EPAMDs) in areas open to public
                                    pedestrian use in accordance with Museum guidelines. Please contact <a
                                        href="mailto:ourcivilization@gmail.com"
                                        class="inline-link">ourcivilization@gmail.com</a> or
                                    212-650-2010 for guidelines and to make a reservation.</p>
                            </div>
                            <div class="accordion-content">
                                <p>Visitors may borrow manual wheelchairs (standard and wide) from the coat check at the
                                    81st Street entrance on a first-come, first-served basis. If you plan to borrow a Museum
                                    wheelchair and need assistance, please visit with a companion.</p>
                            </div>
                        </details>

                        <details class="accordion-item group">
                            <summary class="accordion-header">
                                <h3 class="accordion-question">Website Accessibility Statement</h3>
                                <span class="accordion-icon group-open:hidden">+</span>
                                <span class="accordion-icon hidden group-open:block">−</span>
                            </summary>
                            <div class="accordion-content">
                                <p>OC is committed to facilitating accessibility and usability of its website, <a
                                        href="linkto:metmuseum" class="inline-link">https://www.OurCivilization.org</a>, for
                                    all
                                    people with disabilities. We are working to implement digital accessibility standards in
                                    accordance with the World Wide Web Consortium’s <a href="#" class="inline-link">Web
                                        Content Accessibility Guidelines (WCAG) 2.1</a> Level AA and the revised <a href="#"
                                        class="inline-link">508 Standards</a> developed by the United States Access Board.
                                    Our efforts to create an optimally accessible digital experience are ongoing. If you
                                    have specific questions or concerns about the accessibility of a particular web page on
                                    <a href="linkto:metmuseum" class="inline-link">https://www.OurCivilization.org</a>,
                                    please
                                    contact us at <a href="mailto:ourcivilization@gmail.com"
                                        class="inline-link">ourcivilization@gmail.com</a>. To report a website
                                    accessibility issue, please specify the web page in your e-mail, and we will make all
                                    reasonable efforts to make that page accessible for you.
                                </p>
                            </div>
                        </details>

                        <div class="border-t border-gray-300"></div>


                    </div>

                    <!-- RIGHT SIDE (Contact) - Mengambil 1 kolom di layar besar -->
                    <div class="md:col-span-1">
                        <h3 class="contact-title">Contact</h3>
                        <div class="contact-body">
                            <p class="mb-4">
                                For more information or to request accommodations, please call 212-650-2010 (voice) or
                                email
                                <a href="mailto:ourcivilization@gmail.com"
                                    class="inline-link">ourcivilization@gmail.com</a>.
                            </p>
                        </div>

                    </div>



                </div> <!-- End of Section Grid -->
            </section>
        </div>

        <script>
            const form = document.getElementById('newsletterForm');
            const emailInput = document.getElementById('email');
            const errorText = document.getElementById('errorText');

            form.addEventListener('submit', (e) => {
                e.preventDefault();
                // Cek validasi email (HTML5 validation)
                if (!emailInput.validity.valid || emailInput.value === '') {
                    emailInput.classList.add('is-invalid');
                    errorText.classList.remove('hidden');
                } else {
                    emailInput.classList.remove('is-invalid');
                    errorText.classList.add('hidden');
                    alert('Success!');
                }
            });

            // Reset warna saat user ngetik lagi
            emailInput.addEventListener('input', () => {
                emailInput.classList.remove('is-invalid');
                errorText.classList.add('hidden');
            });
        </script>
    </body>
@endsection

</html>