<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="Vesper — Suivez vos dépenses personnelles et de couple, gérez 110+ devises, avec une sécurité de niveau bancaire.">
    <meta name="theme-color" content="#022c22" id="theme-color-meta">
    <title>Vesper — Vos finances personnelles et de couple, en toute sérénité</title>

    {{-- Thème + classe .js posés tôt (anti-flash) --}}
    <script nonce="{{ $cspNonce ?? '' }}">
        (function () {
            var r = document.documentElement;
            r.classList.add('js');
            try {
                var t = localStorage.getItem('vesper.theme') || 'system';
                var dark = t === 'dark' || (t === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
                r.classList.toggle('dark', dark);
            } catch (e) {}
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">

    {{-- ===== Navigation ===== --}}
    <header class="sticky top-0 z-50 border-b border-white/10 bg-emerald-950/80 text-white shadow-lg shadow-emerald-950/20 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
            <a href="{{ route('landing') }}" class="group flex shrink-0 items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 text-white shadow-lg shadow-emerald-500/40 transition-transform duration-300 group-hover:rotate-6">
                    <x-icon name="wallet" class="h-5 w-5" />
                </span>
                <span class="text-xl font-extrabold tracking-tight">Vesper</span>
            </a>

            <nav class="hidden items-center gap-1 lg:flex">
                <a href="#fonctionnalites" class="rounded-lg px-3 py-2 text-sm font-medium text-emerald-50/85 transition hover:bg-white/10 hover:text-white">Fonctionnalités</a>
                <a href="#confidentialite" class="rounded-lg px-3 py-2 text-sm font-medium text-emerald-50/85 transition hover:bg-white/10 hover:text-white">Confidentialité</a>
                <a href="#faq" class="rounded-lg px-3 py-2 text-sm font-medium text-emerald-50/85 transition hover:bg-white/10 hover:text-white">FAQ</a>
                <a href="#tarifs" class="rounded-lg px-3 py-2 text-sm font-medium text-emerald-50/85 transition hover:bg-white/10 hover:text-white">Tarifs</a>
            </nav>

            <div class="flex items-center gap-2">
                <button type="button" data-action="toggleTheme" aria-label="Changer de thème" class="flex h-9 w-9 items-center justify-center rounded-lg text-emerald-50/90 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A8.5 8.5 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8Z"/></svg>
                    <svg class="hidden h-5 w-5 dark:inline-block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                </button>
                <a href="{{ route('login') }}" class="hidden rounded-lg px-3 py-2 text-sm font-semibold text-emerald-50/90 transition hover:bg-white/10 hover:text-white sm:block">Se connecter</a>
                <a href="{{ route('register') }}" class="btn-primary !px-4 !py-2 text-sm sm:!px-5">Essai gratuit</a>
            </div>
        </div>
    </header>

    {{-- ===== Héros ===== --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=1920&auto=format&fit=crop')"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-emerald-950/90 via-emerald-900/85 to-slate-950/95"></div>
        <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full bg-emerald-500/30 blur-3xl"></div>
        <div class="absolute -right-24 top-1/3 h-96 w-96 rounded-full bg-amber-500/20 blur-3xl"></div>

        <div class="relative mx-auto max-w-6xl px-4 py-24 text-center text-white sm:px-6 sm:py-32 lg:py-40">
            <div class="animate-fade-in inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-semibold tracking-wide backdrop-blur">
                <x-icon name="sparkles" class="h-4 w-4 text-amber-300" />
                La gestion financière nouvelle génération
            </div>

            <h1 class="animate-fade-in-up mx-auto mt-6 max-w-4xl text-4xl font-extrabold leading-[1.1] tracking-tight sm:text-6xl">
                Vos finances personnelles et de couple,
                <span class="bg-gradient-to-r from-amber-300 via-amber-400 to-emerald-300 bg-clip-text text-transparent">en toute sérénité</span>
            </h1>

            <p class="animate-fade-in-up delay-100 mx-auto mt-6 max-w-2xl text-lg text-emerald-50/90 sm:text-xl">
                Suivez vos dépenses, gérez 110+ devises (USD, CDF, CFA…) et reprenez le contrôle de votre budget —
                sans jamais exposer vos informations à qui que ce soit.
            </p>

            <div class="animate-fade-in-up delay-200 mt-10 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('register') }}" class="btn-primary text-base">
                    Commencer l'essai gratuit
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
                <a href="{{ route('login') }}" class="btn-ghost !border-white/20 !bg-white/10 !text-white hover:!border-white/40 hover:!text-white">
                    J'ai déjà un compte
                </a>
            </div>

            <div class="animate-fade-in-up delay-300 mt-10 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-emerald-100/80">
                <span class="inline-flex items-center gap-1.5"><x-icon name="check" class="h-4 w-4 text-emerald-300" /> 7 jours offerts</span>
                <span class="inline-flex items-center gap-1.5"><x-icon name="check" class="h-4 w-4 text-emerald-300" /> Sans carte bancaire</span>
                <span class="inline-flex items-center gap-1.5"><x-icon name="check" class="h-4 w-4 text-emerald-300" /> Annulable à tout moment</span>
            </div>
        </div>
    </section>

    {{-- ===== Bandeau chiffres clés ===== --}}
    <section class="relative z-10 -mt-10 px-4 sm:px-6">
        <div class="mx-auto grid max-w-5xl grid-cols-2 gap-4 rounded-2xl border border-slate-200 bg-white/90 p-6 shadow-xl shadow-slate-900/5 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90 md:grid-cols-4">
            @foreach ([
                ['110+', 'Devises gérées'],
                ['7 jours', 'D\'essai gratuit'],
                ['0', 'Carte bancaire requise'],
                ['100%', 'Données privées'],
            ] as $stat)
                <div class="text-center">
                    <p class="text-3xl font-extrabold tracking-tight text-emerald-700 dark:text-emerald-400">{{ $stat[0] }}</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $stat[1] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== Storytelling : problème → solution ===== --}}
    <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-28">
        <div data-reveal class="mx-auto max-w-3xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-emerald-600">Pourquoi Vesper ?</p>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">En avez-vous assez de vous demander où part votre argent ?</h2>
            <p class="mt-5 text-lg text-slate-500 dark:text-slate-400">
                Chaque mois, c'est la même histoire : vous travaillez dur, l'argent rentre… puis disparaît.
                Entre les dépenses du quotidien, celles du couple et les conversions de devises, garder le fil devient un vrai casse-tête.
            </p>
        </div>

        <div class="mt-14 grid grid-cols-1 gap-6 md:grid-cols-3">
            <div data-reveal class="card">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-300">
                    <x-icon name="wallet" class="h-6 w-6" />
                </div>
                <h3 class="mt-5 text-lg font-bold">L'argent s'évapore</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">À la fin du mois, impossible de dire où sont passés vos revenus. Sans suivi, chaque petite dépense finit par peser lourd.</p>
            </div>
            <div data-reveal class="card">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300">
                    <x-icon name="users" class="h-6 w-6" />
                </div>
                <h3 class="mt-5 text-lg font-bold">À deux, c'est flou</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">Qui a payé quoi ? Combien reste-t-il pour le loyer ? Les dépenses communes et personnelles se mélangent.</p>
            </div>
            <div data-reveal class="card">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-500/20 dark:text-sky-300">
                    <x-icon name="globe" class="h-6 w-6" />
                </div>
                <h3 class="mt-5 text-lg font-bold">Plusieurs devises, un casse-tête</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">USD, CDF, CFA… Convertir à la main, c'est long, source d'erreurs, et ça brouille la vision de vos finances.</p>
            </div>
        </div>

        <div data-reveal class="mx-auto mt-12 max-w-2xl text-center">
            <p class="text-lg text-slate-600 dark:text-slate-300">
                <strong class="text-emerald-700 dark:text-emerald-400">Vesper transforme ce chaos en clarté.</strong>
                En quelques secondes par jour, vous savez exactement où va chaque franc, dollar ou euro — seul ou à deux.
            </p>
        </div>
    </section>

    {{-- ===== Globe : multi-devises ===== --}}
    <section class="relative overflow-hidden bg-emerald-950 py-20 text-white sm:py-28">
        <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-emerald-500/20 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-amber-500/10 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2">
            <div data-reveal>
                <p class="text-sm font-bold uppercase tracking-widest text-amber-300">Multi-devises</p>
                <h2 class="mt-3 text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">Votre argent voyage, suivez-le partout</h2>
                <p class="mt-4 text-lg text-emerald-100/85">
                    Que vous soyez en RDC, en Europe ou ailleurs, Vesper gère plus de 110 devises et les convertit
                    automatiquement. Fini les calculs à la main : votre budget reste clair, où que vous soyez.
                </p>
                <ul class="mt-8 space-y-4">
                    @foreach (['Conversion automatique et taux à jour', 'Suivi bimétaire USD / CDF pensé pour la RDC', 'Revenus dans une devise, dépenses dans une autre'] as $item)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-300">
                                <x-icon name="check" class="h-4 w-4" />
                            </span>
                            <span class="text-emerald-50/90">{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="btn-primary mt-9">
                    Essayer gratuitement
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            <div data-reveal class="relative mx-auto w-full max-w-md">
                <div class="relative aspect-square">
                    <svg viewBox="0 0 400 400" class="h-full w-full drop-shadow-2xl" aria-hidden="true">
                        <defs>
                            <radialGradient id="ocean" cx="35%" cy="30%" r="75%">
                                <stop offset="0%" stop-color="#38bdf8"/>
                                <stop offset="55%" stop-color="#0284c7"/>
                                <stop offset="100%" stop-color="#0c4a6e"/>
                            </radialGradient>
                            <radialGradient id="shade" cx="35%" cy="30%" r="85%">
                                <stop offset="0%" stop-color="#ffffff" stop-opacity="0.28"/>
                                <stop offset="45%" stop-color="#ffffff" stop-opacity="0"/>
                                <stop offset="100%" stop-color="#000000" stop-opacity="0.4"/>
                            </radialGradient>
                            <clipPath id="globe-clip"><circle cx="200" cy="200" r="172"/></clipPath>
                        </defs>

                        <g clip-path="url(#globe-clip)">
                            <g>
                                <animateTransform attributeName="transform" type="translate" from="0 0" to="-400 0" dur="32s" repeatCount="indefinite"/>
                                <g fill="#34d399" fill-opacity="0.9">
                                    <path d="M70 120 Q90 70 140 85 Q165 95 150 140 Q140 175 105 170 Q75 165 70 120Z"/>
                                    <path d="M125 235 Q140 210 165 220 Q180 260 165 320 Q150 350 130 330 Q112 300 125 235Z"/>
                                    <path d="M232 95 Q240 75 265 82 Q278 95 268 112 Q252 125 238 112 Q230 105 232 95Z"/>
                                    <path d="M238 185 Q238 150 270 152 Q295 155 292 205 Q290 265 262 278 Q240 285 238 250 Q236 215 238 185Z"/>
                                    <path d="M292 78 Q310 45 355 55 Q378 62 372 95 Q368 130 340 150 Q305 165 295 130 Q288 105 292 78Z"/>
                                    <path d="M332 288 Q340 272 358 278 Q368 290 362 305 Q352 316 338 310 Q328 300 332 288Z"/>
                                </g>
                                <g fill="#34d399" fill-opacity="0.9" transform="translate(400 0)">
                                    <path d="M70 120 Q90 70 140 85 Q165 95 150 140 Q140 175 105 170 Q75 165 70 120Z"/>
                                    <path d="M125 235 Q140 210 165 220 Q180 260 165 320 Q150 350 130 330 Q112 300 125 235Z"/>
                                    <path d="M232 95 Q240 75 265 82 Q278 95 268 112 Q252 125 238 112 Q230 105 232 95Z"/>
                                    <path d="M238 185 Q238 150 270 152 Q295 155 292 205 Q290 265 262 278 Q240 285 238 250 Q236 215 238 185Z"/>
                                    <path d="M292 78 Q310 45 355 55 Q378 62 372 95 Q368 130 340 150 Q305 165 295 130 Q288 105 292 78Z"/>
                                    <path d="M332 288 Q340 272 358 278 Q368 290 362 305 Q352 316 338 310 Q328 300 332 288Z"/>
                                </g>
                            </g>
                            <g stroke="#ffffff" stroke-opacity="0.14" fill="none">
                                <ellipse cx="200" cy="200" rx="172" ry="38"/>
                                <ellipse cx="200" cy="200" rx="172" ry="86"/>
                                <ellipse cx="200" cy="200" rx="86" ry="172"/>
                                <ellipse cx="200" cy="200" rx="38" ry="172"/>
                            </g>
                        </g>

                        <circle cx="200" cy="200" r="172" fill="url(#shade)"/>
                        <circle cx="200" cy="200" r="172" fill="none" stroke="#ffffff" stroke-opacity="0.25" stroke-width="2"/>
                    </svg>

                    <span class="animate-currency-pop absolute top-6 left-0 inline-flex items-center gap-1.5 rounded-full border border-emerald-300/40 bg-emerald-100/90 px-3 py-1 text-xs font-bold text-emerald-800 shadow-lg backdrop-blur dark:border-emerald-500/30 dark:bg-emerald-500/20 dark:text-emerald-200" style="animation-delay:0s">$ USD</span>
                    <span class="animate-currency-pop absolute top-2 right-0 inline-flex items-center gap-1.5 rounded-full border border-amber-300/40 bg-amber-100/90 px-3 py-1 text-xs font-bold text-amber-800 shadow-lg backdrop-blur dark:border-amber-500/30 dark:bg-amber-500/20 dark:text-amber-200" style="animation-delay:1s">€ EUR</span>
                    <span class="animate-currency-pop absolute top-1/2 -left-3 inline-flex items-center gap-1.5 rounded-full border border-sky-300/40 bg-sky-100/90 px-3 py-1 text-xs font-bold text-sky-800 shadow-lg backdrop-blur dark:border-sky-500/30 dark:bg-sky-500/20 dark:text-sky-200" style="animation-delay:2s">FC CDF</span>
                    <span class="animate-currency-pop absolute top-1/2 -right-3 inline-flex items-center gap-1.5 rounded-full border border-rose-300/40 bg-rose-100/90 px-3 py-1 text-xs font-bold text-rose-800 shadow-lg backdrop-blur dark:border-rose-500/30 dark:bg-rose-500/20 dark:text-rose-200" style="animation-delay:3s">CFA</span>
                    <span class="animate-currency-pop absolute bottom-6 left-2 inline-flex items-center gap-1.5 rounded-full border border-violet-300/40 bg-violet-100/90 px-3 py-1 text-xs font-bold text-violet-800 shadow-lg backdrop-blur dark:border-violet-500/30 dark:bg-violet-500/20 dark:text-violet-200" style="animation-delay:4s">£ GBP</span>
                    <span class="animate-currency-pop absolute bottom-3 right-0 inline-flex items-center gap-1.5 rounded-full border border-teal-300/40 bg-teal-100/90 px-3 py-1 text-xs font-bold text-teal-800 shadow-lg backdrop-blur dark:border-teal-500/30 dark:bg-teal-500/20 dark:text-teal-200" style="animation-delay:5s">XOF</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Fonctionnalités ===== --}}
    <section id="fonctionnalites" class="mx-auto max-w-6xl scroll-mt-24 px-4 py-20 sm:px-6 sm:py-28">
        <div data-reveal class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-emerald-600">Fonctionnalités</p>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Tout ce qu'il faut pour maîtriser votre budget</h2>
            <p class="mt-4 text-lg text-slate-500 dark:text-slate-400">Des outils pensés pour les particuliers, les couples et la diaspora.</p>
        </div>

        <div class="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @php
                $features = [
                    ['globe', 'Multi-devises (110+)', 'USD, Franc congolais (CDF), CFA et plus. Gestion bimétaire USD/CDF pour la RDC.', 'from-emerald-500 to-teal-500'],
                    ['users', 'Mode couple', 'Créez un foyer, partagez vos dépenses communes et gardez vos dépenses personnelles privées.', 'from-amber-500 to-orange-500'],
                    ['lock', 'Chiffrement de bout en bout', 'Vos montants sont verrouillés dès la saisie. Personne, pas même nos équipes, ne peut les lire.', 'from-sky-500 to-indigo-500'],
                    ['download', 'Rapports PDF', 'Générez des rapports financiers professionnels, par catégorie ou par période.', 'from-rose-500 to-pink-500'],
                    ['sparkles', 'Analyses intelligentes', 'Prévisions de fin de mois, alertes de budget et catégorisation automatique de vos dépenses.', 'from-violet-500 to-purple-500'],
                    ['shield', 'Sécurité bancaire', 'Connexions surveillées et protection automatique contre toute activité suspecte.', 'from-teal-500 to-emerald-500'],
                ];
            @endphp
            @foreach ($features as $f)
                <div data-reveal class="card group">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $f[3] }} text-white shadow-lg transition-transform duration-300 group-hover:rotate-6 group-hover:scale-110">
                        <x-icon name="{{ $f[0] }}" class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-bold">{{ $f[1] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $f[2] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== Confidentialité (sans jargon technique) ===== --}}
    <section id="confidentialite" class="scroll-mt-24 border-y border-slate-200 bg-white py-20 dark:border-slate-800 dark:bg-slate-900 sm:py-28">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div data-reveal class="mx-auto max-w-2xl text-center">
                <p class="text-sm font-bold uppercase tracking-widest text-emerald-600">Confidentialité</p>
                <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Vos données sont entre de bonnes mains</h2>
                <p class="mt-4 text-lg text-slate-500 dark:text-slate-400">
                    Nous savons à quel point vos finances sont personnelles. C'est pourquoi la protection de vos
                    données n'est pas une option : c'est notre fondation.
                </p>
            </div>

            <div class="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @php
                    $trust = [
                        ['shield', 'Verrouillé dès la saisie', 'Vos informations sont protégées par une sécurité de niveau bancaire dès leur enregistrement.'],
                        ['lock', 'Illisible, même pour nous', 'Vos montants restent privés. Personne — pas même nos équipes — ne peut les consulter.'],
                        ['users', 'Jamais revendues', 'Vos données ne sont ni vendues, ni partagées, ni utilisées à des fins publicitaires.'],
                        ['bolt', 'Vous restez maître', 'Supprimez votre compte et toutes vos données à tout moment, en un clic.'],
                    ];
                @endphp
                @foreach ($trust as $t)
                    <div data-reveal class="rounded-2xl border border-slate-200 bg-slate-50 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:border-slate-800 dark:bg-slate-800/60">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                            <x-icon name="{{ $t[0] }}" class="h-6 w-6" />
                        </div>
                        <h3 class="mt-4 font-bold">{{ $t[1] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $t[2] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ===== Section couple (photo) ===== --}}
    <section class="relative overflow-hidden bg-emerald-950 py-20 text-white sm:py-28">
        <div class="absolute -right-32 -top-32 h-96 w-96 rounded-full bg-emerald-500/20 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-32 h-96 w-96 rounded-full bg-amber-500/10 blur-3xl"></div>

        <div class="relative mx-auto grid max-w-6xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-2">
            <div data-reveal class="relative">
                <img src="https://images.unsplash.com/photo-1521791136064-7986c2920216?q=80&w=1200&auto=format&fit=crop"
                     alt="Un couple gère son budget sereinement"
                     class="aspect-[4/3] w-full rounded-3xl object-cover shadow-2xl shadow-black/40"
                     loading="lazy">
                <div class="absolute -bottom-5 -right-4 hidden animate-float rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-md sm:block">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 text-white">
                            <x-icon name="trend-up" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-xs text-emerald-100/80">Économies ce mois</p>
                            <p class="text-lg font-extrabold">+ 24 %</p>
                        </div>
                    </div>
                </div>
            </div>

            <div data-reveal>
                <p class="text-sm font-bold uppercase tracking-widest text-amber-300">Pensé pour les couples</p>
                <h2 class="mt-3 text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl">Un seul foyer, une vision claire</h2>
                <p class="mt-4 text-lg text-emerald-100/80">
                    Gérez vos finances à deux en toute transparence, sans perdre votre intimité. Le mode couple
                    sépare les dépenses communes des dépenses personnelles.
                </p>
                <ul class="mt-8 space-y-4">
                    @foreach (['Dépenses partagées et personnelles distinctes', 'Budget familial avec alertes en temps réel', 'Multi-devises : suivez vos revenus en USD et vos dépenses en CDF ou CFA'] as $item)
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500/20 text-emerald-300">
                                <x-icon name="check" class="h-4 w-4" />
                            </span>
                            <span class="text-emerald-50/90">{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('register') }}" class="btn-primary mt-9">
                    Créer mon foyer
                    <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>
        </div>
    </section>

    {{-- ===== Comment ça marche ===== --}}
    <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-28">
        <div data-reveal class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-emerald-600">Comment ça marche</p>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">En route en moins de 2 minutes</h2>
        </div>

        <div class="mt-14 grid grid-cols-1 gap-6 md:grid-cols-3">
            @foreach ([
                ['bolt', '1', 'Créez votre compte', 'Inscrivez-vous avec Google ou votre email. 7 jours d\'essai premium offerts.'],
                ['wallet', '2', 'Ajoutez vos dépenses', 'Saisissez vos transactions en quelques secondes, dans la devise de votre choix.'],
                ['chart', '3', 'Suivez vos finances', 'Visualisez vos habitudes, recevez des alertes et exportez des rapports PDF.'],
            ] as $step)
                <div data-reveal class="relative rounded-2xl border border-slate-200 bg-white p-7 text-center shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-500/30">
                        <x-icon name="{{ $step[0] }}" class="h-7 w-7" />
                    </div>
                    <span class="absolute right-4 top-4 text-5xl font-black text-slate-100 dark:text-slate-800">{{ $step[1] }}</span>
                    <h3 class="mt-5 text-lg font-bold">{{ $step[2] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">{{ $step[3] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== FAQ ===== --}}
    <section id="faq" class="mx-auto max-w-3xl scroll-mt-24 px-4 py-20 sm:px-6 sm:py-28">
        <div data-reveal class="text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-emerald-600">FAQ</p>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Questions fréquentes</h2>
        </div>

        <div data-reveal class="mt-10 divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white px-6 dark:divide-slate-800 dark:border-slate-800 dark:bg-slate-900">
            @php
                $faqs = [
                    ['L\'essai est-il vraiment gratuit ?', 'Oui, à 100 %. Vous profitez de toutes les fonctionnalités premium pendant 7 jours, sans carte bancaire et sans engagement.'],
                    ['Puis-je utiliser Vesper à deux ?', 'Absolument. Le mode couple crée un foyer partagé, tout en gardant vos dépenses personnelles privées.'],
                    ['Mes données sont-elles vraiment protégées ?', 'Oui. Vos informations sont protégées par une sécurité de niveau bancaire. Personne — pas même nos équipes — ne peut lire vos montants, et vos données ne sont jamais revendues.'],
                    ['Quelles devises sont prises en charge ?', 'Plus de 110 devises, dont l\'USD, le CDF, le CFA, l\'EUR et le GBP, avec conversion automatique et taux à jour.'],
                    ['Puis-je annuler à tout moment ?', 'Oui, sans engagement. Vous pouvez annuler votre abonnement et supprimer toutes vos données en un clic.'],
                    ['Sur quels appareils puis-je utiliser Vesper ?', 'Partout : mobile, tablette et ordinateur. Vesper est une application web installable, rapide et légère.'],
                ];
            @endphp
            @foreach ($faqs as $faq)
                <details class="group">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 py-5 text-left font-semibold text-slate-900 dark:text-slate-100">
                        {{ $faq[0] }}
                        <svg class="h-5 w-5 shrink-0 text-slate-400 transition-transform duration-300 group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                    </summary>
                    <p class="pb-5 text-slate-500 dark:text-slate-400">{{ $faq[1] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    {{-- ===== Appel à l'action final ===== --}}
    <section id="tarifs" class="relative scroll-mt-24 overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image:url('https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?q=80&w=1920&auto=format&fit=crop')"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-emerald-950/95 via-emerald-900/85 to-teal-900/80"></div>

        <div class="relative mx-auto max-w-4xl px-4 py-24 text-center text-white sm:px-6 sm:py-32">
            <div data-reveal>
                <h2 class="text-3xl font-extrabold leading-tight tracking-tight sm:text-5xl">Commencez votre essai gratuit de 7 jours</h2>
                <p class="mx-auto mt-4 max-w-xl text-lg text-emerald-100/85">
                    Aucune carte bancaire requise. Toutes les fonctionnalités premium incluses dès aujourd'hui.
                </p>
                <div class="mt-9 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="btn-primary text-base">
                        Créer mon compte gratuit
                        <x-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                    <a href="{{ route('pricing') }}" class="btn-ghost !border-white/20 !bg-white/10 !text-white hover:!border-white/40 hover:!text-white">
                        Voir les tarifs
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== Pied de page ===== --}}
    <footer class="border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-950">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 py-10 text-center sm:flex-row sm:px-6 sm:text-left">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 text-white">
                    <x-icon name="wallet" class="h-4 w-4" />
                </span>
                <span class="text-lg font-extrabold">Vesper</span>
            </div>
            <p class="text-sm text-slate-400">© {{ date('Y') }} Vesper — Vos données restent privées, toujours.</p>
            <nav class="flex items-center gap-5 text-sm text-slate-500 dark:text-slate-400">
                <a href="{{ route('login') }}" class="transition hover:text-emerald-600 dark:hover:text-emerald-400">Connexion</a>
                <a href="{{ route('pricing') }}" class="transition hover:text-emerald-600 dark:hover:text-emerald-400">Tarifs</a>
            </nav>
        </div>
    </footer>

</body>
</html>