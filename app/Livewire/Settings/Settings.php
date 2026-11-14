<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use Livewire\Component;

/**
 * Paramètres — langue, thème, devise, profils.
 */
final class Settings extends Component
{
    public function render()
    {
        return view('livewire.settings.settings')->layout('layouts.app');
    }
}
