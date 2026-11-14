<?php

declare(strict_types=1);

namespace App\Livewire\Transactions;

use Livewire\Component;

/**
 * Liste / gestion des transactions.
 */
final class TransactionList extends Component
{
    public function render()
    {
        return view('livewire.transactions.transaction-list')->layout('layouts.app');
    }
}
