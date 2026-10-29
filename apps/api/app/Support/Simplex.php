<?php

namespace App\Support;

/**
 * Two-phase simplex for small LPs: minimize c·x subject to linear constraints,
 * x >= 0. Uses Bland's rule to guarantee termination (no cycling). Intended for
 * least-cost feed formulation — a handful of variables and constraints.
 *
 * ponytail: dense tableau, Bland's rule. Fine for feed-size problems (tens of
 * vars/constraints); swap for a sparse/revised simplex only if that ceiling bites.
 */
class Simplex
{
    public const OPTIMAL = 'optimal';

    public const INFEASIBLE = 'infeasible';

    public const UNBOUNDED = 'unbounded';

    /**
     * @param  list<float>  $c  objective coefficients to minimize (length n)
     * @param  list<array{coeffs:list<float>,op:string,rhs:float}>  $constraints
     * @return array{status:string,x:list<float>,objective:float}
     */
    public static function minimize(array $c, array $constraints, float $eps = 1e-9): array
    {
        $n = count($c);
        $m = count($constraints);

        // Normalise constraints to have non-negative RHS.
        $rows = [];
        foreach ($constraints as $con) {
            $coeffs = $con['coeffs'];
            $op = $con['op'];
            $rhs = $con['rhs'];
            if ($rhs < 0) {
                $coeffs = array_map(fn ($v) => -$v, $coeffs);
                $rhs = -$rhs;
                $op = $op === '<=' ? '>=' : ($op === '>=' ? '<=' : '=');
            }
            $rows[] = ['coeffs' => $coeffs, 'op' => $op, 'rhs' => $rhs];
        }

        // Assign slack/surplus and artificial variable columns.
        $nSlack = 0;
        $nArtificial = 0;
        foreach ($rows as $row) {
            if ($row['op'] === '<=') {
                $nSlack++;
            } elseif ($row['op'] === '>=') {
                $nSlack++;
                $nArtificial++;
            } else {
                $nArtificial++;
            }
        }

        $total = $n + $nSlack + $nArtificial;
        $slackBase = $n;
        $artBase = $n + $nSlack;

        $tableau = [];      // m rows, each total+1 (last = rhs)
        $basis = [];        // basic variable index per row
        $artificialCols = [];
        $s = 0;
        $a = 0;

        foreach ($rows as $i => $row) {
            $t = array_fill(0, $total + 1, 0.0);
            for ($j = 0; $j < $n; $j++) {
                $t[$j] = (float) $row['coeffs'][$j];
            }
            $t[$total] = (float) $row['rhs'];

            if ($row['op'] === '<=') {
                $col = $slackBase + $s++;
                $t[$col] = 1.0;
                $basis[$i] = $col;
            } elseif ($row['op'] === '>=') {
                $t[$slackBase + $s++] = -1.0;       // surplus
                $col = $artBase + $a++;
                $t[$col] = 1.0;                       // artificial
                $basis[$i] = $col;
                $artificialCols[] = $col;
            } else {
                $col = $artBase + $a++;
                $t[$col] = 1.0;
                $basis[$i] = $col;
                $artificialCols[] = $col;
            }
            $tableau[] = $t;
        }

        // Phase 1: minimise sum of artificials (if any).
        if ($nArtificial > 0) {
            $phase1 = array_fill(0, $total, 0.0);
            foreach ($artificialCols as $col) {
                $phase1[$col] = 1.0;
            }

            self::optimize($tableau, $basis, $phase1, $total, $m, $eps);

            $objective = 0.0;
            foreach ($basis as $i => $col) {
                $objective += $phase1[$col] * $tableau[$i][$total];
            }
            if ($objective > 1e-7) {
                return ['status' => self::INFEASIBLE, 'x' => [], 'objective' => 0.0];
            }

            // Drop artificial columns before phase 2.
            foreach ($tableau as &$t) {
                array_splice($t, $artBase, $nArtificial);
            }
            unset($t);
            $total -= $nArtificial;
        }

        // Phase 2: minimise the real objective (pad with zeros for slack/surplus).
        $cost = array_pad(array_map(fn ($v) => (float) $v, $c), $total, 0.0);
        $status = self::optimize($tableau, $basis, $cost, $total, $m, $eps);

        if ($status === self::UNBOUNDED) {
            return ['status' => self::UNBOUNDED, 'x' => [], 'objective' => 0.0];
        }

        $x = array_fill(0, $n, 0.0);
        foreach ($basis as $i => $col) {
            if ($col < $n) {
                $x[$col] = $tableau[$i][$total];
            }
        }
        $objective = 0.0;
        for ($j = 0; $j < $n; $j++) {
            $objective += $c[$j] * $x[$j];
        }

        return ['status' => self::OPTIMAL, 'x' => array_map(fn ($v) => round($v, 6), $x), 'objective' => $objective];
    }

    /**
     * Pivot to optimality for the given cost vector. Returns OPTIMAL or UNBOUNDED.
     *
     * @param  array<int,array<int,float>>  $tableau
     * @param  array<int,int>  $basis
     * @param  array<int,float>  $cost
     */
    private static function optimize(array &$tableau, array &$basis, array $cost, int $total, int $m, float $eps): string
    {
        while (true) {
            // Reduced costs: c_j - c_B · column_j.
            $reduced = [];
            for ($j = 0; $j < $total; $j++) {
                $cb = 0.0;
                for ($i = 0; $i < $m; $i++) {
                    $cb += $cost[$basis[$i]] * $tableau[$i][$j];
                }
                $reduced[$j] = $cost[$j] - $cb;
            }

            // Bland's rule: first column with negative reduced cost.
            $enter = -1;
            for ($j = 0; $j < $total; $j++) {
                if ($reduced[$j] < -$eps) {
                    $enter = $j;
                    break;
                }
            }
            if ($enter === -1) {
                return self::OPTIMAL;
            }

            // Ratio test; Bland's rule breaks ties by smallest basic-variable index.
            $leave = -1;
            $bestRatio = INF;
            for ($i = 0; $i < $m; $i++) {
                $aij = $tableau[$i][$enter];
                if ($aij > $eps) {
                    $ratio = $tableau[$i][$total] / $aij;
                    if ($ratio < $bestRatio - $eps ||
                        (abs($ratio - $bestRatio) <= $eps && ($leave === -1 || $basis[$i] < $basis[$leave]))) {
                        $bestRatio = $ratio;
                        $leave = $i;
                    }
                }
            }
            if ($leave === -1) {
                return self::UNBOUNDED;
            }

            self::pivot($tableau, $leave, $enter, $total, $m);
            $basis[$leave] = $enter;
        }
    }

    /**
     * @param  array<int,array<int,float>>  $tableau
     */
    private static function pivot(array &$tableau, int $row, int $col, int $total, int $m): void
    {
        $pivotVal = $tableau[$row][$col];
        for ($j = 0; $j <= $total; $j++) {
            $tableau[$row][$j] /= $pivotVal;
        }
        for ($i = 0; $i < $m; $i++) {
            if ($i === $row) {
                continue;
            }
            $factor = $tableau[$i][$col];
            if ($factor == 0.0) {
                continue;
            }
            for ($j = 0; $j <= $total; $j++) {
                $tableau[$i][$j] -= $factor * $tableau[$row][$j];
            }
        }
    }
}
