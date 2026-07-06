<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AccommodationTextController;
use App\Http\Controllers\Admin\TestimonialController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminDeleteSafetyTest extends TestCase
{
    public function test_testimonial_delete_is_idempotent_when_row_is_missing(): void
    {
        $this->ensureTable('testimonials');

        Route::post('/__codex/testimonials/{id}/destroy', [TestimonialController::class, 'destroy']);

        $response = $this->postJsonWithCsrf('/__codex/testimonials/999/destroy');

        $response->assertOk();
        $response->assertJson([
            'message' => 'Testimonial already deleted.',
        ]);
    }

    public function test_accommodation_text_delete_is_idempotent_when_row_is_missing(): void
    {
        $this->ensureTable('accommodation_texts');

        Route::post('/__codex/accommodation-texts/{id}/destroy', [AccommodationTextController::class, 'destroy']);

        $response = $this->postWithCsrf('/__codex/accommodation-texts/999/destroy');

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Text deleted successfully!');
    }

    private function ensureTable(string $table): void
    {
        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }
}
