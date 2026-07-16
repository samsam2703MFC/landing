# Documentation — Landing L'Atelier By

Documentation technique du site vitrine **entièrement piloté par la base de données**.
Tout le contenu éditorial (textes, images, liens, sections) se modifie depuis **PHPMyAdmin**, sans toucher au code.

---

## 1. Architecture générale

```
┌─────────────┐      ┌──────────────┐      ┌─────────────────────┐
│  MySQL       │─────▶│  lp_api.php   │─────▶│  index.html (JS)     │
│  tables lp_* │  SQL │  REST JSON    │ HTTP │  patch functions     │
└─────────────┘      └──────────────┘      └─────────────────────┘
```

1. Les tables `lp_*` contiennent tout le contenu.
2. `lp_api.php` lit ces tables et renvoie du **JSON**.
3. Les pages HTML récupèrent ce JSON au chargement et **remplacent** le contenu par défaut (« patch »).

Le HTML contient toujours une **version de secours (fallback)** codée en dur : si l'API est injoignable, la page reste affichable. Dès que l'API répond, la DB prend le dessus.

### Serveur
- Site servi sous : `http://185.180.206.46/landing/`
- Racine serveur : `/var/www/latelierby-landing`
- Déploiement : **GitHub Actions** → `git pull` automatique à chaque push sur `main`.

### Base de données
| Paramètre | Valeur |
|---|---|
| Hôte | `localhost` |
| Base | `atelierby_db` |
| Utilisateur | `sam` |
| Identifiants | codés dans chaque `lp_*.php` (constantes `LP_DB_*`) |

> ⚠️ **Cache** : `lp_api.php` envoie `Cache-Control: max-age=300` (5 min). Après une modif en DB, faites **Ctrl+Shift+R** ou attendez 5 min pour voir le changement.

---

## 2. Quelle table pilote quelle partie ?

| Zone de la page | Table(s) | Route API |
|---|---|---|
| Carrousel héro | `lp_hero_slides` | `hero` |
| Section « Les produits » (familles) | `lp_product_families` | `families` |
| En-têtes de sections (eyebrow/titre/lede) | `lp_sections` | `sections` |
| Section « Collaborations » | `lp_collaborations` | `collabs` |
| Carrousel « Éditions de saison » | `lp_seasonal_items` | `seasonal` |
| Section « Nos services » | `lp_services` | `services` |
| Section « Boutiques » + picker webshop | `lp_shops` (+ `lp_shop_hours`, `lp_shop_services`) | `shops`, `pickers` |
| Section « Franchise » (accueil) | `lp_franchise_section` | `franchise` |
| Section « Application mobile » + QR | `lp_app_section` + `lp_params.app_url` | `app` |
| Menu de navigation (items spéciaux) | `lp_nav_items` | `nav` |
| Liens du footer | `lp_footer_links` | `footer` |
| Tous les textes d'interface (FR/NL) | `lp_i18n` | `i18n` |
| Réglages globaux (URLs, app) | `lp_params` | `params` |
| Page franchise (`franchise-lead.html`) | `lp_franchise_i18n` + `lp_franchise_zones` | `franchise_page` |
| Page légale (`mentions-legales.html`) | `lp_legal` | `legal` |
| Envoi d'e-mails | `lp_mail_params`, `lp_mail_log`, `lp_candidates` | — |

---

## 3. Détail des tables

Conventions : les colonnes en `_fr` / `_nl` sont bilingues. `is_active = 0` masque une ligne. `position` / `sort_order` définit l'ordre.

