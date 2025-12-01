{{-- resources/views/components/flash-message.blade.php --}}
@if (session('success'))
    <div class="flex flex-col items-center justify-center px-4">
        <div class="bg-green-100 text-green-800 border border-green-400 px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
            {{ session('success') }}
        </div>
    </div>
@endif