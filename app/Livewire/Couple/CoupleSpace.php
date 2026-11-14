<?php

declare(strict_types=1);

namespace App\Livewire\Couple;

use Livewire\Component;

/**
 * Espace couple — partage du foyer, 2 photos de profil, transactions partagées.
 */
final class CoupleSpace extends Component
{
    public function render()
    {
        return view('livewire.couple.couple-space')->layout('layouts.app');
    }
}
