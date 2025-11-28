@props([
    'successClass' => 'bg-green-100 text-green-800 border-green-400',
    'errorClass' => 'bg-red-100 text-red-800 border-red-400',
    'containerClass' => 'flex flex-col items-center justify-center px-4'
])

@if (session('success'))
    <div class="{{ $containerClass }}">
        <div class="{{ $successClass }} border px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
            {{ session('success') }}
        </div>
    </div>
@endif

@if (session('error'))
    <div class="{{ $containerClass }}">
        <div class="{{ $errorClass }} border px-4 py-3 rounded-lg relative max-w-md w-full text-center shadow-sm" role="alert">
            {{ session('error') }}
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="{{ $containerClass }}">
        <div class="{{ $errorClass }} border px-4 py-3 rounded-lg relative max-w-md w-full shadow-sm" role="alert">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif