<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use Livewire\Component;

/**
 * Tableau de bord — vue d'ensemble (solde, revenus, dépenses, alertes).
 */
final class Dashboard extends Component
{
    public bool $loaded = false;

    public function load(): void
    {
        // Simulation d'un chargement (à remplacer par la lecture Supabase des
        // transactions et agrégats). Le skeleton reste visible pendant ce temps.
        usleep(600000);

        $this->loaded = true;
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard')->layout('layouts.app');
    }
}
