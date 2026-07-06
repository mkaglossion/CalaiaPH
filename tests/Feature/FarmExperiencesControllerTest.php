<?php

namespace Tests\Feature;

use App\Models\FarmExperience;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class FarmExperiencesControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('farm_experiences')) {
            Schema::create('farm_experiences', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('desc')->nullable();
                $table->string('time')->nullable();
                $table->string('capacity')->nullable();
                $table->string('icon')->default('people');
                $table->string('cta')->nullable();
                $table->string('badge')->nullable();
                $table->string('img')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('farm_experiences');
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_save_updates_existing_rows_without_truncating_the_table(): void
    {
        $admin = new \App\Models\User();
        $admin->id = 1;
        $admin->user_type = 'admin';

        Carbon::setTestNow('2026-07-01 10:00:00');

        $first = FarmExperience::create([
            'title' => 'Original One',
            'desc' => 'First experience',
            'time' => 'Weekends',
            'capacity' => '10',
            'icon' => 'people',
            'cta' => 'Book Now',
            'badge' => 'Featured',
            'img' => 'https://example.com/one.jpg',
            'sort_order' => 0,
        ]);

        $second = FarmExperience::create([
            'title' => 'Original Two',
            'desc' => 'Second experience',
            'time' => 'Daily',
            'capacity' => '20',
            'icon' => 'leaf',
            'cta' => 'Reserve',
            'badge' => null,
            'img' => 'https://example.com/two.jpg',
            'sort_order' => 1,
        ]);

        $firstCreatedAt = $first->created_at->toDateTimeString();
        $secondCreatedAt = $second->created_at->toDateTimeString();

        Carbon::setTestNow('2026-07-02 10:00:00');

        $response = $this->actingAs($admin)->postJsonWithCsrf(route('admin.farm.experiences.save'), [
            'experiences' => [
                [
                    'id' => $first->id,
                    'title' => 'Updated One',
                    'desc' => 'First experience updated',
                    'time' => 'Weekends',
                    'capacity' => '12',
                    'icon' => 'people',
                    'cta' => 'Book Now',
                    'badge' => 'Featured',
                    'img' => 'https://example.com/one-updated.jpg',
                ],
                [
                    'id' => $second->id,
                    'title' => 'Original Two',
                    'desc' => 'Second experience',
                    'time' => 'Daily',
                    'capacity' => '20',
                    'icon' => 'leaf',
                    'cta' => 'Reserve',
                    'badge' => null,
                    'img' => 'https://example.com/two.jpg',
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseCount('farm_experiences', 2);

        $first->refresh();
        $second->refresh();

        $this->assertSame('Updated One', $first->title);
        $this->assertSame($firstCreatedAt, $first->created_at->toDateTimeString());
        $this->assertSame($secondCreatedAt, $second->created_at->toDateTimeString());
        $this->assertSame('Original Two', $second->title);

        Carbon::setTestNow();
    }
}
