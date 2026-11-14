# Hébergement — Étude de faisabilité (Vercel & alternatives gratuites)

## 1. Vercel : verdict

**Vercel n'est pas adapté pour héberger Vesper.** Raison technique fondamentale :

> Vercel Functions ne supporte **pas de runtime PHP**. Les runtimes officiels sont :
> **Node.js, Bun, Python, Rust, Go, Ruby, Wasm, Container**.

Or Vesper repose sur **Laravel 11 (PHP) avec Livewire**, c'est-à-dire un rendu **côté serveur**. Il n'existe pas de « frontend statique » à déployer sur Vercel : chaque page est rendue par PHP. Déployer Vesper sur Vercel exigerait une refonte majeure (SPA statique + API Node), ce qui casserait le modèle BFF sécurisé (Vault, chiffrement, sessions, proxy Supabase) — **déconseillé**.

Le runtime « Container » de Vercel pourrait en théorie embarquer PHP via Docker, mais c'est une offre avancée, non gratuite, et hors du cadre « hébergement gratuit ».

## 2. Alternatives gratuites pour Laravel

| Plateforme | Gratuit ? | PHP/Laravel | Notes |
|---|---|---|---|
| **Render** | ✅ Free tier (512 Mo) | ✅ natif + Docker | Le plus simple pour Laravel ; *cold start* après inactivité |
| **Railway** | ⚠️ crédit d'essai | ✅ Docker | Usage-based, plus de « free forever » |
| **Oracle Cloud** | ✅ Always Free | ✅ VPS complet | VPS ARM 4 OCPU/24 Go à vie — le plus puissant, plus de config |
| **Koyeb** | ✅ 1 service free | ✅ Docker | Simple, mais limites strictes |
| **Fly.io** | ⚠️ quasi-payant | ✅ Docker | Petite allowance, facturation à l'usage |
| **Vercel** | ❌ (PHP) | ❌ | Non adapté (voir §1) |

**Recommandation** : la base de données est déjà **gratuite chez Supabase** (Postgres + Auth + Storage). Pour le backend Laravel :
- **Render Free tier** si vous voulez le déploiement le plus simple (Git push → build automatique, variables d'environnement).
- **Oracle Cloud Always Free** si vous voulez un VPS gratuit à vie, illimité, avec contrôle total (Nginx + PHP-FPM + Redis + file d'attente).

## 3. Déploiement recommandé — Render (free tier)

```bash
# 1. Pousser le projet sur GitHub (cf. README)
# 2. Sur render.com : New → Web Service → connecter le repo
#    - Runtime : Docker (ou "Native" avec build command)
#    - Build : composer install --no-dev && npm ci && npm run build
#    - Start : php artisan serve --host=0.0.0.0 --port=$PORT
# 3. Variables d'environnement (secrets via Vault/settings, jamais en clair) :
#    APP_KEY, SUPABASE_*, POSTHOG_*, VAULT_* …
# 4. Ajouter le domaine + forcer HTTPS (TLS)
```

Fichier `Dockerfile` de référence (à placer à la racine) :

```dockerfile
FROM php:8.3-fpm-alpine

RUN apk add --no-cache nginx supervisor libsodium libzip \
    && docker-php-ext-install bcmath pdo opcache sodium

WORKDIR /var/www/html
COPY . .
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader

EXPOSE 80
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
```

## 4. Contraintes de sécurité au déploiement

- `SESSION_SECURE_COOKIE=true`, `APP_DEBUG=false`, TLS forcé.
- Secrets (`service_role`, clés PostHog personnelles, clés de chiffrement) via **Vault** ou variables d'environnement du provider — **jamais** dans le repo.
- Activer HSTS (déjà configuré dans `SecurityHeaders`).
- La clé maîtresse du Vault local (`VAULT_MASTER_KEY`) doit être injectée par le provider (KMS recommandé en production).
