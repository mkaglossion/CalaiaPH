<x-page-layout
    title="Contact Us - Calaia Farm"
    description="We'd love to hear from you. Reach out to plan your visit or ask any questions about Calaia Farm."
    keywords="Calaia Farm, contact, Calaca Batangas, farm inquiries, booking contact, Philippines farm"
    active-header="contact"
>
    {{-- Centralized Hero Banner --}}
    <div class="min-h-screen bg-white">
        <section class="relative h-screen w-full overflow-hidden bg-linear-to-br from-amber-900 via-amber-800 to-amber-950">
            {{-- Content --}}
            <div class="relative z-10 flex h-full flex-col items-center justify-center px-4 text-center">
                <div class="max-w-4xl space-y-8">
                    <h1 class="text-5xl font-black text-white drop-shadow-lg sm:text-6xl md:text-7xl">
                        <span class="text-amber-300">Get In Touch</span>
                    </h1>

                    <p class="text-xl font-bold leading-relaxed text-white drop-shadow-md md:text-2xl">
                        We'd love to hear from you.
                        <span class="block font-black text-amber-300">Reach out to plan your visit or ask any questions.</span>
                    </p>
                </div>
            </div>
        </section>

        {{-- CONTENT --}}
        <section class="bg-white py-12 sm:py-16 pb-0">
            <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
                {{-- SECTION: Visit or Contact Us --}}
                <x-contact-section />
            </div>
        </section>

        {{-- Floating Buttons --}}
        <x-floating-buttons />
    </div>
</x-page-layout>