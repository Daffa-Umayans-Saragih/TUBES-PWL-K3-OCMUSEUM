<nav class="the-met-red text-white w-full h-[135px] flex items-center px-8" style="background-color: #e4002b;">
    <div class="w-full max-w-[1440px] mx-auto flex justify-between items-center h-full pt-4">

        <div class="flex items-end gap-10 h-full pb-6">
            <div class="mb-[-4px]">
                <a href="{{ route('home') }}">
                    <img src="https://www.metmuseum.org/assets/img/met-logo.svg"
                         alt="The Met"
                         class="h-[82px] brightness-0 invert">
                </a>
            </div>

            <ul class="flex items-center gap-7 text-[17px] font-semibold tracking-tight">
                <li><a href="{{ route('plan-your-visit.index') }}" class="cursor-pointer hover:opacity-70 transition-opacity">Visit</a></li>
                <li class="cursor-pointer hover:opacity-70 transition-opacity whitespace-nowrap">Exhibitions and Events</li>
                <li><a href="{{ route('art.index') }}" class="cursor-pointer hover:opacity-70 transition-opacity">Art</a></li>
                <li class="cursor-pointer hover:opacity-70 transition-opacity whitespace-nowrap">Learn with Us</li>
                <li class="cursor-pointer hover:opacity-70 transition-opacity">Research</li>
                <li class="cursor-pointer hover:opacity-70 transition-opacity flex items-center gap-1.5">
                    Shop <i class="fa-solid fa-arrow-up-right-from-square text-[10px] mt-1"></i>
                </li>
                <li class="cursor-pointer hover:opacity-70 transition-opacity pl-2">
                    <a href="{{ route('art.search') }}" class="text-white no-underline">
                        <i class="fa-solid fa-magnifying-glass text-lg"></i>
                    </a>
                </li>
            </ul>
        </div>

        <div class="flex flex-col items-end gap-4 h-full pt-2">
            <div class="flex items-center gap-2 text-[15px] font-medium cursor-pointer hover:opacity-70">
                <i class="fa-solid fa-globe text-base"></i>
                <span>English</span>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('ticket.cart') }}" class="border-[1.5px] border-white px-[22px] py-[9px] rounded-[4px] text-[15px] font-bold hover:bg-white hover:text-[#e4002b] transition-all">
                    <i class="fa-solid fa-shopping-cart"></i> Cart
                </a>

                @if(Auth::check() || session()->has('guest_user') || session()->has('guest_id'))
                    @auth
                        <span class="text-white font-medium px-[10px] py-[9px] text-[15px]">
                            Hello, {{ Auth::user()->name }}
                        </span>
                    @endauth
                    @if(session()->has('guest_name'))
                        <span class="text-white font-medium px-[10px] py-[9px] text-[15px]">
                            Guest: {{ session('guest_name') }}
                        </span>
                    @endif
                    @if(Auth::user() && Auth::user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="bg-white text-[#e4002b] px-[22px] py-[9px] rounded-[4px] text-[15px] font-bold hover:opacity-90 transition-all border-[1.5px] border-white">
                            Admin Panel
                        </a>
                    @endif
                    <form action="{{ route('account.logout') }}" method="POST" class="inline m-0 p-0">
                        @csrf
                        <button type="submit" class="border-[1.5px] border-white px-[22px] py-[9px] rounded-[4px] text-[15px] font-bold hover:bg-white hover:text-[#e4002b] transition-all">
                            Logout
                        </button>
                    </form>
                @else
                    <a href="{{ route('account.login') }}" class="border-[1.5px] border-white px-[22px] py-[9px] rounded-[4px] text-[15px] font-bold hover:bg-white hover:text-[#e4002b] transition-all">
                        Login
                    </a>
                @endif

                <a href="{{ route('membership.index') }}" class="border-[1.5px] border-white px-[22px] py-[9px] rounded-[4px] text-[15px] font-bold hover:bg-white hover:text-[#e4002b] transition-all">
                    Membership
                </a>
                <a href="{{ route('ticket.index') }}" class="bg-white text-[#e4002b] px-[22px] py-[9px] rounded-[4px] text-[15px] font-bold flex items-center gap-2 border-[1.5px] border-white hover:bg-transparent hover:text-white transition-all">
                    <i class="fa-solid fa-ticket -rotate-[20deg] text-base"></i>
                    Tickets
                </a>
            </div>
        </div>

    </div>
</nav>
