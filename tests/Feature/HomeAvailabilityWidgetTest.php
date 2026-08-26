<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomeAvailabilityWidgetTest extends TestCase
{
    public function test_home_widget_temporarily_shows_only_dates_and_check_availability(): void
    {
        $html = view('components.check-availability', [
            'inventory' => [],
            'totalQuantity' => 0,
            'dayTourInventory' => [],
            'totalDayTourQuantity' => 0,
        ])->render();

        $this->assertStringContainsString('md:grid-cols-3', $html);
        $this->assertStringContainsString('id="check_in"', $html);
        $this->assertStringContainsString('id="check_out"', $html);
        $this->assertStringContainsString('id="check-availability-btn"', $html);
        $this->assertStringContainsString('Check Availability', $html);

        $this->assertStringNotContainsString('id="guests-panel"', $html);
        $this->assertStringNotContainsString('Rooms, Adults, Children &amp; Pets', $html);
        $this->assertStringNotContainsString('Check-in Time</p>', $html);
        $this->assertStringNotContainsString('Check-out Time</p>', $html);
        $this->assertStringNotContainsString('ROOM DETAILS', $html);
        $this->assertStringNotContainsString('<p class="text-xs text-gray-500 font-semibold mb-0.5">GUESTS</p>', $html);

        // Hidden defaults remain for the existing booking flow.
        $this->assertStringContainsString('name="rooms_count"', $html);
        $this->assertStringContainsString('name="adults_total"', $html);
    }
}
