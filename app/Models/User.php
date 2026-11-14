<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Modèle d'utilisateur minimal.
 *
 * ⚠️  Vesper n'utilise PAS de table `users` locale : Supabase est la source de
 *     vérité (session `supabase_user_id`). Ce modèle n'existe que pour
 *     satisfaire la configuration `auth` par défaut de Laravel ; il n'est pas
 *     utilisé par le flux d'authentification réel.
 */
class User extends Authenticatable
{
    protected $fillable = ['name', 'email'];

    protected $hidden = ['password', 'remember_token'];
}
