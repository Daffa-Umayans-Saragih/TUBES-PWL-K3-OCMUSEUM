<nav class="bg-[#07244a] text-white sticky top-0 z-50">
    <style>
        .menu-trigger.active::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -0.35rem;
            width: 100%;
            height: 2px;
            background: #fff;
        }

        /* Animasi smooth untuk mobile menu */
        #mobile-drawer {
            transition: transform 0.3s ease-in-out;
        }

        #mobile-drawer.hidden-drawer {
            transform: translateX(100%);
        }

        #mobile-drawer.show-drawer {
            transform: translateX(0);
        }

        /* Hilangkan scrollbar */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    <div class="max-w-7xl mx-auto px-4 py-4">
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-4">
                <div class="text-[3.0rem] font-black leading-none tracking-[-0.08em]">
                    OC
                </div>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden xl:flex items-center gap-10 text-sm font-semibold tracking-[0.02em] ml-16 mr-auto">
                <button type="button" data-menu="visit"
                    class="menu-trigger relative pb-2 text-white/90 hover:text-white transition">
                    Visit
                </button>

                <button type="button" data-menu="art"
                    class="menu-trigger relative pb-2 text-white/90 hover:text-white transition">
                    Art
                </button>
            </div>

            <div class="flex items-center gap-3 text-sm font-medium">

                <!-- Search Button -->
                <a href="{{ route('art.search') }}" class="text-white/90 hover:text-white transition" aria-label="Search">
                    <i class="fas fa-search text-lg"></i>
                </a>

                <!-- Desktop Right Menu -->
                <div class="hidden xl:flex items-center gap-3">

                    <a href="#"
                        class="inline-flex items-center gap-2 rounded-full border border-white/200 bg-white/10 px-4 py-2 text-white/90 hover:text-white hover:border-white transition">
                        <i class="fas fa-globe"></i>
                        English
                    </a>

                    <a href="{{ route('ticket.cart') }}"
                        class="rounded-full border border-white/200 px-4 py-2 text-white/90 hover:text-white hover:border-white transition">
                        <i class="fas fa-shopping-cart"></i> Cart
                    </a>

                    @if(Auth::check() || session()->has('guest_id'))
                        @auth
                            <a href="{{ route('account.index') }}"
                                class="rounded-full border border-white/200 px-4 py-2 text-white/90 hover:text-white hover:border-white transition">
                                Account
                            </a>
                            
                            @if(auth()->user()->is_admin)
                                @if(auth()->user()->role_admin === 'cashier')
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/40 px-4 py-2 text-slate-200 font-medium whitespace-nowrap hover:text-white hover:border-white/60 hover:bg-white/5 transition-all duration-300">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Cashier Panel
                                    </a>
                                @elseif(auth()->user()->role_admin === 'superadmin')
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="inline-flex items-center gap-1.5 rounded-full border border-[#d4af37]/40 px-4 py-2 text-[#d4af37] font-medium whitespace-nowrap hover:text-[#f3d79b] hover:border-[#f3d79b]/60 hover:bg-[#d4af37]/5 transition-all duration-300">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2l3 5 5-2-2 6-2 9H6L4 11l-2-6 5 2 3-5z"></path></svg>
                                        Super Admin Panel
                                    </a>
                                @else
                                    <a href="{{ route('admin.dashboard') }}"
                                        class="inline-flex items-center gap-1.5 rounded-full border border-[#c8a96b]/40 px-4 py-2 text-[#c8a96b] font-medium whitespace-nowrap hover:text-[#e5c78b] hover:border-[#e5c78b]/60 hover:bg-[#c8a96b]/5 transition-all duration-300">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                        Admin Panel
                                    </a>
                                @endif
                            @endif

                            <a href="{{ route('order.show') }}"
                                class="rounded-full border border-white/200 px-4 py-2 text-white/90 hover:text-white hover:border-white transition">
                                Orders
                            </a>
                        @endauth

                        <form action="{{ route('account.logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit"
                                class="rounded-full border border-white/200 px-4 py-2 text-white/90 hover:text-white hover:border-white transition">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('account.login') }}"
                            class="rounded-full border border-white/200 px-4 py-2 text-white/90 hover:text-white hover:border-white transition">
                            Login
                        </a>
                    @endif

                    <a href="{{ route('member.membership') }}"
                        class="rounded-full border border-white/200 px-4 py-2 text-white/90 hover:text-white hover:border-white transition">
                        Membership
                    </a>
                </div>

                <!-- Tickets -->
                <a href="{{ route('ticket.index') }}"
                    class="rounded-full bg-white px-4 py-2 text-[#07244a] font-semibold shadow-sm hover:bg-gray-100 transition">
                    Tickets
                </a>

                <!-- Hamburger -->
                <button id="hamburger-open"
                    class="xl:hidden flex flex-col justify-center items-center w-8 h-8 gap-1.5 focus:outline-none">

                    <span class="block w-6 h-0.5 bg-white"></span>
                    <span class="block w-6 h-0.5 bg-white"></span>
                    <span class="block w-6 h-0.5 bg-white"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- MEGA MENU DESKTOP -->
    <div id="mega-menu" class="hidden border-t border-white/20 bg-[#07244a]">
        <div class="max-w-7xl mx-auto px-4 py-10">
            <div class="grid gap-10 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,420px)]">

                <!-- VISIT -->
                <div class="grid gap-5" data-menu-content="visit">
                    <a href="{{ route('plan-your-visit.index') }}"
                        class="text-base font-semibold text-white hover:text-white/80 transition">
                        Plan Your Visit
                    </a>

                    <a href="{{ route('ticket.index') }}"
                        class="text-base font-semibold text-white hover:text-white/80 transition">
                        Buy Tickets
                    </a>

                    <a href="{{ route('member.membership') }}"
                        class="text-base font-semibold text-white hover:text-white/80 transition">
                        Become a Member
                    </a>
                </div>

                <div class="grid gap-3" data-menu-content="visit">
                    <a href="{{ route('visit.accessibility') }}"
                        class="text-base font-semibold text-white hover:text-white/80 transition">
                        Accessibility
                    </a>
                    <a href="{{ route('visit.our-experience') }}"
                        class="text-base font-semibold text-white hover:text-white/80 transition">
                        Our Experience
                    </a>
                </div>

                <div class="flex items-center" data-menu-content="visit">
                    <div class="border-l border-white/30 pl-8">
                        <p class="text-3xl font-semibold leading-tight text-white">
                            Make the most of your visit
                        </p>
                    </div>
                </div>

                <!-- ART -->
                <div class="grid gap-5 hidden" data-menu-content="art">
                    <a href="{{ route('art.index') }}"
                        class="text-base font-semibold text-white hover:text-white/80 transition">
                        The OC Collection
                    </a>

                    <a href="{{ route('art.curatorial-areas') }}"
                        class="text-base font-semibold text-white hover:text-white/80 transition">
                        Curatorial Areas
                    </a>
                </div>

                <div class="grid gap-5 hidden" data-menu-content="art"></div>

                <div class="flex items-center hidden" data-menu-content="art">
                    <div class="border-l border-white/30 pl-8">
                        <p class="text-3xl font-semibold leading-tight text-white">
                            Explore art across time and place
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- MOBILE DRAWER -->
    <div id="mobile-drawer"
        class="fixed inset-0 bg-[#07244a] z-[60] hidden-drawer xl:hidden flex flex-col">

        <!-- Header -->
        <div class="p-4 flex justify-between items-center border-b border-white/10">
            <div class="text-[1.5rem] font-black leading-none tracking-[-0.08em]">
                OC
            </div>

            <button id="hamburger-close"
                class="text-white text-3xl focus:outline-none">
                &times;
            </button>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-scroll no-scrollbar p-6 flex flex-col gap-8">

            <!-- VISIT -->
            <div class="mobile-accordion border-b border-white/20 pb-4">

                <button type="button"
                    class="mobile-accordion-header w-full flex justify-between items-center text-2xl font-bold">

                    <span>Visit</span>

                    <i class="accordion-icon fas fa-chevron-down text-lg text-white transition-transform duration-300"></i>
                </button>

                <div class="mobile-accordion-content hidden mt-5 flex flex-col gap-4 text-lg font-medium">

                    <a href="{{ route('plan-your-visit.index') }}"
                        class="text-white/90 hover:text-white transition">
                        Plan Your Visit
                    </a>

                    <a href="{{ route('ticket.index') }}"
                        class="text-white/90 hover:text-white transition">
                        Buy Tickets
                    </a>

                    <a href="{{ route('member.membership') }}"
                        class="text-white/90 hover:text-white transition">
                        Become a Member
                    </a>

                    <a href="{{ route('visit.accessibility') }}"
                        class="text-white/90 hover:text-white transition">
                        Accessibility
                    </a>

                    <a href="{{ route('visit.our-experience') }}"
                        class="text-white/90 hover:text-white transition">
                        Our Experience
                    </a>

                </div>
            </div>

            <!-- ART -->
            <div class="mobile-accordion border-b border-white/20 pb-4">

                <button type="button"
                    class="mobile-accordion-header w-full flex justify-between items-center text-2xl font-bold">

                    <span>Art</span>

                    <i class="accordion-icon fas fa-chevron-down text-lg text-white transition-transform duration-300"></i>
                </button>

                <div class="mobile-accordion-content hidden mt-5 flex flex-col gap-4 text-lg font-medium">

                    <a href="{{ route('art.index') }}"
                        class="text-white/90 hover:text-white transition">
                        The OC Collection
                    </a>

                    <a href="{{ route('art.curatorial-areas') }}"
                        class="text-white/90 hover:text-white transition">
                        Curatorial Areas
                    </a>

                </div>
            </div>

            <!-- Buttons -->
            <div class="mt-4 flex flex-col gap-4">

                <a href="{{ route('ticket.index') }}"
                    class="w-full py-4 bg-white text-[#07244a] text-center font-bold rounded-sm">
                    Tickets
                </a>

                <a href="{{ route('membership.index') }}"
                    class="w-full py-4 border border-white text-white text-center font-bold rounded-sm">
                    Membership
                </a>

            </div>

            <!-- Footer -->
            <div class="mt-auto flex flex-wrap gap-4 text-sm border-t border-white/20 pt-6">

                <a href="#" class="underline">English</a>
                <a href="#">Español</a>
                <a href="#">Français</a>
                <a href="#">Deutsch</a>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            // MEGA MENU DESKTOP
            const menuButtons = document.querySelectorAll('.menu-trigger');
            const megaMenu = document.getElementById('mega-menu');
            const contents = document.querySelectorAll('[data-menu-content]');

            function closeMenu() {
                contents.forEach(item => item.classList.add('hidden'));

                megaMenu.classList.add('hidden');

                menuButtons.forEach(btn => {
                    btn.classList.remove('active');
                    btn.classList.remove('text-white');
                });
            }

            function openMenu(name, button) {

                closeMenu();

                const showItems = document.querySelectorAll(`[data-menu-content="${name}"]`);

                showItems.forEach(item => item.classList.remove('hidden'));

                megaMenu.classList.remove('hidden');

                button.classList.add('active');
                button.classList.add('text-white');
            }

            menuButtons.forEach(button => {

                button.addEventListener('click', () => {

                    const name = button.getAttribute('data-menu');

                    const active =
                        !megaMenu.classList.contains('hidden') &&
                        button.classList.contains('active');

                    if (active) {
                        closeMenu();
                        return;
                    }

                    openMenu(name, button);
                });
            });

            // MOBILE DRAWER
            const openBtn = document.getElementById('hamburger-open');
            const closeBtn = document.getElementById('hamburger-close');
            const drawer = document.getElementById('mobile-drawer');

            openBtn.addEventListener('click', () => {

                drawer.classList.remove('hidden-drawer');
                drawer.classList.add('show-drawer');

                document.body.style.overflow = 'hidden';
            });

            closeBtn.addEventListener('click', () => {

                drawer.classList.remove('show-drawer');
                drawer.classList.add('hidden-drawer');

                document.body.style.overflow = '';
            });

            // ACCORDION MOBILE
            const mobileAccordions = document.querySelectorAll('.mobile-accordion');

            mobileAccordions.forEach(acc => {

                const header = acc.querySelector('.mobile-accordion-header');
                const content = acc.querySelector('.mobile-accordion-content');
                const icon = acc.querySelector('.accordion-icon');

                header.addEventListener('click', () => {

                    const isOpen = !content.classList.contains('hidden');

                    // Tutup semua
                    mobileAccordions.forEach(other => {

                        other.querySelector('.mobile-accordion-content')
                            .classList.add('hidden');

                        other.querySelector('.accordion-icon')
                            .classList.remove('rotate-180');
                    });

                    // Buka yg dipilih
                    if (!isOpen) {

                        content.classList.remove('hidden');

                        icon.classList.add('rotate-180');
                    }
                });
            });
        });
    </script>
</nav>