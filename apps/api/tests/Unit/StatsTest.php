<?php

namespace Tests\Unit;

use App\Support\Stats;
use PHPUnit\Framework\TestCase;

class StatsTest extends TestCase
{
    public function test_empty_sample(): void
    {
        $this->assertSame(
            ['count' => 0, 'mean' => null, 'std' => null, 'cv_pct' => null, 'min' => null, 'max' => null],
            Stats::summary([])
        );
    }

    public function test_uniform_sample_has_zero_cv(): void
    {
        $s = Stats::summary([10.0, 10.0, 10.0]);

        $this->assertSame(10.0, $s['mean']);
        $this->assertSame(0.0, $s['std']);
        $this->assertSame(0.0, $s['cv_pct']);
    }

    public function test_known_spread(): void
    {
        // [8,12]: mean 10, population std 2, cv 20%
        $s = Stats::summary([8.0, 12.0]);

        $this->assertSame(10.0, $s['mean']);
        $this->assertSame(2.0, $s['std']);
        $this->assertSame(20.0, $s['cv_pct']);
        $this->assertSame(8.0, $s['min']);
        $this->assertSame(12.0, $s['max']);
    }
}
