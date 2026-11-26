# BizManager

BizManager est une application Laravel de gestion commerciale destinée aux petites et moyennes entreprises. L’interface est en français et les données métier sont isolées par entreprise.

## Fonctionnalités

- Tableau de bord avec ventes, factures impayées, dépenses du mois, alertes de stock, ventes récentes et produits les plus vendus.
- Rapports de période pour les ventes terminées, les achats reçus et les dépenses, avec export CSV.
- Gestion des produits, catégories, clients et fournisseurs; les listes des produits et clients sont filtrables et paginées.
- Enregistrement des ventes et achats avec workflow brouillon → validation/réception → annulation; le stock n’est modifié qu’à la validation et restauré lors d’une annulation; listes filtrables et paginées par numéro, tiers, statut et période.
- Création de factures à partir des ventes et suivi de leur statut de paiement.
- Recherche et filtres de factures par numéro, client et statut de paiement.
- Suivi des mouvements de stock et des dépenses, avec catégories de dépenses et filtres par période, catégorie ou description.
- Recherche globale de produits, clients, fournisseurs, ventes, achats, factures et dépenses.
- Comptes utilisateurs, inscription, authentification et contrôle d’accès par rôle.

## Prérequis

- PHP 8.2 ou ultérieur et Composer.
- MySQL configuré et accessible depuis l’application.
- Node.js et npm pour compiler les ressources front-end.

## Installation locale (PowerShell)

Depuis la racine du projet :

```powershell
composer install
if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
}
```

Configurez ensuite les paramètres de l’application et de la base MySQL dans `.env` (`APP_URL`, `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` et `DB_PASSWORD`). Si `.env` existe déjà, modifiez uniquement les valeurs nécessaires ; ne le remplacez pas.

Pour une nouvelle installation uniquement, générez la clé d’application :

```powershell
php artisan key:generate
```

Avant d’appliquer des migrations, vérifiez que `.env` pointe vers la bonne base et examinez leur état :

```powershell
php artisan migrate:status
php artisan migrate --pretend
```

Si les migrations affichées sont bien attendues, appliquez-les avec `php artisan migrate`. Cette commande modifie le schéma de la base. Ne lancez pas `migrate:fresh` ni `db:wipe` sur une base contenant des données.

Installez et compilez les ressources, puis démarrez le serveur :

```powershell
npm install
npm run build
php artisan serve
```

L’application est alors disponible à l’adresse indiquée par Artisan, généralement `http://127.0.0.1:8000`.

Pour le développement avec Vite, la file d’attente et les journaux Laravel, utilisez `composer run dev` dans un terminal dédié.

## Tests et formatage

```powershell
php artisan test
vendor\bin\pint --test
```

La suite de tests utilise SQLite en mémoire via `phpunit.xml` et ne doit pas écrire dans la base MySQL configurée pour l’application.

## Accès et données

Les pages métier nécessitent une authentification. À l’inscription, un utilisateur peut créer une entreprise ou rejoindre une entreprise existante lorsque le flux d’inscription lui fournit un code d’accès. Ce code est valable 24 heures et à usage unique. Les opérations métier sont limitées à l’entreprise de l’utilisateur connecté et aux autorisations associées à son rôle.

## Déploiement en production

Le dossier `.env.production.example` est la base de configuration production. Le serveur doit fournir PHP 8.2+ (extensions `bcmath`, `intl`, `mbstring`, `pdo_mysql`, `openssl`), MySQL/MariaDB, Node.js et un document root pointing sur `public/`.

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
cp .env.production.example .env
php artisan key:generate
```

Renseignez ensuite `.env` : `APP_URL` en HTTPS, identifiants DB dédiés (pas de `root`), `MAIL_MAILER=smtp` pour que la réinitialisation de mot de passe fonctionne, `SESSION_SECURE_COOKIE=true` et un `APP_KEY` propre à l’environnement. Activez `MYSQL_ATTR_SSL_CA` si le serveur DB le supporte.

Avant la migration, faites une sauvegarde puis vérifiez le schéma :

```bash
php artisan migrate:status
php artisan migrate --pretend
php artisan migrate
```

Ne lancez **jamais** `db:seed` en production : le seeder crée un administrateur de test (`test@example.com` / `password`). Achevons la mise en place avec :

```bash
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

Les dossiers `storage/`, `bootstrap/cache/` et `public/build/` doivent être accessibles en écriture par le serveur web. `public/hot` ne doit **pas** exister en production : sa présence force Vite à utiliser le serveur de développement local.

Le endpoint `GET /up` sert de health-check. Les journaux utilisent le canal `daily` (14 jours de rétention) dès que `LOG_STACK=daily` est défini.

## Sauvegardes et rollback

- Sauvegardez la base avant chaque migration (`mysqldump`) et conservez les fichiers de `storage/app/`.
- Pour annuler une migration employant `down()`, utilisez `php artisan migrate:rollback --step=1`. La migration qui rend `company_id` obligatoire vérifie au préalable qu’aucun enregistrement n’est orphelin et échoue explicitement sinon.
- Après un rollback ou un changement de configuration, régénérez les caches (`php artisan optimize:clear` puis `php artisan optimize`).

## Intégration continue

Le workflow `.github/workflows/ci.yml` exécute, à chaque push et pull request : installation des dépendances, `pint --test`, `php artisan test` et `npm run build`. La suite de tests utilise SQLite en mémoire et n’affecte jamais la base applicative.
