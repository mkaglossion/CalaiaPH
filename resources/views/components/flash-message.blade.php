{{-- resources/views/components/flash-message.blade.php --}}

@props(['type' => null, 'message' => null])

@php
    /**
     * Supported types: success, error, info, warning
     * Validation errors are shown as "error"
     */

    $messages = collect();

    // If type and message props are provided, use only those
    if ($type && $message) {
        $messages->push([
            'type' => $type,
            'text' => $message,
        ]);
    } else {
        // Otherwise, collect from errors and session
        if ($errors->any()) {
            // Show all errors
            foreach ($errors->all() as $error) {
                $messages->push([
                    'type' => 'error',
                    'text' => $error,
                ]);
            }
        }

        foreach (['success', 'error', 'info', 'warning'] as $t) {
            if (session()->has($t)) {
                $messages->push([
                    'type' => $t,
                    'text' => session($t),
                ]);
            }
        }
    }

    $styles = [
        'success' => [
            'wrap' => 'bg-green-50 text-green-800 border-green-200',
            'btn'  => 'text-green-800 hover:text-green-600 focus:ring-green-400',
            'role' => 'status',
            'icon' => 'fas fa-check-circle',
        ],
        'error' => [
            'wrap' => 'bg-red-50 text-red-800 border-red-200',
            'btn'  => 'text-red-800 hover:text-red-600 focus:ring-red-400',
            'role' => 'alert',
            'icon' => 'fas fa-exclamation-circle',
        ],
        'info' => [
            'wrap' => 'bg-blue-50 text-blue-800 border-blue-200',
            'btn'  => 'text-blue-800 hover:text-blue-600 focus:ring-blue-400',
            'role' => 'status',
            'icon' => 'fas fa-info-circle',
        ],
        'warning' => [
            'wrap' => 'bg-yellow-50 text-yellow-900 border-yellow-200',
            'btn'  => 'text-yellow-900 hover:text-yellow-700 focus:ring-yellow-400',
            'role' => 'status',
            'icon' => 'fas fa-exclamation-triangle',
        ],
    ];

    // Default auto-dismiss timeout (ms). Set to 0 to disable.
    // You can override per-message with ['timeout' => 5000]
    $defaultTimeout = 0;
@endphp

@if ($messages->isNotEmpty())
    <div
        id="flash-messages"
        class="fixed top-4 right-4 left-auto z-9999 space-y-3 flex flex-col items-end w-auto max-w-4xl"
        data-controller="flash"
        {{-- Always right-aligned. If you see a left-aligned flash-message, check for duplicate component or conflicting CSS. --}}
    >
        @foreach ($messages as $i => $msg)
            @php
                $type = $msg['type'];
                $cfg  = $styles[$type] ?? $styles['info'];
                $id   = "flash-{$type}-{$i}";
                $timeout = $msg['timeout'] ?? $defaultTimeout;
                $icon = $cfg['icon'] ?? 'fas fa-info-circle';
            @endphp

            <div
                id="{{ $id }}"
                class="flash-item relative rounded-lg border px-4 py-3 shadow-sm {{ $cfg['wrap'] }} inline-block w-auto max-w-4xl"
                role="{{ $cfg['role'] }}"
                aria-live="{{ $cfg['role'] === 'alert' ? 'assertive' : 'polite' }}"
                data-timeout="{{ (int) $timeout }}"
            >
                <div class="flex items-center">
                    <i class="{{ $icon }} text-lg"></i>
                    <div class="ml-3 pr-12 text-sm leading-5 whitespace-nowrap overflow-auto">
                        {{ $msg['text'] }}
                    </div>
                </div>

                <button
                    type="button"
                    class="flash-close absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-md focus:outline-none focus:ring-2 {{ $cfg['btn'] }}"
                    aria-label="Dismiss notification"
                    data-target="{{ $id }}"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endforeach
    </div>

    <script>
        (function () {
            const container = document.getElementById('flash-messages');
            if (!container) return;

            const removeWithFade = (el) => {
                if (!el) return;
                el.style.transition = 'opacity 200ms ease, transform 200ms ease';
                el.style.opacity = '0';
                el.style.transform = 'translateY(-4px)';
                window.setTimeout(() => el.remove(), 220);
            };

            // Close button (event delegation)
            container.addEventListener('click', (e) => {
                const btn = e.target.closest('.flash-close');
                if (!btn) return;

                const id = btn.getAttribute('data-target');
                const el = document.getElementById(id);
                removeWithFade(el);
            });

            // Auto-dismiss per item (data-timeout="0" disables)
            container.querySelectorAll('.flash-item').forEach((item) => {
                const timeout = parseInt(item.getAttribute('data-timeout') || '0', 10);
                if (!timeout) return;

                window.setTimeout(() => removeWithFade(item), timeout);
            });
        })();
    </script>
@endif