@extends('layouts.admin')

@section('title', 'Manage Users')

@section('content')

<div class="p-8 bg-linear-to-b from-gray-50 to-gray-100 min-h-screen rounded-xl">

    <h1 class="text-4xl font-extrabold mb-3 tracking-tight text-gray-900">
        User Management
    </h1>
    <p class="text-gray-600 mb-6">View and manage user accounts.</p>

    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 rounded-xl bg-gray-500 text-white font-medium shadow hover:bg-gray-600 hover:shadow-lg transition">
            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- ============================================================= --}}
    {{-- USER ACCOUNTS SECTION --}}
    {{-- ============================================================= --}}
    <div class="flex justify-between items-end mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">User Accounts</h2>
            <p class="text-gray-500 text-sm">Manage registered users and their information.</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 shadow-sm bg-white/60 backdrop-blur">
        <table class="w-full text-left">
            <thead class="bg-gray-100/70 backdrop-blur">
                <tr>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">ID</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Name</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Email</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">User Type</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700">Created At</th>
                    <th class="px-6 py-4 text-sm font-semibold text-gray-700 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $user->id }}</td>
                        <td class="px-6 py-4 text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</td>
                        <td class="px-6 py-4 text-gray-600 text-sm">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-gray-600 text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $user->user_type == 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                {{ ucfirst($user->user_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-sm">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-right space-x-4">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition">
                                View
                            </a>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-600 font-semibold hover:text-blue-700 hover:underline transition">
                                Edit
                            </a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline" 
                                  onsubmit="return confirm('Are you sure you want to delete this user?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 font-semibold hover:text-red-700 hover:underline transition">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                            No users found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>

@endsection
