<!-- HEADER -->
  <header class="w-full h-[92px] bg-[#07244a] flex items-center justify-between px-10">
    
    <!-- LOGO -->
    <a href="{{ route('home') }}" class="text-white font-bold leading-[0.78] text-[45px] no-underline">
      <div>OC</div>
    </a>

    <!-- RIGHT -->
    <div class="flex items-center gap-10 text-white">

      <!-- LOGIN / LOGOUT -->
      @if(Auth::check() || session()->has('guest_id'))
      <form action="{{ route('account.logout') }}" method="POST" class="inline m-0 p-0">
        @csrf
        <button type="submit" class="flex items-center gap-3 text-[18px] font-semibold bg-transparent border-none text-white cursor-pointer">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0ZM4.5 20.118a7.5 7.5 0 0115 0A17.933 17.933 0 0112 21.75a17.933 17.933 0 01-7.5-1.632Z"/>
          </svg>
          <span>Logout</span>
        </button>
      </form>
      @else
      <a href="{{ route('account.login') }}" class="flex items-center gap-3 text-[18px] font-semibold">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0ZM4.5 20.118a7.5 7.5 0 0115 0A17.933 17.933 0 0112 21.75a17.933 17.933 0 01-7.5-1.632Z"/>
        </svg>
        <span>Login</span>
      </a>
      @endif

      <!-- CART -->
      <a href="{{ route('ticket.cart') }}" class="flex items-center gap-3 text-[18px] font-semibold">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386a1.5 1.5 0 011.415 1.026L5.7 6.75m0 0h13.05l-1.5 7.5H7.2m-1.5-7.5L7.2 14.25m0 0a1.5 1.5 0 100 3 1.5 1.5 0 000-3Zm9 0a1.5 1.5 0 100 3 1.5 1.5 0 000-3Z"/>
        </svg>
        <span>Cart</span>
      </a>

    </div>

  </header>