### `lp_hero_slides` — carrousel d'accueil
`id, position, eyebrow_fr, eyebrow_nl, title_fr, title_nl, lede_fr, lede_nl, cta1_text_fr, cta1_text_nl, cta1_url, cta2_text_fr, cta2_text_nl, cta2_url, image_path, ws_product_slug, is_active`
- `title_*` accepte du HTML (`<span class="script">...</span>` pour l'accent).
- Garder **4 slides actives** (le carrousel est calibré sur 4).

### `lp_product_families` — les 5 familles de produits
`position, name_fr, name_nl, count_fr, count_nl, image_path, href, is_active`

### `lp_sections` — en-têtes de sections
`section_key, eyebrow_fr, eyebrow_nl, title_fr, title_nl, lede_fr, lede_nl`
- `section_key` ∈ `produits`, `collaborations`, `saison`, `boutiques`, `experiences`, `app`.

### `lp_collaborations`
`id, position, tag_fr, tag_nl, name_fr, name_nl, desc_fr, desc_nl, image_path, shop_url, is_active`

### `lp_seasonal_items` — carrousel de saison
`id, position, tag_fr, tag_nl, name_fr, name_nl, desc_fr, desc_nl, image_path, item_url, ws_product_slug, available_from, available_until, is_active`

### `lp_services` — section « Nos services »
`id, position, name_fr, name_nl, desc_fr, desc_nl, icon_svg, is_active`
- `icon_svg` = contenu SVG brut (ex. `<circle .../><path .../>`), inséré dans `<svg viewBox="0 0 24 24">…</svg>`.

### `lp_shops` — boutiques **et** points webshop
`id, sort_order, name, city, postal_code, kind, address, phone, email, concept_fr, concept_nl, image_path, webshop_url, is_active, picker_key, zone, lat, lng, webshop_active`
- **`is_active = 1`** → apparaît dans la section **Boutiques**.
- **`webshop_active = 1`** → apparaît dans le **picker** « Choisissez votre boutique ».
- `kind` ∈ `shop`, `popup`.
- Picker : étiquette = **`city`**, lien = **`webshop_url`**, géoloc = **`lat` / `lng`**.

#### `lp_shop_hours`
`id, shop_id, day, hours` — `day` ∈ `mon,tue,wed,thu,fri,sat,sun` ; `hours` ex. `7:00 – 19:00` ou `closed`.

#### `lp_shop_services`
`id, shop_id, service_key` — `service_key` ∈ `collect, delivery, catering, b2b, phone, office, loyalty`.

### `lp_franchise_section` — bloc franchise sur l'accueil
`title_fr, title_nl, lede_fr, lede_nl, point1_fr, point1_nl, point2_fr, point2_nl, point3_fr, point3_nl, cta_text_fr, cta_text_nl, cta_url`
- **`cta_url`** = destination du bouton « Recevoir le dossier ».

### `lp_app_section` — section app mobile
`id, title_fr, title_nl, lede_fr, lede_nl, point1_fr, point1_nl, point2_fr, point2_nl, point3_fr, point3_nl, cta_text_fr, cta_text_nl, hint_fr, hint_nl`
- Le **lien du QR code et de la bannière mobile** vient de `lp_params.app_url` (voir ci-dessous).

### `lp_nav_items` — items spéciaux du menu (ex. Galette)
`id, position, label_fr, label_nl, url, icon, hex_color, is_active`
- `is_active = 1` affiche l'item ; `= 0` le masque (idéal pour du saisonnier).
- `icon` = emoji (`👑`) **ou** SVG complet (`<svg…>…</svg>`).
- `hex_color` = couleur du texte (ex. `#8A6200`).

### `lp_footer_links` — liens du pied de page
`id, col, position, label_fr, label_nl, url, is_active`
- `col` : `1` = Explorer, `2` = Services, `3` = La Maison.
- Si tous les liens d'une colonne sont inactifs → **la colonne entière disparaît**.
- Les **titres** de colonnes restent dans `lp_i18n` (`ft.explore`, `ft.services`, `ft.house`).

### `lp_i18n` — textes d'interface (FR/NL)
`i18n_key, value_fr, value_nl`
- Clés : navigation, filtres, jours, footer, labels de modales, picker, QR… (~55 clés).
- Modifier une valeur ici change le texte sur tout le site.

### `lp_params` — réglages globaux (clé/valeur)
`param_key, param_value`
| Clé | Rôle |
|---|---|
| `app_url` | Lien de l'app/PWA (QR code **et** bannière mobile) |
| `site_name` | Nom du site |
| `galette_url` | Destination des liens « Galette des Rois » |
| `franchise_url` | (non utilisé par le bouton franchise — voir `lp_franchise_section.cta_url`) |

### `lp_legal` — données légales (page mentions-legales)
`company_name, legal_form, address, bce_number, vat_number, editor_name, contact_email, contact_phone, host_name, host_address, host_contact, dpo_email, data_retention, payment_methods, jurisdiction, audience_tool, updated_date`
- **Une seule ligne.** Chaque champ remplit un emplacement de la page légale.

### `lp_franchise_i18n` — textes de la page franchise
`i18n_key, value_fr, value_nl` (~60 clés : héro, profil, prérequis, formulaire, modale, footer).

### `lp_franchise_zones` — zones du menu déroulant franchise
`id, lang, group_label, group_pos, value, label, pos, is_active`
- `lang` ∈ `fr`, `nl` ; `group_label` = province (l'optgroup) ; `value`/`label` = option.

### E-mails
- **`lp_mail_params`** : config d'envoi — `from_email, from_name, notify_email, notify_name, notify_subject_fr/nl, confirm_subject_fr/nl, confirm_intro_fr/nl, smtp_host, smtp_port, smtp_user, smtp_pass, smtp_secure, brevo_api_key`. Priorité d'envoi : **Brevo API** → SMTP → `mail()`.
- **`lp_candidates`** : leads franchise reçus.
- **`lp_mail_log`** : journal de chaque e-mail envoyé (`type, to_email, subject, status, error_msg, sent_at`).

---

## 4. API — `lp_api.php`

Une seule requête charge tout : `GET /landing/lp_api.php?r=all`

Routes individuelles : `?r=hero`, `seasonal`, `collabs`, `franchise`, `shops`, `pickers`, `families`, `sections`, `services`, `app`, `nav`, `footer`, `i18n`, `params`, `all`.
Routes dédiées aux autres pages : `?r=franchise_page` (page franchise), `?r=legal` (page légale).

- `?r=all` renvoie 14 clés (tout sauf `legal` et `franchise_page`, servis à part).
- Chaque handler est isolé : si une table manque, seul son bloc est vide — le reste de la page fonctionne.

---

## 5. Recettes courantes (PHPMyAdmin)

| Objectif | Action |
|---|---|
| Changer un texte d'interface | `lp_i18n` → modifier `value_fr` / `value_nl` de la clé |
| Masquer une collaboration / un produit saisonnier | Mettre `is_active = 0` sur la ligne |
| Ajouter une boutique | Insérer une ligne dans `lp_shops` (+ horaires/services si besoin) |
| Faire apparaître une boutique dans le picker | `webshop_active = 1` + remplir `webshop_url`, `lat`, `lng` |
| Activer « Galette des Rois » dans le menu | `lp_nav_items` → ligne galette → `is_active = 1` |
| Changer le lien du bouton franchise | `lp_franchise_section` → `cta_url` |
| Changer le lien du QR / de la bannière app | `lp_params` → `app_url` |
| Compléter les mentions légales | `lp_legal` → remplir les colonnes |
| Ajouter une zone franchise | `lp_franchise_zones` → insérer (lang, group_label, value, label) |

> Après chaque modification : **Ctrl+Shift+R** (cache API 5 min).

---

## 6. Installation d'une table (nouveau serveur)

Chaque table a un script `lp_install_*.php`. Pour (ré)initialiser :
1. Déposer le fichier sur le serveur (via `git pull`).
2. Visiter une fois `http://185.180.206.46/landing/lp_install_<table>.php`.
3. **Supprimer le fichier** ensuite (il est public).

Scripts disponibles : `lp_install_hero, families, sections, collabs, seasonal, services, shops, app, nav, footer, i18n, legal, franchise_page, mail`.

Les scripts `lp_migrate_*.php` et `lp_fix_*.php` / `lp_diag_*.php` sont des utilitaires ponctuels — **à supprimer après usage**.

---

## 7. Pages du site

| Fichier | Rôle | Source DB |
|---|---|---|
| `index.html` | Accueil | `?r=all` |
| `franchise-lead.html` | Formulaire de franchise | `?r=franchise_page` + POST `lp_lead.php` |
| `mentions-legales.html` | Mentions légales / RGPD / CGV | `?r=legal` |
| `livraison-bureau.html` | Landing « Livraison bureau » (B2B) | `?r=od` (+ footer partagé) |

---

## 10. Livraison bureau (`livraison-bureau.html`) — tables `lp_od_*`

Page B2B « Livraison au bureau », **entièrement pilotée par la base** comme le
reste du site : contenu codé en dur comme *fallback*, puis remplacé par la DB
via `lp_api.php?r=od`. Si les tables `lp_od_*` n'existent pas encore, la page
reste pleinement fonctionnelle sur son fallback.

**Installation** : visitez une fois `…/landing/lp_od_install.php` (crée + seed
les 13 tables), puis **supprimez le fichier**. Comme les autres `lp_install_*`.

| Zone de la page | Table `lp_od_*` |
|---|---|
| Tous les textes d'interface (FR/NL) | `lp_od_i18n` (clé/valeur bilingue) |
| Listes simples (recPoints, prodAssure, contactPoints, clients, freq) | `lp_od_list` |
| Boutiques du réseau (routage) — `sid=0` = direction | `lp_od_shops` |
| Zones de livraison (jours, cutoff, créneaux, minimum, note) | `lp_od_zones` |
| Section « Pour qui, pour quoi » | `lp_od_usecases` |
| Section « Comment ça marche » (4 étapes) | `lp_od_steps` |
| Section « Mise en place » (5 étapes de déploiement) | `lp_od_rollout` |
| Carrousel « Les formules » | `lp_od_offers` (includes séparés par `|`) |
| Carrousel « Nos produits » | `lp_od_products` |
| Chiffres de preuve | `lp_od_specs` |
| Témoignages | `lp_od_testimonials` |
| FAQ | `lp_od_faqs` |
| Champs du formulaire de contact | `lp_od_form_fields` |
| **Footer** | **partagé** : `lp_footer_links` + `lp_i18n` (`ft.*`) |

- `lp_od_zones.days` = codes séparés par virgule (`lun,mar,mer`) ; `slots` séparés par `|`.
- Le **footer est harmonisé** avec le reste du site : il lit `lp_footer_links`
  (colonnes 1/2/3) et les titres `ft.explore` / `ft.services` / `ft.house` de
  `lp_i18n` — modifier le footer du site met à jour cette page aussi.
- Route API : `?r=od` renvoie tout le contenu `lp_od_*` **plus** le footer partagé.

---

## 8. Points d'attention

- **Cache API 5 min** : toujours faire Ctrl+Shift+R après une modif DB.
- **Géolocalisation** : le bouton « boutique la plus proche » exige **HTTPS**. Sur HTTP nu, il affiche « Localisation indisponible » — la sélection manuelle fonctionne quand même.
- **Lien « Galette des Rois »** : pointe vers `galette-des-rois.html` (page non créée). Changez `lp_params.galette_url` vers une page existante (ex. `index.html#saison`) ou créez la page.
- **Sécurité** : les identifiants DB sont en clair dans les `lp_*.php`. Repo à garder **privé** ; idéalement, déplacer les identifiants dans un fichier de config non versionné.
- **Bannière app mobile** : s'affiche sur ≤760px, pointe vers `lp_params.app_url`, refermable (mémorisé). Renseignez la vraie adresse PWA.

---

## 9. Déploiement

Push sur `main` → GitHub Actions (`.github/workflows/deploy.yml`) se connecte en SSH au serveur et exécute `git pull` dans `/var/www/latelierby-landing`. Aucune action manuelle nécessaire.
