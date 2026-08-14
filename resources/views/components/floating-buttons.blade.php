{{-- Reusable Floating Buttons Component --}}
<style>
    [data-floating-buttons] {
        position: fixed !important;
        right: calc(0.75rem + env(safe-area-inset-right, 0px)) !important;
        bottom: calc(0.75rem + env(safe-area-inset-bottom, 0px)) !important;
        width: 44px !important;
        min-height: 148px !important;
        z-index: 9990 !important;
        display: flex !important;
        flex-direction: column !important;
        gap: 8px !important;
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }

    [data-floating-buttons] > a,
    [data-floating-buttons] > button {
        width: 44px !important;
        height: 44px !important;
        min-height: 44px !important;
        flex: 0 0 44px !important;
    }

    @media (min-width: 640px) {
        [data-floating-buttons] {
            right: 1rem !important;
            bottom: 1rem !important;
            width: 48px !important;
            min-height: 160px !important;
        }

        [data-floating-buttons] > a,
        [data-floating-buttons] > button {
            width: 48px !important;
            height: 48px !important;
            min-height: 48px !important;
            flex-basis: 48px !important;
        }
    }
</style>

<div
    data-floating-buttons
    class="fixed bottom-3 right-3 flex flex-col gap-2 sm:bottom-4 sm:right-4"
    style="position: fixed !important; right: 12px !important; bottom: 12px !important; z-index: 2147483000 !important; display: flex !important; flex-direction: column !important; visibility: visible !important; opacity: 1 !important; pointer-events: auto !important;"
>
    {{-- Special Events button --}}
    <button
        type="button"
        onclick="navigateToBookingCalendar()"
        aria-label="Open Booking Calendar"
        class="flex items-center justify-center w-11 h-11 sm:w-12 sm:h-12 rounded-full text-white shadow-md
               hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2"
        style="background-color: #8f5d54;"
        onmouseover="this.style.backgroundColor='#a0675a'"
        onmouseout="this.style.backgroundColor='#8f5d54'"
    >
        <i class="bi bi-calendar-event text-base sm:text-lg"></i>
    </button>

    {{-- Messenger button --}}
    <a
        href="https://m.me/410774518796277"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Chat on Messenger"
        class="flex items-center justify-center w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-[#0084ff] text-white shadow-md
               hover:bg-[#0073e6] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#0084ff]"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 16 16"
            class="w-4 h-4 sm:w-5 sm:h-5"
            fill="currentColor"
        >
            <path d="M0 7.76C0 3.301 3.493 0 8 0s8 3.301 8 7.76-3.493 7.76-8 7.76c-.81 0-1.586-.107-2.316-.307a.64.64 0 0 0-.427.03l-1.588.702a.64.64 0 0 1-.898-.566l-.044-1.423a.64.64 0 0 0-.215-.456C.956 12.108 0 10.092 0 7.76m5.546-1.459-2.35 3.728c-.225.358.214.761.551.506l2.525-1.916a.48.48 0 0 1 .578-.002l1.869 1.402a1.2 1.2 0 0 0 1.735-.32l2.35-3.728c.226-.358-.214-.761-.551-.506L9.728 7.381a.48.48 0 0 1-.578.002L7.281 5.98a1.2 1.2 0 0 0-1.735.32z"/>
        </svg>
    </a>

    {{-- Scroll to top button --}}
    <button
        type="button"
        id="scrollToTopBtn"
        aria-label="Scroll to top"
        title="Scroll to top"
        class="flex items-center justify-center w-11 h-11 sm:w-12 sm:h-12 rounded-full text-white shadow-md
               hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2"
        style="background-color: #8f5d54;"
        onmouseover="this.style.backgroundColor='#a0675a'"
        onmouseout="this.style.backgroundColor='#8f5d54'"
    >
        {{-- Up arrow icon --}}
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 16 16"
            class="w-5 h-5 sm:w-6 sm:h-6"
            fill="currentColor"
            aria-hidden="true"
        >
            <path fill-rule="evenodd"
                  d="M8 3.5a.75.75 0 0 1 .53.22l4 4a.75.75 0 0 1-1.06 1.06L8.75 5.81v6.44a.75.75 0 0 1-1.5 0V5.81L4.53 8.78a.75.75 0 0 1-1.06-1.06l4-4A.75.75 0 0 1 8 3.5z" />
        </svg>
    </button>
</div>
<script>
function navigateToBookingCalendar() {
    window.location.href = '{{ url("/booking/calendar") }}';
}

function initializeFloatingButtons() {
    const floatingButtons = document.querySelector('[data-floating-buttons]');
    if (!floatingButtons) return;

    // Keep global floating controls outside page-specific stacking and overflow contexts.
    if (floatingButtons.parentElement !== document.body) {
        document.body.appendChild(floatingButtons);
    }

    const scrollBtn = document.getElementById('scrollToTopBtn');
    if (!scrollBtn || scrollBtn.dataset.initialized === 'true') return;
    scrollBtn.dataset.initialized = 'true';

    scrollBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth',
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeFloatingButtons, { once: true });
} else {
    initializeFloatingButtons();
}
</script>
