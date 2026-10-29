<?php

namespace Tests\Unit;

use App\Support\Simplex;
use PHPUnit\Framework\TestCase;

class SimplexTest extends TestCase
{
    public function test_minimum_over_ge_constraint(): void
    {
        // min x+y s.t. x+y >= 1  ->  objective 1
        $r = Simplex::minimize([1, 1], [
            ['coeffs' => [1, 1], 'op' => '>=', 'rhs' => 1],
        ]);

        $this->assertSame(Simplex::OPTIMAL, $r['status']);
        $this->assertEqualsWithDelta(1.0, $r['objective'], 1e-6);
    }

    public function test_least_cost_feed_mix(): void
    {
        // cost [2,3]; protein [10,20]·x >= 15; mass x1+x2 = 1
        // hand solution: x=(0.5,0.5), cost 2.5
        $r = Simplex::minimize([2, 3], [
            ['coeffs' => [10, 20], 'op' => '>=', 'rhs' => 15],
            ['coeffs' => [1, 1], 'op' => '=', 'rhs' => 1],
        ]);

        $this->assertSame(Simplex::OPTIMAL, $r['status']);
        $this->assertEqualsWithDelta(2.5, $r['objective'], 1e-6);
        $this->assertEqualsWithDelta(0.5, $r['x'][0], 1e-6);
        $this->assertEqualsWithDelta(0.5, $r['x'][1], 1e-6);
    }

    public function test_infeasible_when_target_unreachable(): void
    {
        // mass = 1 but protein must be >= 25 while max ingredient is 20 -> infeasible
        $r = Simplex::minimize([2, 3], [
            ['coeffs' => [1, 1], 'op' => '=', 'rhs' => 1],
            ['coeffs' => [10, 20], 'op' => '>=', 'rhs' => 25],
        ]);

        $this->assertSame(Simplex::INFEASIBLE, $r['status']);
    }

    public function test_upper_bound_constraint(): void
    {
        // min -x s.t. x <= 4  ->  x=4, objective -4
        $r = Simplex::minimize([-1], [
            ['coeffs' => [1], 'op' => '<=', 'rhs' => 4],
        ]);

        $this->assertSame(Simplex::OPTIMAL, $r['status']);
        $this->assertEqualsWithDelta(-4.0, $r['objective'], 1e-6);
    }
}
