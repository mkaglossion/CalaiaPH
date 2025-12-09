<x-page-layout
    title="Contact Us - Calaia Farm"
    description="We'd love to hear from you. Reach out to plan your visit or ask any questions about Calaia Farm."
    keywords="Calaia Farm, contact, Calaca Batangas, farm inquiries, booking contact, Philippines farm"
    active-header="contact"
>
    <!-- Hero Section -->
    <section class="relative h-[70vh] bg-cover bg-center flex items-center justify-center text-white overflow-hidden"
             style="background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('{{ asset('images/facilities/IMG_29422.svg') }}')">
        <div class="absolute inset-0 bg-linear-to-br from-amber-900/30 to-amber-700/30 animate-pulse"></div>
        <div class="relative z-10 text-center px-4 animate-fade-in">
            <h1 class="text-5xl md:text-6xl font-bold mb-4 drop-shadow-lg">Contact Us</h1>
            <p class="text-xl md:text-2xl max-w-2xl mx-auto drop-shadow-md">
                 We'd love to hear from you. Reach out to plan your visit or ask any questions about Calaia Farm.
            </p>
        </div>
    </section>

    {{-- SECTION: Visit or Contact Us --}}
    <x-contact-section />
</x-page-layout>