# ERP ↔ Landing Integration Guide

How the **ERP backend** and the **public landing page** exchange data.
Both share the same MySQL database (`atelierby_db`) on the same host.

> French content documentation lives in `DOCUMENTATION.md`. This file is the
> technical integration contract, intended for ERP/backend developers.

---

## 1. Overview

```
                 ┌───────────────────────── shared MySQL: atelierby_db ─────────────────────────┐
                 │                                                                               │
   ┌─────────┐   │   CONTENT tables (lp_hero_slides, lp_shops, lp_i18n, …)   ┌──────────────┐    │
   │   ERP   │──▶│   ────────────────────────────────────────────────────▶  │  lp_api.php   │───▶ Landing (read)
   │ backend │   │        writes content                    reads (SELECT)   └──────────────┘    │
   │         │◀──│   LEAD/LOG tables (lp_candidates, lp_mail_log)  ◀────────  lp_lead.php   ◀──── Landing form (write)
   └─────────┘   │        reads leads                        writes (INSERT)                      │
                 └───────────────────────────────────────────────────────────────────────────────┘
```

Two independent data flows:

| Flow | Direction | Mechanism | Tables |
|---|---|---|---|
| **Content** | ERP → Landing | ERP writes tables, landing reads them through `lp_api.php` | all `lp_*` content tables |
| **Leads** | Landing → ERP | Landing writes via `lp_lead.php`, ERP reads the rows | `lp_candidates`, `lp_mail_log` |

The landing **never writes** to content tables and the ERP **never needs** the HTTP API — it reads/writes the shared DB directly.

---

## 2. Landing → ERP : lead capture (the only write path)

### 2.1 The form

| | |
|---|---|
| Page | `franchise-lead.html` (franchise application) |
| Method | `POST` |
| Endpoint | `/landing/lp_lead.php` |
| Content-Type | `application/json` |
| Response | `application/json` |

This is the **only** form that writes to the database. All other buttons are navigation links.

### 2.2 Request payload

```json
{
  "first_name": "Jean",
  "last_name":  "Dupont",
  "email":      "jean.dupont@example.be",
  "phone":      "+32 470 12 34 56",
  "area":       "namur",
  "message":    "",
  "lang":       "fr"
}
```

| Field | Required | Max | Notes |
|---|---|---|---|
| `first_name` | ✅ | 255 | stripped of tags |
| `last_name` | ✅ | 255 | stripped of tags |
| `email` | ✅ | — | must pass `FILTER_VALIDATE_EMAIL` |
| `phone` | ❌ | 40 | |
| `area` | ❌ | 120 | zone value from `lp_franchise_zones.value` |
| `message` | ❌ | 1000 | |
| `lang` | ❌ | — | `fr` or `nl` (default `fr`) — selects the e-mail language |

### 2.3 Responses

| HTTP | Body | Meaning |
|---|---|---|
| `200` | `{"ok": true, "id": 42}` | Lead stored (`id` = `lp_candidates.id`) |
| `204` | — | CORS pre-flight (`OPTIONS`) |
| `400` | `{"error":"invalid_json"}` | Malformed body |
| `405` | `{"error":"method_not_allowed"}` | Not a POST |
| `422` | `{"error":"missing_required_fields"}` | Missing first/last/email |
| `500` | `{"error":"insert_failed"}` | DB insert failed |
| `503` | `{"error":"db_unavailable"}` | DB connection failed |

### 2.4 What `lp_lead.php` does server-side

1. Validates and sanitises the payload.
2. `INSERT INTO lp_candidates (first_name, last_name, email, phone, area, message, lang, ip, user_agent)`.
3. Sends **two e-mails** (see §5): an internal notification and a candidate confirmation.
4. Logs each e-mail attempt into `lp_mail_log`.
5. Returns `{ok:true, id}`.

### 2.5 CORS

Only these origins are accepted (`lp_lead.php` and `lp_api.php`):

```
https://latelierby.be
https://www.latelierby.be
http://185.180.206.46
http://localhost
```

Add the production domain here when the site goes live on a new host.

---

## 3. `lp_candidates` — the table the ERP consumes

Rows created by the landing form; the ERP reads them.

| Column | Type | Source |
|---|---|---|
| `id` | INT PK | auto |
| `first_name` | VARCHAR | form |
| `last_name` | VARCHAR | form |
| `email` | VARCHAR | form |
| `phone` | VARCHAR | form |
| `area` | VARCHAR | form (zone value) |
| `message` | TEXT | form |
| `lang` | ENUM(`fr`,`nl`) | form |
| `ip` | VARCHAR | server (`REMOTE_ADDR`) |
| `user_agent` | VARCHAR | server |
| `created_at` | TIMESTAMP | auto |

**Sample ERP query — new leads since yesterday:**

```sql
SELECT id, first_name, last_name, email, phone, area, lang, created_at
FROM lp_candidates
WHERE created_at >= NOW() - INTERVAL 1 DAY
ORDER BY created_at DESC;
```

The ERP can add its own columns (e.g. `status`, `assigned_to`, `synced_at`) — the landing insert only sets the columns above, so extra nullable columns are safe.

---

## 4. ERP → Landing : content management (read path)

The ERP is the **source of truth for content**. It writes directly to the `lp_*`
content tables; the landing exposes them read-only through `lp_api.php`.

### 4.1 Read API

