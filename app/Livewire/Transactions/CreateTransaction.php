<?php

declare(strict_types=1);

namespace App\Livewire\Transactions;

use App\Domain\Currency\BiMonetaryAllocation;
use App\Domain\Currency\BiMonetaryService;
use App\Domain\Currency\Money;
use Livewire\Component;

/**
 * Saisie d'une transaction (composant Livewire).
 *
 * Démontre le cas bimétaire critique RDC : l'utilisateur renseigne certains
 * champs en USD et d'autres en CDF ; le service unifie les montants dans la
 * devise de base du foyer pour les rapports globaux.
 *
 * La persistance (non illustrée ici) passe par un TransactionService qui :
 *  1. chiffre chaque montant (FieldEncryptor + DEK du foyer),
 *  2. écrit via SupabaseClient::asUser (RLS).
 */
final class CreateTransaction extends Component
{
    public string $title = '';
    public string $kind = 'expense';
    public string $scope = 'personal';
    public string $occurredAt = '';
    public string $note = '';

    // Champs bimétaires (RDC) : montant en USD et/ou en CDF.
    public string $usdAmount = '';
    public string $cdfAmount = '';
    public string $baseCurrency = 'USD';

    public ?string $unifiedTotal = null;

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'kind' => ['required', 'in:expense,income,transfer'],
            'scope' => ['required', 'in:personal,shared'],
            'occurredAt' => ['required', 'date'],
            'usdAmount' => ['nullable', 'regex:/^\d+(\.\d+)?$/'],
            'cdfAmount' => ['nullable', 'regex:/^\d+(\.\d+)?$/'],
        ];
    }

    public function save(BiMonetaryService $unifier): void
    {
        $this->validate();

        $legs = [];
        if ($this->usdAmount !== '' && $this->usdAmount !== '0') {
            $legs[] = new Money($this->usdAmount, 'USD');
        }
        if ($this->cdfAmount !== '' && $this->cdfAmount !== '0') {
            $legs[] = new Money($this->cdfAmount, 'CDF');
        }

        if ($legs === []) {
            $this->addError('usdAmount', __('transactions.amount'));

            return;
        }

        $allocation = new BiMonetaryAllocation($legs);

        // Unification multi-devises (taux dynamique ou configuré).
        $summary = $unifier->unify($allocation, $this->baseCurrency);

        $this->unifiedTotal = (string) $summary->unifiedTotal->rounded(2);

        // → Ici : chiffrement (FieldEncryptor) + persistance (Supabase RLS).
        session()->flash('status', __('transactions.save'));

        $this->reset(['title', 'usdAmount', 'cdfAmount', 'note', 'unifiedTotal']);
    }

    public function render()
    {
        return view('livewire.transactions.create-transaction');
    }
}
