<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use Livewire\Component;

/**
 * Tableau de bord — vue d'ensemble (solde, revenus, dépenses, alertes).
 */
final class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.dashboard.dashboard')->layout('layouts.app');
    }
}
