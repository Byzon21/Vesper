<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="description" content="Vesper — Suivez vos dépenses personnelles et de couple, gérez 110+ devises, avec une sécurité de niveau bancaire.">
    <meta name="theme-color" content="#022c22">
    <title>Vesper — Vos finances personnelles et de couple, en toute sérénité</title>

    {{-- Pose la classe .js dès que possible (anti-flash pour les animations au scroll) --}}
    <script nonce="{{ $cspNonce ?? '' }}">document.documentElement.classList.add('js');</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">

    {{-- ===== Barre de navigation (verre, sticky) ===== --}}
    <header class="sticky top-0 z-50 border-b border-white/10 bg-emerald-950/80 text-white shadow-lg shadow-emerald-950/20 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3.5 sm:px-6">
            <a href="{{ route('landing') }}" class="group flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-teal-500 text-white shadow-lg shadow-emerald-500/40 transition-transform duration-300 group-hover:rotate-6">
                    <x-icon name="wallet" class="h-5 w-5" />
                </span>
                <span class="text-xl font-extrabold tracking-tight">Vesper</span>
            </a>
            <nav class="flex items-center gap-2 sm:gap-3">
                <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-emerald-50/90 transition hover:bg-white/10 hover:text-white sm:px-4">
                    Se connecter
                </a>
                <a href="{{ route('register') }}" class="btn-primary !px-4 !py-2 text-sm sm:!px-5">
                    Essai gratuit
                </a>
            </nav>
        </div>
    </header>

    {{-- ===== Héros (photo + dégradé + orbes) ===== --}}
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
                Suivez vos dépenses, gérez 110+ devises (USD, CDF, CFA…) et comprenez vos habitudes —
                avec un chiffrement de niveau bancaire. Même nous ne pouvons pas lire vos montants.
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
        <div class="mx-auto grid max-w-5xl grid-cols-2 gap-4 rounded-2xl border border-slate-200 bg-white/90 p-6 shadow-xl shadow-slate-900/5 backdrop-blur md:grid-cols-4">
            @foreach ([
                ['110+', 'Devises gérées'],
                ['AES-256', 'Chiffrement'],
                ['7 jours', 'D\'essai gratuit'],
                ['0', 'Carte bancaire requise'],
            ] as $stat)
                <div class="text-center">
                    <p class="text-3xl font-extrabold tracking-tight text-emerald-700">{{ $stat[0] }}</p>
                    <p class="mt-1 text-sm text-slate-500">{{ $stat[1] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== Fonctionnalités ===== --}}
    <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-28">
        <div data-reveal class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-bold uppercase tracking-widest text-emerald-600">Fonctionnalités</p>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Tout ce qu'il faut pour maîtriser votre budget</h2>
            <p class="mt-4 text-lg text-slate-500">Des outils pensés pour les particuliers, les couples et la diaspora.</p>
        </div>

        <div class="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @php
                $features = [
                    ['globe', 'Multi-devises (110+)', 'USD, Franc congolais (CDF), CFA et plus. Gestion bimétaire USD/CDF pour la RDC.', 'from-emerald-500 to-teal-500'],
                    ['users', 'Mode couple', 'Créez un foyer, partagez vos dépenses communes et gardez vos dépenses personnelles privées.', 'from-amber-500 to-orange-500'],
                    ['lock', 'Chiffrement de bout en bout', 'AES-256-GCM, hiérarchie de clés et Zero Trust. Vos montants restent illisibles pour tous, même pour nous.', 'from-sky-500 to-indigo-500'],
                    ['download', 'Rapports PDF', 'Générez des rapports financiers professionnels, par catégorie ou par période.', 'from-rose-500 to-pink-500'],
                    ['sparkles', 'Analyses intelligentes', 'Prévisions de fin de mois, alertes de budget et catégorisation automatique par machine learning.', 'from-violet-500 to-purple-500'],
                    ['shield', 'Sécurité bancaire', 'JWKS, HMAC, rate-limiting, détection de bots et en-têtes de sécurité stricts.', 'from-teal-500 to-emerald-500'],
                ];
            @endphp
            @foreach ($features as $f)
                <div data-reveal class="card group">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $f[3] }} text-white shadow-lg transition-transform duration-300 group-hover:rotate-6 group-hover:scale-110">
                        <x-icon name="{{ $f[0] }}" class="h-6 w-6" />
                    </div>
                    <h3 class="mt-5 text-lg font-bold">{{ $f[1] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $f[2] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== Section couple + multi-devises (photo) ===== --}}
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
                <div data-reveal class="relative rounded-2xl border border-slate-200 bg-white p-7 text-center shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-lg shadow-emerald-500/30">
                        <x-icon name="{{ $step[0] }}" class="h-7 w-7" />
                    </div>
                    <span class="absolute right-4 top-4 text-5xl font-black text-slate-100">{{ $step[1] }}</span>
                    <h3 class="mt-5 text-lg font-bold">{{ $step[2] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $step[3] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ===== Appel à l'action final (photo) ===== --}}
    <section class="relative overflow-hidden">
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
    <footer class="border-t border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-4 py-10 text-center sm:flex-row sm:px-6 sm:text-left">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 text-white">
                    <x-icon name="wallet" class="h-4 w-4" />
                </span>
                <span class="text-lg font-extrabold">Vesper</span>
            </div>
            <p class="text-sm text-slate-400">© {{ date('Y') }} Vesper — Vos données sont chiffrées de bout en bout.</p>
            <nav class="flex items-center gap-5 text-sm text-slate-500">
                <a href="{{ route('login') }}" class="transition hover:text-emerald-600">Connexion</a>
                <a href="{{ route('pricing') }}" class="transition hover:text-emerald-600">Tarifs</a>
            </nav>
        </div>
    </footer>

</body>
</html>
