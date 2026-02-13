@extends('layouts.admin')

@section('title', 'View User')

@section('content')

<div class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl">

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        View User
    </h1>
    <p class="text-gray-600 mb-10">Detailed information about the selected user.</p>

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">User Details</h2>
            <p class="text-gray-500 text-sm">Complete profile information.</p>
        </div>
        <div class="space-x-4">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
                Edit User
            </a>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-xl bg-gray-500 text-white font-medium shadow hover:bg-gray-600 hover:shadow-lg transition">
                Back to Users
            </a>
        </div>
    </div>

    <div class="bg-white/60 backdrop-blur rounded-2xl border border-gray-200 shadow-sm p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                <p class="text-gray-900 font-medium">{{ $user->first_name }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                <p class="text-gray-900 font-medium">{{ $user->last_name }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <p class="text-gray-900">{{ $user->email }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Mobile Number</label>
                <p class="text-gray-900">{{ $user->mobile_number ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">User Type</label>
                <p class="text-gray-900">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $user->user_type == 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                        {{ ucfirst($user->user_type) }}
                    </span>
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                <p class="text-gray-900">{{ $user->username ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                <p class="text-gray-900">{{ $user->address ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Zip Code</label>
                <p class="text-gray-900">{{ $user->zip_code ?? 'N/A' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date of Birth</label>
                <p class="text-gray-900">{{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('M d, Y') : 'N/A' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Created At</label>
                <p class="text-gray-500 text-sm">{{ $user->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Updated At</label>
                <p class="text-gray-500 text-sm">{{ $user->updated_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
    </div>
</div>

@endsection