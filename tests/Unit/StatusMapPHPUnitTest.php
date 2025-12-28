<?php

namespace Tests\Unit;

use Tests\TestCase;

class StatusMapPHPUnitTest extends TestCase
{
    public function test_status_map_has_expected_structure()
    {
        $map = config('water_quality.status_map');

        $this->assertIsArray($map);

        foreach (['GOOD', 'BAD', 'HARMFUL'] as $status) {
            $this->assertArrayHasKey($status, $map);
            $this->assertArrayHasKey('badge', $map[$status]);
            $this->assertArrayHasKey('value', $map[$status]);
            $this->assertArrayHasKey('border', $map[$status]);

            $this->assertIsString($map[$status]['badge']);
            $this->assertIsString($map[$status]['value']);
            $this->assertIsString($map[$status]['border']);
        }
    }
}
