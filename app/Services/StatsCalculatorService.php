<?php

namespace App\Services;

class StatsCalculatorService
{
    /**
     * Compute statistical metrics for an array of numeric values.
     * Uses ISO 13528 Algorithm A for Robust Mean (x*) and Robust SD (s*).
     */
    public function calculate(array $values): array
    {
        $numericValues = array_filter(array_map('floatval', $values), function ($val) {
            return is_numeric($val);
        });

        $n = count($numericValues);

        if ($n === 0) {
            return [
                'count' => 0,
                'mean' => 0,
                'median' => 0,
                'std_dev' => 0,
                'robust_mean' => 0,
                'robust_sd' => 0,
                'min' => 0,
                'max' => 0,
            ];
        }

        sort($numericValues);

        $min = min($numericValues);
        $max = max($numericValues);
        $mean = array_sum($numericValues) / $n;
        $median = $this->calculateMedian($numericValues);
        $stdDev = $this->calculateStdDev($numericValues, $mean);

        // ISO 13528 Algorithm A for Robust Mean (x*) & Robust SD (s*)
        list($robustMean, $robustSd) = $this->algorithmA($numericValues, $median, $stdDev);

        return [
            'count' => $n,
            'mean' => round($mean, 4),
            'median' => round($median, 4),
            'std_dev' => round($stdDev, 4),
            'robust_mean' => round($robustMean, 4),
            'robust_sd' => round($robustSd, 4),
            'min' => round($min, 4),
            'max' => round($max, 4),
        ];
    }

    /**
     * Calculate Z-Score for a result value.
     */
    public function calculateZScore(float $resultValue, float $assignedValue, float $targetSd): array
    {
        if ($targetSd == 0) {
            $zScore = 0;
        } else {
            $zScore = ($resultValue - $assignedValue) / $targetSd;
        }

        $zScoreRound = round($zScore, 2);
        $absZ = abs($zScoreRound);

        if ($absZ <= 2.0) {
            $status = 'satisfactory';
            $badgeClass = 'bg-success';
            $label = 'Satisfactory';
        } elseif ($absZ < 3.0) {
            $status = 'warning';
            $badgeClass = 'bg-warning text-dark';
            $label = 'Questionable (Warning)';
        } else {
            $status = 'unsatisfactory';
            $badgeClass = 'bg-danger';
            $label = 'Unsatisfactory (Action Signal)';
        }

        return [
            'z_score' => $zScoreRound,
            'status' => $status,
            'badge_class' => $badgeClass,
            'label' => $label,
        ];
    }

    private function calculateMedian(array $sortedValues): float
    {
        $count = count($sortedValues);
        $middle = floor(($count - 1) / 2);

        if ($count % 2) {
            return $sortedValues[$middle];
        }

        return ($sortedValues[$middle] + $sortedValues[$middle + 1]) / 2.0;
    }

    private function calculateStdDev(array $values, float $mean): float
    {
        $count = count($values);
        if ($count <= 1) return 0.0;

        $variance = array_reduce($values, function ($carry, $item) use ($mean) {
            return $carry + pow($item - $mean, 2);
        }, 0.0) / ($count - 1);

        return sqrt($variance);
    }

    /**
     * ISO 13528 Algorithm A Implementation.
     */
    private function algorithmA(array $values, float $initialMedian, float $initialSd): array
    {
        $n = count($values);
        if ($n <= 2) {
            return [$initialMedian, $initialSd];
        }

        // Initial estimates
        $xStar = $initialMedian;

        // MAD (Median Absolute Deviation) calculation
        $absDiffs = array_map(function ($val) use ($xStar) {
            return abs($val - $xStar);
        }, $values);
        sort($absDiffs);
        $mad = $this->calculateMedian($absDiffs);
        $sStar = 1.4826 * $mad;

        if ($sStar == 0) {
            $sStar = $initialSd > 0 ? $initialSd : 0.001;
        }

        // Iterative refinement (max 50 iterations or until convergence)
        for ($iter = 0; $iter < 50; $iter++) {
            $prevXStar = $xStar;
            $prevSStar = $sStar;

            $delta = 1.5 * $sStar;
            $modifiedValues = [];

            foreach ($values as $x) {
                if ($x < $xStar - $delta) {
                    $modifiedValues[] = $xStar - $delta;
                } elseif ($x > $xStar + $delta) {
                    $modifiedValues[] = $xStar + $delta;
                } else {
                    $modifiedValues[] = $x;
                }
            }

            $xStar = array_sum($modifiedValues) / $n;
            $sStar = 1.134 * $this->calculateStdDev($modifiedValues, $xStar);

            if (abs($xStar - $prevXStar) < 0.0001 && abs($sStar - $prevSStar) < 0.0001) {
                break;
            }
        }

        return [$xStar, $sStar];
    }
}
