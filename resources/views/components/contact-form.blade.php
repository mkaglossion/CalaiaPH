{{-- resources/views/components/contact-form.blade.php --}}
@props([
    'action' => '',
    'title' => 'Send a Message',
    'submitText' => 'Send Message'
])

<div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
    <h3 class="font-semibold text-[#8f5d54] mb-4" style="font-size: 20px;">{{ $title }}</h3>
    <form method="POST" action="{{ $action }}" class="space-y-4" role="form" aria-label="Contact form">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                <input id="name" name="name" type="text" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                       style="font-size: 13px;"
                       placeholder="Your full name">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                <input id="email" name="email" type="email" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                       style="font-size: 13px;"
                       placeholder="your@email.com">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                <input id="phone" name="phone" type="tel"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors"
                       style="font-size: 13px;"
                       placeholder="+63 XXX XXX XXXX">
            </div>
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <select id="subject" name="subject"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors bg-white"
                        style="font-size: 13px;">
                    <option value="" selected disabled>Select an option</option>
                    <option value="Visit">Visit</option>
                    <option value="Experience Booking">Experience Booking</option>
                    <option value="Partnership">Partnership</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>
        <div>
            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
            <textarea id="message" name="message" rows="20"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:border-transparent transition-colors resize-vertical"
                      style="font-size: 13px;"
                      placeholder="Tell us about your inquiry...">
            </textarea>
        </div>
        <button type="submit"
                class="w-full bg-brand hover:bg-[#b57c63] text-white font-medium py-3 px-6 rounded-lg transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-[#8f5d54] focus:ring-offset-2">
            {{ $submitText }}
        </button>
    </form>
</div>