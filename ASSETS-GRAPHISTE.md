# Assets à demander au graphiste — Landing L'Atelier By

État des visuels du site et **liste de ce qui reste à produire**.
Aucune image n'est techniquement « cassée » : tout s'affiche. Mais de nombreux
visuels sont des **placeholders réutilisés** (une même image sert à plusieurs
endroits) et doivent être remplacés par de vrais visuels dédiés.

**Style existant à respecter :** illustrations au trait (single-line), fond
transparent, cohérence entre toutes les pièces.

---

## 🎨 Récapitulatif par priorité

| Priorité | Élément | Quantité | État actuel |
|---|---|---|---|
| 🔴 Essentiel | Favicon | 1 jeu | ❌ absent |
| 🔴 Essentiel | Image de partage social (Open Graph) | 1 | ❌ absent |
| 🔴 Essentiel | Illustrations des boutiques | 8 | ⚠️ placeholders (images produits réutilisées) |
| 🟠 Recommandé | Visuels du carrousel héro | 4 | ⚠️ réutilisés depuis produits/saison |
| 🟠 Recommandé | Visuels des collaborations | 4 | ⚠️ génériques (marques non représentées) |
| 🟠 Recommandé | Icônes PWA + manifest (si app) | 1 jeu | ❌ absent |
| 🟢 Optionnel | Jeu d'icônes cohérent (services + UI) | ~18 | ✅ SVG au trait déjà en place |
| 🟢 Optionnel | Optimisation du poids des images | toutes | ⚠️ trop lourdes (500–900 Ko) |

---

## 1. 🔴 Favicon (absent)

Aucun favicon n'est défini. À fournir :

| Fichier | Taille | Usage |
|---|---|---|
| `favicon.ico` | 32×32 (multi-tailles) | onglet navigateur |
| `favicon-32.png` | 32×32 | navigateurs modernes |
| `favicon-16.png` | 16×16 | idem |
| `apple-touch-icon.png` | 180×180 | écran d'accueil iOS |

Idéalement une déclinaison simplifiée du logo (monogramme).

---

## 2. 🔴 Image de partage social — Open Graph (absent)

Quand un lien du site est partagé (WhatsApp, Facebook, LinkedIn…), aucune
vignette n'apparaît. À fournir :

| Fichier | Dimensions | Usage |
|---|---|---|
| `img/social/og-image.png` | **1200 × 630 px** | aperçu de lien (og:image / twitter:image) |

Contenu suggéré : logo + baseline sur fond de la marque. Une seule image
suffit pour tout le site.

---

## 3. 🔴 Illustrations des boutiques (8 — placeholders)

Les 8 boutiques réutilisent actuellement des images de produits. Chaque
boutique devrait avoir **son propre visuel** (illustration de devanture, ou
photo, selon la direction artistique).

| Boutique | Ville | Placeholder actuel | À livrer (suggestion) |
|---|---|---|---|
| Maison Châtelain | Bruxelles | `bread-1.png` | `img/shops/chatelain.png` |
| Atelier Sablon | Bruxelles | `cake.png` | `img/shops/sablon.png` |
| Le Carré | Liège | `croissant.png` | `img/shops/carre.png` |
| Zuid Bakery | Antwerpen | `roll.png` | `img/shops/zuid.png` |
| Patershol | Gent | `cookies.png` | `img/shops/patershol.png` |
| Le Grognon | Namur | `savoury-tart.png` | `img/shops/grognon.png` |
| Brugge Studio | Brugge | `sweet-tart-small.png` | `img/shops/brugge.png` |
| Leuven | Leuven | `sandwiches.png` | `img/shops/leuven.png` |

> Le chemin final se renseigne dans la colonne `image_path` de la table `lp_shops`.

---

## 4. 🟠 Visuels du carrousel héro (4 — réutilisés)

Les 4 slides du héro réutilisent des images produits/saison. Des visuels
dédiés (plus larges, plus « accroche ») seraient préférables.

| Slide | Thème | Placeholder actuel | À livrer (suggestion) |
|---|---|---|---|
| 1 | Fait main chaque matin | `bread-1.png` | `img/hero/hero-1.png` |
| 2 | 4 achetées, 5ᵉ offerte | `cake.png` | `img/hero/hero-2.png` |
| 3 | Produits de saison | `epiphany.png` | `img/hero/hero-3.png` |
| 4 | Brioche myrtille (été) | `brioche-croustillante.png` | `img/hero/hero-4.png` |

> Chemins à renseigner dans `lp_hero_slides.image_path`.

---

