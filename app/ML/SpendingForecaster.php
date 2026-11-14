<?php

declare(strict_types=1);

namespace App\ML;

/**
 * Prévisions financières prédictives (sans dépendance externe).
 *
 * - Projection de fin de mois (moyenne mobile des dépenses quotidiennes)
 * - Tendance linéaire (pente/intercept par régression des moindres carrés)
 * - Alerte de dépassement de budget.
 */
final class SpendingForecaster
{
    /**
     * Projette le total de fin de mois à partir des totaux quotidiens passés.
     *
     * @param list<float> $dailyTotals totaux de dépenses par jour (écoulés)
     */
    public function forecastEndOfMonth(array $dailyTotals, int $totalDays, int $elapsedDays): float
    {
        if ($dailyTotals === [] || $elapsedDays <= 0) {
            return 0.0;
        }

        $average = array_sum($dailyTotals) / count($dailyTotals);
        $spentSoFar = array_sum($dailyTotals);
        $remaining = max(0, $totalDays - $elapsedDays);

        return round($spentSoFar + ($average * $remaining), 2);
    }

    /**
     * Régression linéaire simple sur une série temporelle.
     *
     * @param list<float> $values
     * @return array{slope: float, intercept: float} tendance y = slope*x + intercept
     */
    public function linearTrend(array $values): array
    {
        $n = count($values);

        if ($n < 2) {
            return ['slope' => 0.0, 'intercept' => $values[0] ?? 0.0];
        }

        $sumX = 0.0;
        $sumY = 0.0;
        $sumXY = 0.0;
        $sumXX = 0.0;

        foreach ($values as $i => $y) {
            $x = $i + 1;
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumXX += $x * $x;
        }

        $denominator = ($n * $sumXX) - ($sumX * $sumX);

        if ($denominator === 0.0) {
            return ['slope' => 0.0, 'intercept' => $sumY / $n];
        }

        $slope = (($n * $sumXY) - ($sumX * $sumY)) / $denominator;
        $intercept = ($sumY - ($slope * $sumX)) / $n;

        return ['slope' => $slope, 'intercept' => $intercept];
    }

    /**
     * Détecte un dépassement de budget probable.
     *
     * @return array<string, mixed>|null alerte ou null si aucun risque
     */
    public function budgetAlert(float $projectedTotal, float $budget): ?array
    {
        if ($budget <= 0.0) {
            return null;
        }

        $ratio = $projectedTotal / $budget;

        if ($ratio >= 1.0) {
            return [
                'severity' => $ratio >= 1.1 ? 'critical' : 'warning',
                'projected' => $projectedTotal,
                'budget' => $budget,
                'overshoot_percent' => round(($ratio - 1) * 100, 1),
            ];
        }

        return null;
    }
}
