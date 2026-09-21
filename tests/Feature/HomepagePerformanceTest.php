<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomepagePerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_keeps_queries_bounded_without_duplicate_testimonial_loading(): void
    {
        config()->set('weather.api_key', '');
        $queries = [];

        DB::listen(function ($query) use (&$queries): void {
            $queries[] = preg_replace('/\s+/', ' ', trim($query->sql));
        });

        $response = $this->get('/');
        $testimonialQueries = array_filter(
            $queries,
            fn (string $sql): bool => str_contains($sql, 'from "testimonials"')
                || str_contains($sql, 'from `testimonials`')
        );

        $response->assertOk();
        $this->assertLessThanOrEqual(30, count($queries));
        $this->assertCount(1, $testimonialQueries);
    }

    public function test_homepage_loads_shared_swiper_assets_only_once(): void
    {
        config()->set('weather.api_key', '');

        $html = $this->get('/')->getContent();

        $this->assertSame(1, substr_count($html, 'swiper-bundle.min.css'));
        $this->assertSame(1, substr_count($html, 'swiper-bundle.min.js'));
    }
}
