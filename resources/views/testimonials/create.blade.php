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
                <div class="flex items-center space-x-1">
                    <!-- Star rating system - visually shows 5 stars but allows 1-5 selection -->
                    <input type="hidden" name="rating" id="rating-value" value="1" required>
                    <div class="flex space-x-1" id="star-rating">
                        <button type="button" class="star-btn text-3xl text-gray-300 hover:text-yellow-400" data-value="1">★</button>
                        <button type="button" class="star-btn text-3xl text-gray-300 hover:text-yellow-400" data-value="2">★</button>
                        <button type="button" class="star-btn text-3xl text-gray-300 hover:text-yellow-400" data-value="3">★</button>
                        <button type="button" class="star-btn text-3xl text-gray-300 hover:text-yellow-400" data-value="4">★</button>
                        <button type="button" class="star-btn text-3xl text-gray-300 hover:text-yellow-400" data-value="5">★</button>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-2">Rate your experience from 1 to 5 stars</p>
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const starButtons = document.querySelectorAll('.star-btn');
        const ratingInput = document.getElementById('rating-value');

        starButtons.forEach(button => {
            button.addEventListener('click', function() {
                const value = this.getAttribute('data-value');
                ratingInput.value = value;

                // Update star colors
                starButtons.forEach((star, index) => {
                    if (index < value) {
                        star.classList.remove('text-gray-300');
                        star.classList.add('text-yellow-400');
                    } else {
                        star.classList.remove('text-yellow-400');
                        star.classList.add('text-gray-300');
                    }
                });
            });
        });

        // Initialize with 1 star selected by default
        const defaultRating = 1;
        ratingInput.value = defaultRating;

        starButtons.forEach((star, index) => {
            if (index < defaultRating) {
                star.classList.remove('text-gray-300');
                star.classList.add('text-yellow-400');
            }
        });
    });
</script>
</x-page-layout>