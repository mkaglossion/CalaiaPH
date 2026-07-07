@extends('layouts.admin-guest')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4 py-12 sm:py-16">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md sm:max-w-sm p-6 sm:p-8 text-gray-800 relative mx-auto">
    @if(session('error'))
      <div
        data-admin-flash
        data-admin-flash-library="swal"
        data-admin-flash-title="Error!"
        data-admin-flash-type="error"
        data-admin-flash-message="{{ e(session('error')) }}"
      ></div>
    @endif
    @if(session('success'))
      <div
        data-admin-flash
        data-admin-flash-library="swal"
        data-admin-flash-title="Success!"
        data-admin-flash-type="success"
        data-admin-flash-message="{{ e(session('success')) }}"
      ></div>
    @endif

    <h2 class="text-2xl sm:text-3xl font-semibold mb-4 text-center" style="color:#8f5d54;">Admin Log In</h2>
    <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-4">
      @csrf
      <div>
        <label for="login_username" class="block text-sm font-medium text-gray-700 mb-1">Username or Email</label>
        <input type="text" id="login_username" name="username" placeholder="Enter your username or email" autocomplete="username" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none" required>
      </div>
      <div>
        <label for="login_password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" id="login_password" name="password" placeholder="Enter your password" class="w-full p-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#8f5d54] focus:border-[#8f5d54] outline-none" required autocomplete="current-password">
      </div>
      <button type="submit" class="w-full py-3 rounded-lg font-semibold hover:opacity-90 transition-all shadow-md text-white" style="background-color:#8f5d54;">Log In</button>
    </form>

    <div class="text-center mt-4">
      <a href="{{ route('forgot.password') }}" style="color:#8f5d54;" class="text-sm hover:underline">Forgot your password?</a>
    </div>
  </div>
</div>
@endsection
