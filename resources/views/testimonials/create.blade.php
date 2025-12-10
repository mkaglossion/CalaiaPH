<x-page-layout
    title="Share Your Experience - Calaia Farm"
    description="Share your experience at Calaia Farm. Your testimonial helps others discover the magic of our farm."
    keywords="Calaia Farm, testimonial, review, farm experience, customer feedback"
    active-header="home"
>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Share Your Experience</h1>
        <p class="text-gray-600 mb-8">We'd love to hear about your experience at Calaia! Your testimonial helps others discover the magic of our farm.</p>

        <form method="POST" action="{{ route('testimonials.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Your Name</label>
                <input type="text" name="name" id="name" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Enter your full name">
            </div>

            <div>
                <label for="position" class="block text-sm font-medium text-gray-700 mb-1">Your Position/Title</label>
                <input type="text" name="position" id="position" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="e.g., Guest, Visitor, Customer">
            </div>

            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 mb-1">Your Testimonial</label>
                <textarea name="content" id="content" rows="5" required
                          class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                          placeholder="Share your experience, what you loved, and why you'd recommend Calaia"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                <div class="flex items-center space-x-2">
                    <!-- Only show 5-star option since only perfect ratings are allowed -->
                    <label class="flex items-center">
                        <input type="radio" name="rating" value="5" required checked
                               class="sr-only peer">
                        <div class="w-10 h-10 flex items-center justify-center bg-gray-100 rounded-full peer-checked:bg-yellow-400 peer-checked:text-white">
                            <span class="text-lg">5</span>
                        </div>
                    </label>
                </div>
                <p class="text-sm text-gray-500 mt-2">Only 5-star experiences can be shared (perfect ratings only)</p>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Submit Testimonial
                </button>
            </div>
        </form>
    </div>
</div>
</x-page-layout>