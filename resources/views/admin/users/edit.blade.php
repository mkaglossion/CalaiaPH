@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')

<div class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl">

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        Edit User
    </h1>
    <p class="text-gray-600 mb-10">Modify user information and settings.</p>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">User Information</h2>
            <p class="text-gray-500 text-sm">Update the user's details below.</p>
        </div>
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 rounded-xl bg-gray-500 text-white font-medium shadow hover:bg-gray-600 hover:shadow-lg transition">
            Back to Users
        </a>
    </div>

    <div class="bg-white/60 backdrop-blur rounded-2xl border border-gray-200 shadow-sm p-8">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                    <input type="text" name="first_name" id="first_name" value="{{ old('first_name', $user->first_name) }}" 
                           class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                </div>

                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                    <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}" 
                           class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" 
                           class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                </div>

                <div>
                    <label for="mobile_number" class="block text-sm font-medium text-gray-700 mb-2">Mobile Number</label>
                    <input type="text" name="mobile_number" id="mobile_number" value="{{ old('mobile_number', $user->mobile_number) }}" 
                           class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2">
                </div>

                <div>
                    <label for="user_type" class="block text-sm font-medium text-gray-700 mb-2">User Type</label>
                    <select name="user_type" id="user_type" 
                            class="block w-full border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 px-3 py-2" required>
                        <option value="user" {{ old('user_type', $user->user_type) == 'user' ? 'selected' : '' }}>User</option>
                        <option value="admin" {{ old('user_type', $user->user_type) == 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <a href="{{ route('admin.users.show', $user->id) }}" class="px-4 py-2 rounded-xl bg-gray-300 text-gray-800 font-medium shadow hover:bg-gray-400 hover:shadow-lg transition">
                    Cancel
                </a>
                <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 text-white font-medium shadow hover:bg-blue-700 hover:shadow-lg transition">
                    <i class="fas fa-save mr-2"></i>Update User
                </button>
            </div>
        </form>
    </div>
</div>

@endsection