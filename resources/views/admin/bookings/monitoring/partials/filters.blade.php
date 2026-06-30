<div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
    <form method="GET" action="{{ request()->url() }}" class="flex flex-col gap-3 lg:flex-row lg:items-end lg:flex-wrap">
        <div class="flex-1 min-w-72">
            <label for="search" class="block text-xs font-semibold text-gray-600 mb-1">Search</label>
            <div class="relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ $search ?? '' }}"
                    placeholder="Reference, guest name, email, or contact"
                    class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                >
            </div>
        </div>

        <div class="w-full sm:w-auto">
            <label for="status" class="block text-xs font-semibold text-gray-600 mb-1">Filter</label>
            <select id="status" name="status" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                @foreach(($statusOptions ?? []) as $value => $label)
                    <option value="{{ $value }}" {{ ($statusFilter ?? 'all') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-full sm:w-auto">
            <label for="date_from" class="block text-xs font-semibold text-gray-600 mb-1">From</label>
            <input type="date" id="date_from" name="date_from" value="{{ $dateFrom ?? '' }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
        </div>

        <div class="w-full sm:w-auto">
            <label for="date_to" class="block text-xs font-semibold text-gray-600 mb-1">To</label>
            <input type="date" id="date_to" name="date_to" value="{{ $dateTo ?? '' }}" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent">
        </div>

        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 text-white text-sm rounded-lg shadow hover:bg-amber-700 transition whitespace-nowrap">
            <i data-lucide="filter" class="w-4 h-4"></i>
            <span>Apply</span>
        </button>
    </form>
</div>
