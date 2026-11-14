<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use Livewire\Component;

/**
 * Rapports & analyses — graphiques, insights ML, génération PDF.
 */
final class Reports extends Component
{
    public function render()
    {
        return view('livewire.reports.reports')->layout('layouts.app');
    }
}
