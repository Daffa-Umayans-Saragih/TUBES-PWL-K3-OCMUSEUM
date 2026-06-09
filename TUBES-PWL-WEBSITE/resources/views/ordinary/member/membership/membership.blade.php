<!DOCTYPE html>
<html lang="en">

@extends('layouts.main')

@section('title', 'Home')

@section('content')

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Membership | Our Civilization</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="antialiased text-[#2D2D2D]">

        <!-- Hero Section -->
        <div class="bg-[#ffffff]">
            <div class="max-w-screen-2xl mx-auto flex flex-col lg:flex-row items-stretch">
                <div class="flex-1 p-8 lg:p-16 flex flex-col justify-center">
                    <div class="flex items-center space-x-1 mb-12 text-[13px]">
                        <span class="underline hover:no-underline cursor-pointer">Home</span>
                        <span class="mx-2 font-thin">/</span>
                        <span>Membership</span>
                    </div>
                    <h1 class="page-title mb-10">Experience more as a OC Member</h1>
                    <h2 class="page-subtitle mb-6">Join or renew today!</h2>
                    <p class="header-desc mb-6">
                        Discover something new on every visit and stay connected year-round. Enjoy unlimited free admission,
                        priority access to exhibitions, and invitations to Members-only events.
                    </p>
                    <p class="header-desc">
                        Join today and become part of our global community of supporters dedicated to helping The OC
                        educate and inspire millions each year through the power of art.
                    </p>
                    
                     <a a href = "{{ route('member.add-member') }}" class="faq-btn-wrapper mt-8">
                       <button class="bg-[#07244a] text-white px-4 py-2 rounded"> Become a Member!
                       </button>
                    </a>
                    
                </div>
                <div class="flex-1">
                    <img src="{{ asset('images/member-image.avif') }}" alt="OC Members" class="w-full h-full object-cover">
                </div>
            </div>
        </div>

        <!-- Membership Cards Section -->
        <div class="max-w-screen-xl mx-auto px-6 py-16">
           

            <!-- Questions & FAQ Section -->
            <section class="mt-20 border-t border-gray-200 pt-14">
                <h2 class="text-2xl font-extrabold mb-12">Questions</h2>

                <div class="space-y-7 mb-14 max-w-4xl">
                    <div>
                        <h3 class="faq-q">When do Memberships begin and can I delay the start?</h3>
                        <p class="faq-a">Your Membership is active from the date of purchase. Memberships cannot be delayed
                            and are active from the date of purchase. We recommend purchasing your personal or gift
                            Membership closer to the date you would like it to begin.</p>
                    </div>
                    <div>
                        <h3 class="faq-q">Can I purchase a Membership as a gift?</h3>
                        <p class="faq-a">A Membership at The OC makes the perfect gift for all occasions! Select the level
                            you'd like from the options at the top of the page, and you'll be able to enter the recipient's
                            information on the next page.</p>
                    </div>
                    <div>
                        <h3 class="faq-q">How long does it take to receive Membership Cards and can I visit without one?
                        </h3>
                        <p class="faq-a">It usually takes 3 to 4 weeks to receive Membership cards in the mail. Please allow
                            more time for international addresses. Members may always visit and shop without their card. If
                            you do not have your card for an upcoming visit, please check in at our Membership Desk or any
                            admissions desk with your ID number or name.</p>
                    </div>
                    <div>
                        <h3 class="faq-q">How do I log into my Member Portal?</h3>
                        <p class="faq-a">
                            You may access your Member Portal
                            <a href="{{ route('account.login') }}" class="underline font-medium hover:text-black">here</a>.
                            You may log in using the email address associated with your Membership.
                        </p>

                        <p class="faq-a mt-4">
                            If you are unsure about the password associated with your email or are unsure if you have
                            created a login,
                            please check for your account and follow the instructions
                            <a href="{{ route('account.login') }}" class="underline font-medium hover:text-black">here</a>.
                        </p>
                    </div>
                    <!-- ... tambahkan pertanyaan lainnya ke bawah ... -->
                </div>

                <!-- Container FAQ & Contact -->
                <div class="faq-contact-container">
                   

                    <!-- Teks Contact & Terms -->
                    <div class="faq-text-wrapper">
                        <div class="contact-box mb-10">
                            <h4 class="contact-title">Contact Us</h4>
                            <p class="contact-detail">
                                <a href="mailto:ourcivilization@gmail.com"
                                    class="underline-link">ourcivilization@gmail.com</a>
                                or call 212-570-3753
                            </p>
                        </div>

                        <div class="terms-box">
                            <h4 class="contact-title">Terms and Conditions</h4>
                            <p class="terms-text">
                                Please note that contributions are not refundable or transferable. All categories, benefits,
                                and prices are subject to change. Membership benefits may not be refunded, resold,
                                exchanged, or transferred. Admission tickets and membership benefits may not be used for
                                group visits. By visiting the Museum, you agree to comply with our Visitor Guidelines.
                            </p>
                            <p class="terms-text mt-4">*Retail Discount Terms and Conditions</p>
                            <p class="terms-text mt-4">Discount does not apply to the purchase of museum admission, gift
                                memberships, donations, eGift Certificates, prior purchases, clearance products or select
                                items. Cannot be combined with other offers or discounts. Offer subject to change or
                                cancellation without notice.</p>
                        </div>
                    </div>
                </div>
            </section>

           
               
@endsection

</html>