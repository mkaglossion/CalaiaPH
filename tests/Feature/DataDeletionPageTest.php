<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataDeletionPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_deletion_instructions_page_is_publicly_accessible(): void
    {
        $response = $this->get(route('data-deletion'));

        $response
            ->assertOk()
            ->assertSee('Facebook Data Deletion Instructions')
            ->assertSee('Facebook Data Deletion Request')
            ->assertSee('calaia.ph@gmail.com');
    }
}
