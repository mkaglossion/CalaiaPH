<?php

namespace Tests\Unit\Services;

use App\Models\DayTour;
use App\Models\DayTourInventory;
use App\Models\Staycation;
use App\Models\StaycationInventory;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $inventoryService;
    private int $staycationId;
    private int $dayTourId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryService = app(InventoryService::class);
        $this->staycationId = Staycation::create(['name' => 'Test Cabin', 'base_quantity' => 5])->id;
        $this->dayTourId = DayTour::create(['name' => 'Test Tour'])->id;
    }

    public function test_checks_all_nights_in_a_staycation_range(): void
    {
        $this->seedStaycationInventory('2030-01-01', 3);
        $this->seedStaycationInventory('2030-01-02', 3);

        $this->assertTrue($this->inventoryService->checkStaycationAvailability($this->staycationId, '2030-01-01', '2030-01-03', 2));
        $this->assertFalse($this->inventoryService->checkStaycationAvailability($this->staycationId, '2030-01-01', '2030-01-03', 4));
    }

    public function test_reserve_and_release_update_each_night(): void
    {
        $this->seedStaycationInventory('2030-01-01', 5);
        $this->seedStaycationInventory('2030-01-02', 5);

        $this->inventoryService->reserveStaycationInventory($this->staycationId, '2030-01-01', '2030-01-03', 2, 10);
        $this->assertSame(3, $this->inventory('2030-01-01')->available_quantity);

        $this->inventoryService->releaseStaycationInventory($this->staycationId, '2030-01-01', '2030-01-03', 1, 10);
        $this->assertSame(4, $this->inventory('2030-01-02')->available_quantity);
    }

    public function test_gets_minimum_available_quantity_in_range(): void
    {
        $this->seedStaycationInventory('2030-01-01', 5);
        $this->seedStaycationInventory('2030-01-02', 2);

        $this->assertSame(2, $this->inventoryService->getMinAvailableInDateRange($this->staycationId, '2030-01-01', '2030-01-03'));
    }

    public function test_day_tour_availability_uses_available_groups(): void
    {
        DayTourInventory::create(['day_tour_id' => $this->dayTourId, 'date' => '2030-01-01', 'max_groups' => 5, 'booked_groups' => 3]);

        $this->assertTrue($this->inventoryService->checkDayTourAvailability($this->dayTourId, '2030-01-01', 2));
        $this->assertFalse($this->inventoryService->checkDayTourAvailability($this->dayTourId, '2030-01-01', 3));
    }

    public function test_missing_day_tour_inventory_defaults_to_available(): void
    {
        $this->assertTrue($this->inventoryService->checkDayTourAvailability($this->dayTourId, '2030-01-01', 1));
    }

    public function test_past_staycation_dates_are_rejected(): void
    {
        $this->expectExceptionMessage('past');
        $this->inventoryService->checkStaycationAvailability($this->staycationId, '2020-01-01', '2020-01-02', 1);
    }

    private function seedStaycationInventory(string $date, int $available): void
    {
        StaycationInventory::create([
            'staycation_id' => $this->staycationId,
            'date' => Carbon::parse($date)->toDateString(),
            'available_quantity' => $available,
        ]);
    }

    private function inventory(string $date): StaycationInventory
    {
        return StaycationInventory::where('staycation_id', $this->staycationId)
            ->whereDate('date', $date)
            ->firstOrFail();
    }
}
