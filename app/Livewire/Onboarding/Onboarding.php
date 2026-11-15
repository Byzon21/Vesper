<?php

declare(strict_types=1);

namespace App\Livewire\Onboarding;

use App\Services\Supabase\SupabaseClient;
use Livewire\Component;

/**
 * Assistant d'onboarding post-inscription.
 *
 * Recueille les informations nécessaires pour personnaliser le tableau de bord :
 * identité, pays, type de compte (particulier / couple) et devises préférées.
 * Met à jour le profil Supabase (RLS : l'utilisateur est propriétaire de sa ligne).
 */
final class Onboarding extends Component
{
    public string $firstName = '';
    public string $lastName = '';
    public string $country = '';
    public string $accountType = 'personal';
    public string $partnerName = '';
    public array $currencies = [];

    /** @var array<string, string> */
    public const CURRENCIES = [
        'USD' => 'Dollar américain ($)',
        'EUR' => 'Euro (€)',
        'GBP' => 'Livre sterling (£)',
        'CAD' => 'Dollar canadien (C$)',
        'CHF' => 'Franc suisse (CHF)',
        'CDF' => 'Franc congolais (FC)',
        'XOF' => 'Franc CFA - UEMOA (CFA)',
        'XAF' => 'Franc CFA - CEMAC (FCFA)',
        'NGN' => 'Naira nigérian (₦)',
        'ZAR' => 'Rand sud-africain (R)',
        'GHS' => 'Cedi ghanéen (GH₵)',
        'KES' => 'Shilling kényan (KSh)',
        'MAD' => 'Dirham marocain (DH)',
        'DZD' => 'Dinar algérien (DA)',
        'TND' => 'Dinar tunisien (DT)',
        'JPY' => 'Yen japonais (¥)',
        'CNY' => 'Yuan chinois (¥)',
        'INR' => 'Roupie indienne (₹)',
    ];

    /** @var array<string, string> */
    public const COUNTRIES = [
        'FR' => 'France',
        'BE' => 'Belgique',
        'CH' => 'Suisse',
        'CA' => 'Canada',
        'US' => 'États-Unis',
        'GB' => 'Royaume-Uni',
        'DE' => 'Allemagne',
        'ES' => 'Espagne',
        'IT' => 'Italie',
        'PT' => 'Portugal',
        'NL' => 'Pays-Bas',
        'CD' => 'RD Congo',
        'CG' => 'Congo-Brazzaville',
        'CM' => 'Cameroun',
        'CI' => "Côte d'Ivoire",
        'SN' => 'Sénégal',
        'ML' => 'Mali',
        'BF' => 'Burkina Faso',
        'TG' => 'Togo',
        'BJ' => 'Bénin',
        'NE' => 'Niger',
        'GA' => 'Gabon',
        'MA' => 'Maroc',
        'DZ' => 'Algérie',
        'TN' => 'Tunisie',
        'NG' => 'Nigéria',
        'GH' => 'Ghana',
        'KE' => 'Kenya',
        'ZA' => 'Afrique du Sud',
        'BR' => 'Brésil',
        'IN' => 'Inde',
        'JP' => 'Japon',
        'CN' => 'Chine',
        'AE' => 'Émirats arabes unis',
    ];

    public function rules(): array
    {
        return [
            'firstName' => ['required', 'string', 'max:60'],
            'lastName' => ['required', 'string', 'max:60'],
            'country' => ['required', 'string', 'size:2'],
            'accountType' => ['required', 'in:personal,couple'],
            'partnerName' => ['nullable', 'required_if:accountType,couple', 'string', 'max:60'],
            'currencies' => ['required', 'array', 'min:1'],
        ];
    }

    public function save(SupabaseClient $supabase): void
    {
        $this->validate();

        $userId = (string) session('supabase_user_id');
        $token = (string) session('supabase_access_token');
        $codes = array_values(array_filter($this->currencies));

        $supabase->asUser($token);
        $supabase->update('profiles', [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'display_name' => trim($this->firstName . ' ' . $this->lastName),
            'country' => $this->country,
            'account_type' => $this->accountType,
            'partner_name' => $this->accountType === 'couple' ? $this->partnerName : null,
            'currencies' => $codes,
            'base_currency' => $codes[0] ?? 'USD',
            'is_onboarded' => true,
        ], ['id' => 'eq.' . $userId]);

        session()->put('is_onboarded', true);
        session()->put('base_currency', $codes[0] ?? 'USD');
        session()->put('currencies', $codes);
        session()->put('country', $this->country);

        $this->redirect(route('dashboard'));
    }

    public function render()
    {
        return view('livewire.onboarding.onboarding', [
            'currencyOptions' => self::CURRENCIES,
            'countryOptions' => self::COUNTRIES,
        ])->layout('layouts.app');
    }
}
