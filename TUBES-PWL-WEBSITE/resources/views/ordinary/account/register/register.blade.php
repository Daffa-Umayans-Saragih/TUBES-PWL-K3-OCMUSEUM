@extends('layouts.sub')

@section('title', 'Create Account')

@section('content')
<style>
  /* Prevent :invalid from showing red on page load */
  input:invalid {
    box-shadow: none !important;
    border-color: #7d7d7d !important;
  }
  /* Show red after user interaction if invalid */
  input:user-invalid {
    border-color: #ef0030 !important;
  }
  /* Preserve error styles when applied by Laravel/JS */
  input.is-invalid, .is-invalid input, .border-red-500 {
    border-color: #ef0030 !important;
  }
</style>
   <div class="min-h-screen bg-[#f3f3f3] font-sans">

  <!-- CONTENT -->
  <main class="px-[132px] pt-[82px] pb-[120px]">

    <!-- TITLE -->
    <h1 class="text-[82px] font-black leading-none tracking-[-3px] text-[#222]">
      Register
    </h1>

    <!-- DESC -->
    <p class="mt-12 text-[18px] text-[#222]">
      Enter your contact details to create an account.
    </p>

    <!-- ALERT BOX -->
    <div class="bg-blue-100 mt-10 w-[850px] bg-[#eadde3] px-8 py-8">
      
      <p class="text-[19px] leading-[1.55] font-semibold text-[#222] max-w-[760px]">
        If you are a Member, or if your email may already be on record with the Museum,
        please click the button below to check if you already have an account and reset your password.
      </p>

      <a href="{{ route('account.account-check') }}" class="mt-8 h-[58px] px-12 bg-gradient-to-b from-[#07244a] to-[#07244a] text-white text-[18px] font-semibold inline-flex items-center justify-center">
        Check for account
      </a>

    </div>

    <!-- FORM -->
    <form action="{{ route('register.store') }}" method="POST" class="mt-24 w-[760px]">
        @csrf

      <!-- ACCOUNT INFO -->
      <section>
        
        <h2 class="text-[74px] font-black leading-none tracking-[-2px] text-[#222]">
          Account Info
        </h2>

        <!-- EMAIL -->
        <div class="mt-10">
          <label class="block text-[16px] text-[#222] mb-3">
            Email
          </label>

          <input
            type="email"
            name="email"
            value="{{ old('email', session('email')) }}"
            class="w-full h-[58px] border @error('email') border-blue-500 @else border-[#7d7d7d] @enderror bg-transparent px-5 text-[16px] outline-none"
            required
          />
          @error('email')
            <p class="mt-2 text-sm text-blue-600">{{ $message }}</p>
          @enderror
        </div>

        <!-- PASSWORD -->
        <div class="mt-8">
          <label class="block text-[16px] text-[#222] mb-3">
            Password
          </label>

          <div class="relative">
            <input
              type="password"
              id="password"
              name="password"
              class="w-full h-[58px] border @error('password') border-blue-500 @else border-[#7d7d7d] @enderror bg-transparent px-5 pr-16 text-[16px] outline-none"
              required
            />

            <button
              type="button"
              id="togglePassword"
              class="absolute right-4 top-1/2 -translate-y-1/2 text-[#666]"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z"/>
              </svg>
            </button>
          </div>
          @error('password')
            <p class="mt-2 text-sm text-blue-600">{{ $message }}</p>
          @enderror

          <p class="mt-3 text-[14px] text-[#333]">
            Password must be least 8 characters
          </p>
        </div>

        <!-- CONFIRM -->
        <div class="mt-8">
          <label class="block text-[16px] text-[#222] mb-3">
            Confirm Password
          </label>

          <div class="relative">
            <input
              type="password"
              id="password_confirmation"
              name="password_confirmation"
              class="w-full h-[58px] border border-[#7d7d7d] bg-transparent px-5 text-[16px] outline-none"
              required
            />
          </div>
        </div>

      </section>

      <!-- PERSONAL -->
      <section class="mt-24">

        <h2 class="text-[74px] font-black leading-none tracking-[-2px] text-[#222]">
          Personal Info
        </h2>

        <div class="mt-10 space-y-8">

          <div>
            <label class="block text-[16px] mb-3">First Name</label>
            <input type="text" name="first_name" value="{{ old('first_name', session('first_name')) }}" class="w-full h-[58px] border @error('first_name') border-blue-500 @else border-[#7d7d7d] @enderror bg-transparent px-5 outline-none" required>
            @error('first_name')
              <p class="mt-2 text-sm text-blue-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="block text-[16px] mb-3">Last Name</label>
            <input type="text" name="last_name" value="{{ old('last_name', session('last_name')) }}" class="w-full h-[58px] border @error('last_name') border-blue-500 @else border-[#7d7d7d] @enderror bg-transparent px-5 outline-none" required>
            @error('last_name')
              <p class="mt-2 text-sm text-blue-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="block text-[16px] mb-3">Phone Number</label>
            <input type="text" name="phone_number" value="{{ old('phone_number') }}" class="w-full h-[58px] border @error('phone_number') border-blue-500 @else border-[#7d7d7d] @enderror bg-transparent px-5 outline-none" required>
            @error('phone_number')
              <p class="mt-2 text-sm text-blue-600">{{ $message }}</p>
            @enderror
          </div>

        </div>

      </section>

      <!-- CONTACT -->
      <section class="mt-24">

        <h2 class="text-[74px] font-black leading-none tracking-[-2px] text-[#222]">
          Contact Info
        </h2>

        <div class="mt-10 space-y-8">

          <div>
            <label class="block text-[16px] mb-3">Address 1</label>
            <input type="text" name="address1" value="{{ old('address1') }}" class="w-full h-[58px] border @error('address1') border-blue-500 @else border-[#7d7d7d] @enderror bg-transparent px-5 outline-none" required>
            @error('address1')
              <p class="mt-2 text-sm text-blue-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label class="block text-[16px] mb-3">Address 2</label>
            <input type="text" name="address2" value="{{ old('address2') }}" class="w-full h-[58px] border border-[#7d7d7d] bg-transparent px-5 outline-none">
          </div>

          <div>
            <label class="block text-[16px] mb-3">City</label>
            <input type="text" name="city" value="{{ old('city') }}" class="w-full h-[58px] border @error('city') border-blue-500 @else border-[#7d7d7d] @enderror bg-transparent px-5 outline-none" required>
            @error('city')
              <p class="mt-2 text-sm text-blue-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- COUNTRY -->
          <div>
            <label class="block text-[16px] mb-3">Country</label>

            <div class="relative">
              <select name="country" class="w-full h-[58px] border @error('country') border-blue-500 @else border-[#7d7d7d] @enderror bg-transparent px-5 appearance-none text-[16px] outline-none" required>
                <option value="USA" {{ old('country') == 'USA' ? 'selected' : '' }}>USA</option>
              </select>

              <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 absolute right-5 top-1/2 -translate-y-1/2 text-[#666] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
              </svg>
            </div>
            @error('country')
              <p class="mt-2 text-sm text-blue-600">{{ $message }}</p>
            @enderror
          </div>

          <!-- STATE + ZIP -->
          <div class="flex gap-9">

            <!-- STATE -->
            <div class="flex-1">
              <label class="block text-[16px] mb-3">State</label>

              <div class="relative">
                <select name="state" class="w-full h-[58px] border @error('state') border-blue-500 @else border-[#7d7d7d] @enderror bg-transparent px-5 appearance-none text-[16px] outline-none" required>
                  <option value="New York" {{ old('state') == 'New York' ? 'selected' : '' }}>New York</option>
                </select>

                <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 absolute right-5 top-1/2 -translate-y-1/2 text-[#666] pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                </svg>
              </div>
              @error('state')
                <p class="mt-2 text-sm text-blue-600">{{ $message }}</p>
              @enderror
            </div>

            <!-- ZIP -->
            <div class="flex-1">
              <label class="block text-[16px] mb-3">Postal Code</label>

              <input
                type="text"
                name="postal_code"
                value="{{ old('postal_code') }}"
                class="w-full h-[58px] border @error('postal_code') border-blue-500 @else border-[#7d7d7d] @enderror bg-transparent px-5 outline-none"
                required
              />
              @error('postal_code')
                <p class="mt-2 text-sm text-blue-600">{{ $message }}</p>
              @enderror
            </div>

          </div>

        </div>

        <!-- TERMS -->
        <p class="mt-20 text-[15px] text-[#333]">
          By registering, you agree to our
          <a href="#" class="underline">Terms of Service</a>
          and
          <a href="#" class="underline">Privacy Policy</a>.
        </p>

        <!-- BUTTON -->
        <button
          type="submit"
          class="mt-5 w-[176px] h-[58px] bg-gradient-to-b from-[#07244a] to-[#07244a] text-white text-[18px] font-semibold"
        >
          Register
        </button>

      </section>

    </form>

  </main>
  
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const input = document.getElementById('password');
      const toggle = document.getElementById('togglePassword');

      if (input && toggle) {
        toggle.addEventListener('click', () => {
          const showing = input.type === 'text';
          input.type = showing ? 'password' : 'text';
          
          if (showing) {
            toggle.innerHTML = `
              <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z"/>
              </svg>
            `;
          } else {
            toggle.innerHTML = `
              <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
              </svg>
            `;
          }
        });
      }
    });
  </script>
@endsection
