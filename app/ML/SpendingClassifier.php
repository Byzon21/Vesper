<?php

declare(strict_types=1);

namespace App\ML;

use Rubix\ML\Classifiers\KNearestNeighbors;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;

/**
 * Classification automatique des dépenses (Rubix ML — package natif PHP).
 *
 * Entraîne un classifieur k-NN sur l'historique catégorisé pour suggérer la
 * catégorie d'une nouvelle transaction. Le modèle est persisté sur disque
 * (chemin hors racine web) et peut être rechargé sans ré-entraînement.
 */
final class SpendingClassifier
{
    public function __construct(
        private readonly string $modelPath,
        private readonly int $k = 5,
    ) {
    }

    /**
     * @param list<array<int|float>> $samples caractéristiques (montant normalisé, jour du mois, jour de semaine)
     * @param list<string>           $labels  catégories correspondantes
     */
    public function train(array $samples, array $labels): void
    {
        if (count($samples) !== count($labels) || $samples === []) {
            throw new \InvalidArgumentException('Échantillons et libellés incohérents.');
        }

        $dataset = new Labeled($samples, $labels);

        $estimator = new PersistentModel(
            new KNearestNeighbors($this->k),
            new Filesystem($this->modelPath),
        );

        $estimator->train($dataset);
        $estimator->save();
    }

    /**
     * Prédit la catégorie la plus probable pour de nouvelles caractéristiques.
     *
     * @param array<int|float> $features
     */
    public function predictCategory(array $features): string
    {
        if (! is_file($this->modelPath)) {
            throw new \RuntimeException('Modèle de classification non entraîné.');
        }

        $estimator = PersistentModel::load(new Filesystem($this->modelPath));

        return (string) $estimator->predict($features);
    }
}
