# Module Multi-devises & cas bimétaire RDC (USD/CDF)

## Value Objects (immuables, `App\Domain\Currency`)

- **`Money`** : montant en **chaîne décimale** (jamais de float), arithmétique exacte **BCMath** (add/subtract/multiply/divide, scale 8).
- **`ExchangeRate`** : `1 base = rate quote` (ex. `1 USD = 2800 CDF`), avec `inverse()`.
- **`CurrencyConverter`** : conversion directe/inverse exacte.
- **`BiMonetaryAllocation`** : liste de jambes `Money` (ex. `[40 USD, 25000 CDF]`).
- **`BiMonetarySummary`** : jambes converties + total unifié en devise de base.

## Résolution des taux (`ExchangeRateService`)

Ordre de repli :
1. **Taux manuel persistant** (utilisateur/foyer) — prioritaire et **configurable**,
2. Taux inverse persistant,
3. **Fournisseur externe** (`ExchangeRateProvider`, clé via Vault) direct puis inverse,
4. **Triangulation via USD** pour les paires croisées.

Un cache en mémoire évite les appels répétés dans une requête.

## Cas critique RDC (bimétaire quotidien)

En RDC, l'utilisateur dépense à la fois en **USD** et en **CDF**. Le formulaire propose donc **deux champs** (`usdAmount` + `cdfAmount`). Le composant `CreateTransaction` :

1. construit une `BiMonetaryAllocation` à partir des champs renseignés,
2. appelle `BiMonetaryService::unify(allocation, baseCurrency)` qui convertit chaque jambe vers la devise de base du foyer,
3. obtient un **total unifié** utilisé par les rapports globaux.

```
Saisie : 40 USD + 25 000 CDF   (taux 1 USD = 2 800 CDF)
Base foyer : USD
→ 40 USD                → 40.00000000 USD
→ 25 000 CDF ÷ 2800     →  8.92857142 USD
Total unifié            → 48.92857142 USD
```

Chaque jambe est persistée dans `transaction_legs` (montant chiffré + taux snapshot + montant converti chiffré), ce qui préserve l'historique même si le taux évolue.

## Référentiel devises

Plus de **110 devises ISO 4217** (`currencies`), couvrant l'Afrique (CDF, XAF, XOF, **LSL** Lesotho, **NAD** Namibie, SZL, BWP, ZWG…), l'Europe, les Amériques, l'Asie, l'Océanie et le Moyen-Orient. Les devises à zéro décimale (XAF, XOF, KMF, BIF, JPY, KRW…) sont correctement paramétrées (`decimals=0`).
