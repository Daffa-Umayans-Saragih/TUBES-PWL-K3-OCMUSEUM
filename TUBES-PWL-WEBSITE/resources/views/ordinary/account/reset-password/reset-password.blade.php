@extends('layouts.sub')

@section('title', 'Reset Password')

@section('content')
 <div class="min-h-screen bg-[#f3f3f3] font-sans flex flex-col justify-between border-t-[3px] border-[#c40024]">

  <!-- CONTENT -->
  <div class="px-[130px] pt-[70px] pb-24">

    <!-- TITLE -->
    <h1 class="text-[84px] leading-none font-black tracking-[-3px] text-[#222]">
      Reset Password
    </h1>

    <!-- DESCRIPTION -->
    <p class="mt-12 max-w-[980px] text-[18px] leading-[1.7] text-[#222]">
      Please enter your new password below.
    </p>

    <!-- FORM -->
    <form action="{{ route('password.update') }}" method="POST" class="mt-12 w-[760px]">
      @csrf

      <input type="hidden" name="token" value="{{ $token }}">
      <input type="hidden" name="email" value="{{ $email }}">

      @error('email')
        <div class="mb-8 p-4 bg-red-50 border border-red-200 text-red-800 text-[16px]">
          {{ $message }}
        </div>
      @enderror

      <!-- PASSWORD -->
      <div>
        <label class="block text-[16px] text-[#222] mb-3">
          New Password
        </label>

        <div class="relative">
          <input
            type="password"
            id="password"
            name="password"
            class="w-full h-[58px] border @error('password') border-red-500 @else border-[#7d7d7d] @enderror bg-transparent px-5 pr-16 text-[16px] outline-none"
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
          <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
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

      <!-- SUBMIT -->
      <div class="mt-12 flex flex-col gap-7 w-[175px]">
        <button
          type="submit"
          class="h-[58px] bg-gradient-to-b from-[#ef002d] to-[#b70024] text-white text-[18px] font-semibold hover:opacity-95 transition"
        >
          Reset Password
        </button>
      </div>

    </form>

  </div>

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
