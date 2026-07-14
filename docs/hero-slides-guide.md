# Hero Carousel — Guide des illustrations

## Format recommandé

| Propriété | Valeur |
|---|---|
| **Format** | PNG avec fond transparent |
| **Dimensions** | 800 × 800 px minimum |
| **Ratio** | Carré (1:1) ou portrait (3:4) |
| **Poids max** | 300 KB par image |
| **Résolution** | 144 dpi (retina) |
| **Mode couleur** | RVB (pas CMJN) |

---

## Style visuel à respecter

Le hero utilise des **illustrations au trait** (line art), pas des photos réalistes.

✅ À faire :
- Fond **transparent** (PNG)
- Trait fin, épuré — même style que les illustrations existantes (`croissant.png`, `bread-1.png`)
- Palette neutre : noir/encre (`#232220`) ou couleur accent (`#8D1D2C`)
- Sujet centré avec espace blanc autour (le CSS gère le cadrage)

❌ À éviter :
- Photos réalistes avec fond plein
- Illustrations trop chargées / colorées
- Logos ou texte dans l'image (le texte vient de la DB)
- Fond blanc (invisible sur le fond crème `#FCFBF8` du site)

---

## Dimensions selon l'usage

```
Desktop (> 900px) : illustration affichée à ~420 × 420 px
Tablette (600-900px): ~320 × 320 px  
Mobile (< 600px)  : ~260 × 260 px
```

→ Exportez en **800 × 800 px** : le CSS redimensionne automatiquement.

---

## Optimisation avant upload

1. Exporter en PNG depuis Illustrator / Figma / Photoshop
2. Compresser avec [TinyPNG](https://tinypng.com) ou [Squoosh](https://squoosh.app)
3. Viser **< 200 KB** après compression
4. Nommer le fichier en minuscules, sans espaces ni accents :
   - ✅ `galette-des-rois.png`
   - ❌ `Galette Des Rois (final).png`

---

## Chemin de dépôt sur le serveur

Uploadez les images dans :
```
/var/www/html/img/products/
```

Dans PHPMyAdmin, mettez à jour la colonne `image_path` de la table `lp_hero_slides` :
```
/img/products/nom-du-fichier.png
```

---

## Exemple de slide complet dans la DB

```sql
UPDATE lp_hero_slides SET
  image_path   = '/img/products/galette-des-rois.png',
  eyebrow_fr   = 'Édition limitée · Janvier',
  eyebrow_nl   = 'Beperkte editie · Januari',
  title_fr     = 'Galette des Rois <span class="script">artisanale.</span>',
  title_nl     = 'Ambachtelijke <span class="script">Driekoningentaart.</span>',
  lede_fr      = 'Frangipane à l\'ancienne, fève signée. Disponible jusqu\'à l\'Épiphanie.',
  lede_nl      = 'Ouderwetse frangipane, gesigneerde boon. Beschikbaar tot Driekoningen.',
  cta1_text_fr = 'Commander',
  cta1_text_nl = 'Bestellen',
  cta1_url     = '#boutiques',
  cta2_text_fr = 'Voir les éditions →',
  cta2_text_nl = 'Bekijk de edities →',
  cta2_url     = '#saison',
  is_active    = 1
WHERE position = 0;
```

---

## Balise HTML autorisée dans les titres

Le champ `title_fr` / `title_nl` accepte uniquement cette balise :

```html
<span class="script">texte en cursive</span>
```

Elle applique la police Daniel (cursive accent) en couleur rouge rubis.  
N'utilisez pas d'autres balises HTML dans ce champ.

---

## Ordre des slides

La colonne `position` (0, 1, 2, 3) contrôle l'ordre d'affichage.  
Le slide `position = 0` est affiché en premier.  
Maximum **4 slides actifs** (`is_active = 1`).
