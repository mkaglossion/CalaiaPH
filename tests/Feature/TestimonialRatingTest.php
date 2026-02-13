<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TestimonialRatingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_accepts_ratings_from_1_to_5()
    {
        // Test each rating from 1 to 5
        for ($rating = 1; $rating <= 5; $rating++) {
            $response = $this->post('/testimonials', [
                'name' => 'Test User',
                'position' => 'Guest',
                'content' => 'Great experience!',
                'rating' => $rating,
            ]);

            // Should not get validation errors for ratings 1-5
            $response->assertSessionDoesntHaveErrors(['rating']);
        }
    }

    #[Test]
    public function it_rejects_ratings_outside_1_to_5_range()
    {
        // Test rating 0 (should fail)
        $response = $this->from('/testimonials/create')->post('/testimonials', [
            'name' => 'Test User',
            'position' => 'Guest',
            'content' => 'Great experience!',
            'rating' => 0,
        ]);
        $response->assertSessionHasErrors(['rating']);

        // Test rating 6 (should fail)
        $response = $this->from('/testimonials/create')->post('/testimonials', [
            'name' => 'Test User',
            'position' => 'Guest',
            'content' => 'Great experience!',
            'rating' => 6,
        ]);
        $response->assertSessionHasErrors(['rating']);
    }
}
