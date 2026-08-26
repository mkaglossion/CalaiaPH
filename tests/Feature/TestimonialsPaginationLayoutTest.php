<?php

namespace Tests\Feature;

use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialsPaginationLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagination_is_centered_outside_the_testimonial_swiper(): void
    {
        Testimonial::create([
            'name' => 'Layout Guest',
            'position' => 'Family Visitor',
            'content' => 'A wonderful stay at Calaia.',
            'rating' => 5,
            'order' => 1,
            'status' => 'approved',
        ]);

        $html = (string) $this->blade('<x-testimonials-cards />');

        $document = new \DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();

        $xpath = new \DOMXPath($document);
        $pagination = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' testimonials-pagination ')]");

        $this->assertSame(1, $pagination->length);
        $this->assertSame(
            0,
            $xpath->query("ancestor::*[contains(concat(' ', normalize-space(@class), ' '), ' testimonialsSwiper ')]", $pagination->item(0))->length
        );
        $this->assertStringContainsString('position: static;', $html);
        $this->assertStringContainsString("el: carousel.querySelector('.testimonials-pagination')", $html);
        $this->assertStringNotContainsString('class="swiper-pagination bottom-2!"', $html);
    }
}
