@extends('layouts.admin')

@section('content')
<div class="container mx-auto p-6">

    <h1 class="text-2xl font-bold mb-6">Pricing Config</h1>

    @if(session('success'))
        <div class="bg-green-200 p-3 mb-4 rounded">{{ session('success') }}</div>
    @endif

    {{-- ========================= --}}
    {{-- Section 1: Weekday & Weekend --}}
    {{-- ========================= --}}
    <div class="mb-6 bg-white p-4 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">Set Weekday & Weekend</h2>
        <form action="{{ route('admin.dayTypes.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-7 gap-2">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $i => $abbr)
                    <label class="flex flex-col items-center">
                        <input type="checkbox" name="weekend[]" value="{{ $i }}" 
                            {{ in_array($i, $weekendDays ?? []) ? 'checked' : '' }}>
                        <span class="mt-1">{{ $abbr }}</span>
                    </label>
                @endforeach
            </div>
            <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Days</button>
        </form>
    </div>

    {{-- ========================= --}}
    {{-- Section 2: Durations for Non-Peak / Peak / Super-Peak --}}
    {{-- ========================= --}}
    <div class="mb-6 bg-white p-4 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">Set Durations</h2>
        <form action="{{ route('admin.peakPeriods.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-3 gap-4">

                @foreach($peakTypes as $peakType)
                <div class="border p-3 rounded">
                    <h3 class="font-semibold mb-2">{{ $peakType->name }}</h3>
                    <label>Start Date</label>
                    <input type="date" name="start_date[{{ $peakType->id }}]" class="border rounded w-full p-2 mb-2">
                    <label>End Date</label>
                    <input type="date" name="end_date[{{ $peakType->id }}]" class="border rounded w-full p-2 mb-2">
                </div>
                @endforeach

            </div>
            <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Durations</button>
        </form>
    </div>

    {{-- ========================= --}}
    {{-- Section 3: Holidays --}}
    {{-- ========================= --}}
    <div class="mb-6 bg-white p-4 rounded shadow">
        <h2 class="text-xl font-semibold mb-4">Declare Holidays</h2>
        <form action="{{ route('admin.holidays.store') }}" method="POST">
            @csrf
            <div class="grid grid-cols-2 gap-4">

                <div>
                    <label class="block font-semibold">Holiday Name</label>
                    <input type="text" name="name" class="border rounded w-full p-2" required>
                </div>

                <div>
                    <label class="block font-semibold">Holiday Date</label>
                    <input type="date" name="date" class="border rounded w-full p-2" required>
                </div>

            </div>
            <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add Holiday</button>
        </form>

        {{-- Existing Holidays Table --}}
        <div class="mt-4">
            <table class="w-full table-auto border-collapse border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border px-2 py-1">Holiday Name</th>
                        <th class="border px-2 py-1">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($holidays as $holiday)
                    <tr>
                        <td class="border px-2 py-1">{{ $holiday->name }}</td>
                        <td class="border px-2 py-1">{{ $holiday->date }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
