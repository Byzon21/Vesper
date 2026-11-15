<?php

declare(strict_types=1);

namespace App\Livewire\Transactions;

use Livewire\Component;

/**
 * Saisie d'une transaction (composant Livewire).
 *
 * La devise est choisie parmi les devises préférées de l'utilisateur (définies
 * à l'onboarding), la devise principale étant sélectionnée par défaut — fini le
 * franc congolais imposé à tous.
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
    public string $amount = '';
    public string $currency = 'USD';

    /** @var array<int, string> */
    public array $availableCurrencies = [];

    /** @var array<string, string> */
    public const CURRENCY_LABELS = [
        'USD' => 'USD — Dollar américain',
        'EUR' => 'EUR — Euro',
        'GBP' => 'GBP — Livre sterling',
        'CAD' => 'CAD — Dollar canadien',
        'CHF' => 'CHF — Franc suisse',
        'CDF' => 'CDF — Franc congolais',
        'XOF' => 'XOF — Franc CFA',
        'XAF' => 'XAF — Franc CFA',
        'NGN' => 'NGN — Naira',
        'ZAR' => 'ZAR — Rand',
        'GHS' => 'GHS — Cedi',
        'KES' => 'KES — Shilling kényan',
        'MAD' => 'MAD — Dirham',
        'DZD' => 'DZD — Dinar algérien',
        'TND' => 'TND — Dinar tunisien',
        'JPY' => 'JPY — Yen',
        'CNY' => 'CNY — Yuan',
        'INR' => 'INR — Roupie',
    ];

    public function mount(): void
    {
        $this->availableCurrencies = array_values((array) session('currencies', ['USD']));
        $this->currency = (string) session('base_currency', $this->availableCurrencies[0] ?? 'USD');
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'kind' => ['required', 'in:expense,income,transfer'],
            'scope' => ['required', 'in:personal,shared'],
            'occurredAt' => ['required', 'date'],
            'amount' => ['required', 'regex:/^\d+(\.\d+)?$/'],
            'currency' => ['required', 'string', 'size:3'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        // → Ici : chiffrement (FieldEncryptor) + persistance (Supabase RLS).
        session()->flash('status', __('transactions.save'));

        $this->reset(['title', 'amount', 'note']);
    }

    public function render()
    {
        return view('livewire.transactions.create-transaction', [
            'currencyLabels' => self::CURRENCY_LABELS,
        ]);
    }
}

