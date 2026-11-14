<?php

declare(strict_types=1);

namespace App\Services\Reporting;

use Barryvdh\DomPDF\Facade\Pdf;

/**
 * Génération de rapports financiers PDF (DOMPDF).
 *
 * Produit à la volée un rapport personnalisé (global, par catégorie, par
 * période, couple vs personnel). Le PDF est ensuite :
 *  1. stocké dans Supabase Storage (chemin sécurisé, soumis à RLS),
 *  2. téléchargeable directement sur l'appareil mobile.
 *
 * Les montants injectés dans la vue sont DÉCHIFFRÉS juste avant le rendu et
 * ne transitent jamais par un cache partagé.
 */
final class PdfReportGenerator
{
    public function __construct(
        private readonly string $view = 'reports.pdf.financial',
    ) {
    }

    /**
     * @param array<string, mixed> $data données financières déchiffrées
     */
    public function generate(array $data): string
    {
        $pdf = Pdf::loadView($this->view, $data)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', false)   // anti-SSRF : pas de ressources distantes
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        return (string) $pdf->output();
    }

    /**
     * Nom de fichier sécurisé (pas de traversée de chemin).
     */
    public function filename(string $kind, string $period = ''): string
    {
        $slug = preg_replace('/[^a-z0-9_-]+/i', '-', $kind . '-' . $period);
        $slug = trim((string) $slug, '-') ?: 'rapport';

        return sprintf('vesper-%s-%s.pdf', $slug, date('Ymd-His'));
    }
}