`GET /landing/lp_api.php?r=all` → single JSON payload with 14 keys.
Individual routes: `hero, seasonal, collabs, franchise, shops, pickers, families, sections, services, app, nav, footer, i18n, params`.
Dedicated pages: `?r=franchise_page`, `?r=legal`.

Response header: `Cache-Control: public, max-age=300` (5-minute browser cache).

### 4.2 Read queries the landing runs

Every content query filters on activity/order. The ERP must respect these conventions when writing:

| Table | Landing SELECT (simplified) |
|---|---|
| `lp_hero_slides` | `WHERE is_active=1 ORDER BY position` |
| `lp_product_families` | `WHERE is_active=1 ORDER BY position` |
| `lp_collaborations` | `WHERE is_active=1 ORDER BY position` |
| `lp_seasonal_items` | `WHERE is_active=1 ORDER BY position` |
| `lp_services` | `WHERE is_active=1 ORDER BY position` |
| `lp_shops` | `WHERE is_active=1 ORDER BY sort_order` |
| picker (`lp_shops`) | `WHERE webshop_active=1 ORDER BY sort_order` |
| `lp_nav_items` | `WHERE is_active=1 ORDER BY position` |
| `lp_footer_links` | `WHERE is_active=1 ORDER BY col, position` |
| `lp_sections` | all rows, keyed by `section_key` |
| `lp_i18n` | all rows → `{fr:{…}, nl:{…}}` |
| `lp_params` | all rows → key/value map |
| `lp_franchise_section` | `LIMIT 1` (single row) |
| `lp_app_section` | `LIMIT 1` (single row) + `lp_params.app_url` |
| `lp_legal` | `LIMIT 1` (single row) |

**Writing conventions for the ERP:**
- `is_active = 0` hides a row from the site (soft delete).
- `position` / `sort_order` controls display order.
- `_fr` / `_nl` columns are the FR/NL variants; keep both populated.
- `image_path` is a path **relative to the landing root**, e.g. `img/products/bread-1.png`. Upload the file under `/var/www/latelierby-landing/img/…`.
- Singleton tables (`lp_franchise_section`, `lp_app_section`, `lp_legal`) must keep exactly **one** row (the landing uses `LIMIT 1`).
- Keep exactly **4 active** rows in `lp_hero_slides` (carousel is calibrated for 4).

### 4.3 Full table map

See `DOCUMENTATION.md` §2–§3 for the column-by-column description of every table.

---

## 5. E-mail pipeline (configurable by the ERP)

On each lead, `lp_lead.php` sends two messages using settings from **`lp_mail_params`**:

| Column | Role |
|---|---|
| `from_email`, `from_name` | Sender identity (must be a verified sender in Brevo) |
| `notify_email`, `notify_name` | Internal recipient (the team) |
| `notify_subject_fr/nl` | Subject of the internal notification |
| `confirm_subject_fr/nl`, `confirm_intro_fr/nl` | Subject/body of the candidate confirmation |
| `brevo_api_key` | Brevo Transactional API key (HTTP) — **preferred** |
| `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`, `smtp_secure` | SMTP fallback |

**Sending priority:** Brevo API (HTTP) → SMTP → PHP `mail()`.
The ERP can rotate the sender, recipient, key or templates by updating this single row.

Every attempt is journalled in **`lp_mail_log`** (`candidate_id, type, to_email, subject, status, error_msg, sent_at`) — useful for the ERP to audit deliverability.

---

## 6. Ownership summary

| Table(s) | Written by | Read by |
|---|---|---|
| `lp_hero_slides`, `lp_product_families`, `lp_collaborations`, `lp_seasonal_items`, `lp_services`, `lp_shops` (+ `lp_shop_hours`, `lp_shop_services`), `lp_sections`, `lp_nav_items`, `lp_footer_links`, `lp_franchise_section`, `lp_app_section`, `lp_i18n`, `lp_params`, `lp_legal`, `lp_franchise_i18n`, `lp_franchise_zones` | **ERP** (content) | Landing (`lp_api.php`) |
| `lp_candidates` | Landing (`lp_lead.php`) | **ERP** (CRM) |
| `lp_mail_log` | Landing (`lp_lead.php`) | ERP (audit) |
| `lp_mail_params` | ERP / admin | Landing (`lp_lead.php`) |

---

## 7. Integration checklist for the ERP

- [ ] Read new leads from `lp_candidates` (poll on `created_at`, or add a `synced_at` column).
- [ ] Manage content by writing the `lp_*` content tables directly (respect `is_active`, `position`, FR/NL).
- [ ] Upload images to `/var/www/latelierby-landing/img/…` and store the relative path in `image_path`.
- [ ] Keep singleton tables to one row; keep 4 active hero slides.
- [ ] Configure e-mail via `lp_mail_params` (Brevo key + verified sender).
- [ ] Add the production domain to the CORS allow-lists in `lp_api.php` and `lp_lead.php`.
- [ ] Remember the landing API is cached 5 min (`Cache-Control: max-age=300`).

---

## 8. Security notes

- DB credentials are currently hard-coded in each `lp_*.php`. Keep the repository private; ideally move them to an untracked config include shared by the ERP and the landing.
- `lp_candidates` stores personal data (name, e-mail, phone, IP) — apply the retention policy declared in `lp_legal.data_retention` (GDPR).
- The write endpoint (`lp_lead.php`) has no CAPTCHA/rate-limit; add one at the ERP or reverse-proxy level if spam becomes an issue.
