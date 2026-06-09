@extends('layouts.sub')

@section('title', 'Check Account')

@section('content')
 <div class="min-h-screen bg-[#f3f3f3] font-sans flex flex-col justify-between border-t-[3px] border-[#c40024]">

  <!-- CONTENT -->
  <div class="px-[130px] pt-[70px] pb-24">

    <!-- TITLE -->
    <h1 class="text-[84px] leading-none font-black tracking-[-3px] text-[#222]">
      Check for an Account
    </h1>

    <!-- DESCRIPTION -->
    <p class="mt-12 max-w-[980px] text-[18px] leading-[1.7] text-[#222]">
      Please enter your email address, first name and last name below and click Submit.
      If you already have an account in our system you will receive an email within
      five minutes to reset your password.
    </p>

    @if(session('success'))
      <div class="mt-8 p-4 bg-green-50 border border-green-200 text-green-800 text-[16px] w-[760px]">
        {{ session('success') }}
      </div>
    @endif

    <!-- FORM -->
    <form action="{{ route('account.account-check.submit') }}" method="POST" class="mt-12 w-[760px]">
      @csrf

      <!-- EMAIL -->
      <div>
        <label class="block text-[16px] text-[#222] mb-3">
          Email Address
        </label>

        <input
          type="email"
          name="email"
          value="{{ old('email') }}"
          placeholder="Email Address"
          class="w-full h-[58px] border border-[#7c7c7c] bg-transparent px-5 text-[16px] text-[#444] outline-none @error('email') border-blue-500 @enderror"
          required
        />
        @error('email')
          <p class="text-blue-500 text-sm mt-1">{{ $message }}</p>
        @enderror
      </div>

      <!-- NAME ROW -->
      <div class="flex gap-9 mt-7">

        <!-- FIRST -->
        <div class="flex-1">
          <label class="block text-[16px] text-[#222] mb-3">
            First Name
          </label>

          <input
            type="text"
            name="first_name"
            value="{{ old('first_name') }}"
            placeholder="First Name"
            class="w-full h-[58px] border border-[#7c7c7c] bg-transparent px-5 text-[16px] text-[#444] outline-none @error('first_name') border-blue-500 @enderror"
            required
          />
          @error('first_name')
            <p class="text-blue-500 text-sm mt-1">{{ $message }}</p>
          @enderror
        </div>

        <!-- LAST -->
        <div class="flex-1">
          <label class="block text-[16px] text-[#222] mb-3">
            Last Name
          </label>

          <input
            type="text"
            name="last_name"
            value="{{ old('last_name') }}"
            placeholder="Last Name"
            class="w-full h-[58px] border border-[#7c7c7c] bg-transparent px-5 text-[16px] text-[#444] outline-none @error('last_name') border-blue-500 @enderror"
            required
          />
          @error('last_name')
            <p class="text-blue-500 text-sm mt-1">{{ $message }}</p>
          @enderror
        </div>

      </div>

      <!-- BUTTONS -->
      <div class="mt-8 flex flex-col gap-7 w-[175px]">

        <!-- SUBMIT -->
        <button
          type="submit"
          class="h-[58px] bg-gradient-to-b from-[#07244a] to-[#07244a] text-white text-[18px] font-semibold hover:opacity-95 transition"
        >
          Submit
        </button>

        <!-- BACK -->
        <a href="{{ route('account.login') }}"
          class="h-[58px] border-[4px] border-[#333] text-[#222] text-[18px] font-medium bg-white hover:bg-[#f7f7f7] transition flex items-center justify-center text-center"
        >
          Back to Login
        </a>

      </div>

    </form>

  </div>

  

@endsection
