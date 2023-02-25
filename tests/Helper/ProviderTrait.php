<?php

namespace Tests\Helper;

use App\Models\Provider;

trait ProviderTrait
{
    private Provider $googleProvider;

    private function mockGoogleProvider(): void
    {
        $this->googleProvider = Provider::factory()->create(['name' => 'google']);
    }
}
