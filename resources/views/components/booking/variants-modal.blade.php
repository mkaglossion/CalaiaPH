{{-- Dynamic Variants Modal --}}
<div id="variants-modal" class="fixed inset-0 z-9999 hidden">
    <!-- Backdrop -->
    <div
        class="absolute inset-0 bg-black/40"
        onclick="closeModal('variants-modal')">
    </div>

    <!-- Modal content -->
    <div class="relative flex items-center justify-center min-h-screen px-4">
        <div
            class="bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[95vh] overflow-hidden z-10"
            onclick="event.stopPropagation()">

            <div class="p-6 flex flex-col h-full">
                <div class="flex justify-between items-center mb-6">
                    <h2 id="variants-modal-title"
                        class="text-gray-900 text-2xl">
                        Select Options
                    </h2>

                    <!-- ❌ CLOSE BUTTON -->
                    <button
                        type="button"
                        onclick="closeModal('variants-modal')"
                        class="text-3xl leading-none text-gray-500 hover:text-gray-800">
                        ×
                    </button>
                </div>

                <!-- Variants container scrollable -->
                <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-6 overflow-y-auto flex-1 variants-scroll"
                    id="variants-container">
                </div>

                <div class="mt-6 pt-4 border-t flex justify-end">
                    <button
                        type="button"
                        onclick="closeModal('variants-modal')"
                        class="px-6 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg">
                        Close
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>