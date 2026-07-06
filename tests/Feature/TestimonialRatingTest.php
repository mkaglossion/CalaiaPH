<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use App\Models\User;

class TestimonialRatingTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::factory()->create([
            'user_type' => 'admin',
        ]);
    }

    #[Test]
    public function it_accepts_ratings_from_1_to_5_in_admin_panel()
    {
        $admin = $this->createAdminUser();

        // Test each rating from 1 to 5 in admin panel
        for ($rating = 1; $rating <= 5; $rating++) {
            $response = $this->actingAs($admin)->postJsonWithCsrf('/admin/testimonials', [
                'name' => 'Test User',
                'position' => 'Guest',
                'content' => 'Great experience!',
                'rating' => $rating,
            ]);

            // Should get successful response for ratings 1-5
            $response->assertStatus(200);
            $response->assertJson(['message' => 'Testimonial added successfully.']);
        }
    }

    #[Test]
    public function it_rejects_ratings_outside_1_to_5_range_in_admin_panel()
    {
        $admin = $this->createAdminUser();

        // Test rating 0 (should fail)
        $response = $this->actingAs($admin)->postJsonWithCsrf('/admin/testimonials', [
            'name' => 'Test User',
            'position' => 'Guest',
            'content' => 'Great experience!',
            'rating' => 0,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating']);

        // Test rating 6 (should fail)
        $response = $this->actingAs($admin)->postJsonWithCsrf('/admin/testimonials', [
            'name' => 'Test User',
            'position' => 'Guest',
            'content' => 'Great experience!',
            'rating' => 6,
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['rating']);
    }
}
