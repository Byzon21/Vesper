<?php

declare(strict_types=1);

namespace App\ML;

/**
 * Générateur d'insights financiers (orchestration ML).
 *
 * À partir de l'historique déchiffré des transactions, produit :
 *  1. suggestions de catégorisation automatique,
 *  2. prévisions de fin de mois + alertes de budget,
 *  3. optimisations d'épargne simples.
 *
 * Les résultats sont ensuite chiffrés (FieldEncryptor) avant stockage dans
 * la table `ml_insights` (payload_ciphertext).
 */
final class InsightGenerator
{
    public function __construct(
        private readonly SpendingClassifier $classifier,
        private readonly SpendingForecaster $forecaster,
    ) {
    }

    /**
     * @param array<int, array<string, mixed>> $transactions
     * @param array<int, array<string, mixed>> $budgets
     * @return list<array{type: string, payload: array<string, mixed>}>
     */
    public function generate(array $transactions, array $budgets): array
    {
        $insights = [];

        // 1. Suggestion de catégorie pour les transactions non catégorisées.
        foreach ($transactions as $tx) {
            if (empty($tx['category_id']) && isset($tx['features'])) {
                $category = $this->classifier->predictCategory($tx['features']);
                $insights[] = [
                    'type' => 'category_suggestion',
                    'payload' => ['transaction_id' => $tx['id'], 'suggested' => $category],
                ];
            }
        }

        // 2. Prévision de fin de mois + alerte budget.
        $dailyTotals = $this->dailyTotals($transactions);
        $projected = $this->forecaster->forecastEndOfMonth(
            $dailyTotals,
            (int) date('t'),
            (int) date('j'),
        );

        foreach ($budgets as $budget) {
            $alert = $this->forecaster->budgetAlert($projected, (float) $budget['amount']);
            if ($alert !== null) {
                $insights[] = [
                    'type' => 'forecast',
                    'payload' => $alert + ['budget_id' => $budget['id']],
                ];
            }
        }

        // 3. Optimisation d'épargne : 20% du revenu mensuel en objectif.
        $income = $this->sumByKind($transactions, 'income');
        if ($income > 0) {
            $insights[] = [
                'type' => 'saving_tip',
                'payload' => ['suggested_saving' => round($income * 0.2, 2)],
            ];
        }

        return $insights;
    }

    /**
     * @param array<int, array<string, mixed>> $transactions
     * @return list<float>
     */
    private function dailyTotals(array $transactions): array
    {
        $totals = [];
        foreach ($transactions as $tx) {
            if (($tx['kind'] ?? '') === 'expense') {
                $day = substr((string) ($tx['occurred_at'] ?? ''), 8, 2);
                $totals[(int) $day] = ((float) ($totals[(int) $day] ?? 0)) + (float) ($tx['amount'] ?? 0);
            }
        }
        ksort($totals);

        return array_values($totals);
    }

    /**
     * @param array<int, array<string, mixed>> $transactions
     */
    private function sumByKind(array $transactions, string $kind): float
    {
        return array_sum(array_map(
            static fn (array $tx): float => ($tx['kind'] ?? '') === $kind ? (float) ($tx['amount'] ?? 0) : 0.0,
            $transactions,
        ));
    }
}