## 5. 🟠 Visuels des collaborations (4 — génériques)

Les collaborations sont co-signées avec des marques nommées, mais les visuels
sont génériques. Idéalement, un visuel évoquant chaque partenaire.

| Collaboration | Placeholder actuel | À livrer (suggestion) |
|---|---|---|
| Pain au chocolat × Darcis | `roll.png` | `img/collabs/darcis.png` |
| Tarte chocolat × Marcolini | `sweet-tart-small.png` | `img/collabs/marcolini.png` |
| Trois fèves × Benoît Nihant | `cookies.png` | `img/collabs/nihant.png` |
| Entremets × La Maison | `cake-slice.png` | `img/collabs/lamaison.png` |

> Chemins à renseigner dans `lp_collaborations.image_path`.

---

## 6. 🟢 Produits & saison (globalement OK)

- **Familles de produits (5)** : `bread-1, croissant, cake, savoury-tart, sandwiches` — ce sont les illustrations principales. À conserver ou affiner selon la DA.
- **Saison (4)** : `brioche-croustillante, spring, epiphany, saint-nicholas` — dédiées, OK.
- ⚠️ **`img/availability/summer.png`** existe mais **n'est utilisé nulle part** (orphelin). À affecter à un produit d'été dans `lp_seasonal_items`, ou à supprimer.

Manque éventuel : si de nouveaux produits saisonniers sont ajoutés (Pâques,
Halloween, Saint-Valentin…), prévoir une illustration par édition.

---

## 7. 🟠 Icônes PWA + manifest (si application/PWA)

La bannière mobile et le QR code pointent vers une PWA (`lp_params.app_url`).
Si une PWA est prévue, il faut :

| Fichier | Taille | Usage |
|---|---|---|
| `img/pwa/icon-192.png` | 192×192 | icône app Android |
| `img/pwa/icon-512.png` | 512×512 | splash / stores |
| `img/pwa/icon-maskable.png` | 512×512 (zone safe) | icône adaptative Android |
| `manifest.webmanifest` | — | à générer (peut être fait côté dev) |

---

## 8. 🟢 Icônes d'interface (déjà en place, optionnel)

18 icônes SVG au trait sont **déjà intégrées** dans le code (services, épingle,
téléphone, e-mail, fermeture, chevrons…). Elles fonctionnent. Un graphiste peut
fournir un **jeu cohérent** si l'on veut homogénéiser le style, notamment :

- **6 icônes services** : Click & Collect, Livraison, Commande téléphonique, B2B, Boutique en ligne, Programme fidélité.
- Icônes de navigation spéciale (ex. 👑 pour la Galette) — actuellement emoji ou SVG, personnalisables via `lp_nav_items.icon`.

> Format : SVG au trait, `viewBox="0 0 24 24"`, sans remplissage (stroke uniquement).

---

## 9. 🟢 Optimisation du poids (recommandé)

Les images actuelles sont **lourdes** (500 Ko à 900 Ko chacune), ce qui ralentit
le chargement mobile. Demander au graphiste de livrer des versions **optimisées** :

- Cible : **< 150 Ko** par illustration.
- Format : **PNG** (fond transparent) ou **WebP** (plus léger).
- Dimensions d'affichage : environ **800 × 800 px** suffisent (carré).

---

## 📐 Spécifications techniques (à transmettre au graphiste)

| Paramètre | Valeur |
|---|---|
| Style | Illustration au trait (single-line), cohérent avec l'existant |
| Fond | Transparent |
| Format | PNG (ou WebP) ; SVG pour les icônes |
| Dimensions produits/boutiques/collabs | ~800 × 800 px (carré) |
| Dimensions héro | ~1000 × 1000 px |
| Image sociale | 1200 × 630 px |
| Poids cible | < 150 Ko par image |
| Nommage | minuscules, sans accents, tirets (`le-grognon.png`) |

**Arborescence de livraison suggérée :**

```
img/
├── brand/      logo, favicon, apple-touch-icon
├── social/     og-image.png
├── hero/       hero-1…4.png
├── products/   (familles — existant)
├── shops/      chatelain, sablon, carre, zuid, patershol, grognon, brugge, leuven
├── collabs/    darcis, marcolini, nihant, lamaison
├── availability/ spring, summer, epiphany, saint-nicholas (existant)
└── pwa/        icon-192, icon-512, icon-maskable
```

Une fois les fichiers livrés, il suffit de les déposer sous `img/…` et de
renseigner les chemins dans les colonnes `image_path` des tables concernées
(voir `DOCUMENTATION.md`). Aucune modification de code n'est nécessaire.
