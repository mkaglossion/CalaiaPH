@extends('layouts.admin')  {{-- assuming your layout file is layouts/admin.blade.php --}}

@section('title', 'Dashboard')

@section('content')
  <div class="space-y-4">
    <h1 class="text-3xl font-semibold text-gray-800">Welcome to Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
      <div class="p-6 bg-white rounded-lg shadow">
        <h2 class="text-lg font-bold text-[#8f5d54]">Users</h2>
        <p class="text-gray-600 mt-2">Total: 120</p>
      </div>

      <div class="p-6 bg-white rounded-lg shadow">
        <h2 class="text-lg font-bold text-[#8f5d54]">Bookings</h2>
        <p class="text-gray-600 mt-2">Active: 45</p>
      </div>

      <div class="p-6 bg-white rounded-lg shadow">
        <h2 class="text-lg font-bold text-[#8f5d54]">Revenue</h2>
        <p class="text-gray-600 mt-2">$12,430</p>
      </div>
    </div>
  </div>
@endsection
