# 09 — Programmatic SEO Strategy

**Project:** crmoffice
**Governing principle:** [pSEO is a default, not optional — Global Rule](../../CLAUDE.md)
**Reference implementation:** `D:\project laravel\whitelabel\whitelabel\app\Http\Controllers\ProgrammaticSeoController.php`
**Last updated:** 2026-05-30

> **Implementation note (Phase 1–6):** 140+ pSEO routes live via ProgrammaticSeoController with dynamic sitemap.xml and robots.txt — covering best-CRM, alternatives-to, compare/vs, and industry-specific landing pages.

> **Why for a CRM?** Even though crmoffice's core product sits behind login, the *public marketing surface* is where acquisition happens. pSEO routes turn "Best CRM for X" / "Alternative to Y" / "Compare A vs B" searches into landing pages. Compounding traffic = lower CAC = stronger moat.

---

## 1. Goals

- 1,000+ indexed pages within 60 days of launch
- 5,000+ unique organic visits/month by month 6
- Each page provides **genuine value** — never thin/scraped content
- Position for: agency owners, freelancers, SMB owners searching CRM solutions

---

## 2. Route Catalog

### 2.1 "Best CRM for X" Pages

```
/best-crm-for-agencies
/best-crm-for-freelancers
/best-crm-for-real-estate
/best-crm-for-msps
/best-crm-for-consultants
/best-crm-for-law-firms
/best-crm-for-accountants
/best-crm-for-marketing-agencies
/best-crm-for-design-studios
/best-crm-for-it-services
/best-crm-for-startups
/best-crm-for-nonprofits
/best-crm-for-construction
/best-crm-for-event-planners
/best-crm-for-fitness-coaches
/best-crm-for-photographers
```

**Year-stamped variants** (auto-generated, freshness signal):
```
/best-crm-for-agencies-2026
/best-crm-for-agencies-2027  (auto-publish at year transition)
```

### 2.2 "Alternatives to Competitor" Pages

```
/alternatives-to-perfex
/alternatives-to-freshsales
/alternatives-to-hubspot
/alternatives-to-zoho-crm
/alternatives-to-pipedrive
/alternatives-to-monday-com
/alternatives-to-bonsai
/alternatives-to-honeybook
/alternatives-to-dubsado
/alternatives-to-suitedash
/alternatives-to-bitrix24
/alternatives-to-vtiger
/alternatives-to-espocrm
/alternatives-to-flowlu
/alternatives-to-agile-crm
/alternatives-to-noco-crm
/alternatives-to-streak
/alternatives-to-copper
/alternatives-to-insightly
/alternatives-to-salesforce-essentials
```

### 2.3 "Compare A vs B" Pages

```
/compare/crmoffice-vs-perfex
/compare/crmoffice-vs-freshsales
/compare/crmoffice-vs-hubspot
/compare/crmoffice-vs-zoho-crm
/compare/crmoffice-vs-pipedrive
/compare/crmoffice-vs-monday-com
/compare/crmoffice-vs-bonsai
/compare/crmoffice-vs-honeybook
/compare/crmoffice-vs-bitrix24
/compare/crmoffice-vs-vtiger
/compare/crmoffice-vs-espocrm
/compare/perfex-vs-freshsales
/compare/perfex-vs-zoho-crm
/compare/perfex-vs-bitrix24
/compare/freshsales-vs-hubspot
/compare/hubspot-vs-zoho-crm
/compare/zoho-crm-vs-pipedrive
... (combinatorial subset based on search volume)
```

### 2.4 "CRM for {Country/Region}" Pages

Useful where local payment/tax/lang features matter:
```
/crm-for-indonesia
/crm-for-singapore
/crm-for-malaysia
/crm-for-philippines
/crm-for-vietnam
/crm-for-thailand
/crm-for-india
/crm-for-australia
```

### 2.5 Feature-Centric Pages

```
/recurring-invoice-software
/proposal-software-for-agencies
/time-tracking-with-invoicing
/client-portal-software
/helpdesk-for-agencies
/contract-management-for-small-business
/self-hosted-crm
/open-source-crm-alternative
```

### 2.6 KB-Driven (Auto from data)

Every published `kb_articles` row → public URL `/kb/{category-slug}/{article-slug}`. All indexed in sitemap.

---

## 3. Required Per-Page Anatomy (MANDATORY per global rule)

Each pSEO page MUST have **all** of:

### 3.1 Meta Tags (Complete Block)

```html
<title>{{ $page->title }} — crmoffice</title>
<meta name="description" content="{{ $page->description }}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ $page->canonical_url }}">

<meta property="og:type" content="website">
<meta property="og:url" content="{{ $page->canonical_url }}">
<meta property="og:title" content="{{ $page->title }}">
<meta property="og:description" content="{{ $page->description }}">
<meta property="og:image" content="{{ $page->og_image }}">
<meta property="og:site_name" content="crmoffice">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $page->title }}">
<meta name="twitter:description" content="{{ $page->description }}">
<meta name="twitter:image" content="{{ $page->og_image }}">

<meta name="author" content="crmoffice">
```

### 3.2 JSON-LD Structured Data

Type per route:

| Route Type | Schema |
|---|---|
| `best-crm-for-{x}` | `ItemList` (ranked items: crmoffice + 4 alternatives) + `FAQPage` |
| `alternatives-to-{competitor}` | `ItemList` + `FAQPage` |
| `compare/{a}-vs-{b}` | `Product` (crmoffice) + `FAQPage` with diff Q&A |
| `crm-for-{country}` | `Service` + `LocalBusiness` (if applicable) + `FAQPage` |
| `kb/{cat}/{article}` | `Article` + `BreadcrumbList` |
| `feature-centric` | `SoftwareApplication` + `AggregateRating` + `FAQPage` |

Example ItemList for `best-crm-for-agencies`:
```json
{
  "@context": "https://schema.org",
  "@type": "ItemList",
  "name": "Best CRM for Agencies 2026",
  "itemListElement": [
    { "@type": "ListItem", "position": 1, "item": { "@type": "SoftwareApplication", "name": "crmoffice", "url": "..." } },
    { "@type": "ListItem", "position": 2, "item": { "@type": "SoftwareApplication", "name": "Perfex CRM", "url": "..." } },
    ...
  ]
}
```

### 3.3 Content (≥ 300 words of genuine value)

Each page generated from **data + templates** with multiple sections:

#### `best-crm-for-{industry}` page structure:
1. **Hero** (H1) — "Best CRM for {Industry} in 2026"
2. **Intro** (1 paragraph) — why CRM matters for this industry, top pain points
3. **Top 5 picks** ranked list with mini-table (price, key features, pros/cons)
4. **Detailed review** of crmoffice + 4 competitors (1 paragraph each)
5. **Comparison table** (auto-generated from `competitor_facts` JSON data file)
6. **FAQ** (auto from `pseo_faq_templates` filtered by industry tag)
7. **Conclusion + CTA** (try crmoffice, see KB, contact sales)
8. **Internal links** to 3 related pSEO pages

#### `compare/{a}-vs-{b}` page structure:
1. **Hero (H1)** — "{A} vs {B}: Honest Comparison 2026"
2. **TL;DR** (1 paragraph)
3. **Side-by-side feature table** — 20+ rows (pricing, deployment, features, integrations)
4. **Pricing breakdown** (3-year TCO calculation)
5. **Who should pick {A}** vs **Who should pick {B}**
6. **Switching guide** (migration steps if from B to A)
7. **FAQ**
8. **CTA**

#### `kb-for-{country}` page structure:
1. **Hero (H1)** — "Best CRM for {Country} Businesses"
2. **Local-specific requirements** — PPN/GST, e-Faktur, local payment gateways, local language support
3. **Feature checklist** — what crmoffice does for that locale
4. **Top alternatives in that country**
5. **Compliance section**
6. **FAQ**
7. **CTA**

### 3.4 Sitemap Inclusion (Dynamic)

`sitemap.xml` generated by scheduled job:
- Static pages (home, features, pricing)
- All pSEO routes (enumerated from `config/pseo.php` catalog)
- All published `kb_articles`
- All `kb_categories`
- All public proposal/estimate/contract links → **EXCLUDED** (have public_token, not for indexing)

Hourly rebuild → cached → served. Submit URL: `https://crmoffice.app/sitemap.xml`.

### 3.5 robots.txt (Allows pSEO crawl)

```
User-agent: *
Allow: /
Allow: /best-crm-for-*
Allow: /alternatives-to-*
Allow: /compare/*
Allow: /crm-for-*
Allow: /kb/*
Disallow: /admin/
Disallow: /portal/
Disallow: /api/
Disallow: /public/proposals/
Disallow: /public/estimates/
Disallow: /public/invoices/
Disallow: /public/contracts/
Disallow: /public/surveys/

Sitemap: https://crmoffice.app/sitemap.xml
```

### 3.6 README Mention

Project README includes step-by-step Google Search Console submission instructions.

---

## 4. Implementation Plan

### 4.1 Data Layer

#### `config/pseo.php`
```php
return [
    'industries' => [
        'agencies' => ['display' => 'Agencies', 'icon' => 'building', 'pain_points' => [...], 'features' => [...]],
        'freelancers' => [...],
        ...
    ],
    'competitors' => [
        'perfex' => ['display' => 'Perfex CRM', 'website' => '...', 'pricing_model' => 'one-time license', 'pros' => [...], 'cons' => [...], 'feature_matrix' => [...]],
        'freshsales' => [...],
        ...
    ],
    'countries' => [
        'indonesia' => ['display' => 'Indonesia', 'currency' => 'IDR', 'tax' => 'PPN 11%', 'payment_gateways' => ['Midtrans','Xendit','Doku','Faspay'], 'language' => 'id', 'compliance' => ['e-Faktur', 'UU PDP']],
        ...
    ],
    'features' => [
        'recurring-invoice' => [...],
        'client-portal' => [...],
        ...
    ],
];
```

#### `storage/app/pseo/competitor-facts/{slug}.json`
Per-competitor JSON with detailed feature matrix — keeps `config/pseo.php` slim. Owner can edit without code change.

#### Database (optional Phase 3)
`pseo_pages` table for **owner-editable content overrides** (so non-dev can polish specific pages).

### 4.2 Controllers (Single class with route-method mapping)

`app/Http/Controllers/Public/ProgrammaticSeoController.php`:

```php
public function bestCrmFor(string $industry, ?int $year = null): View
public function alternativesTo(string $competitor): View
public function compare(string $a, string $b): View
public function crmFor(string $country): View
public function featureCentric(string $feature): View
```

Each method:
1. Validate slug exists in catalog → 404 if not
2. Load data (config + JSON files + DB if Phase 3)
3. Render Inertia page with structured props
4. Add Vary headers, cache 24h via response macro

### 4.3 Vue Page Templates

`resources/js/Pages/Public/Pseo/`:
- `BestCrmFor.vue`
- `AlternativesTo.vue`
- `Compare.vue`
- `CrmFor.vue`
- `FeatureCentric.vue`

Shared partials: `<HeroSection>`, `<FeatureTable>`, `<FaqSection>`, `<RelatedLinks>`, `<JsonLd>`.

### 4.4 Routing (`routes/public.php`)

```php
Route::get('/best-crm-for-{industry}', [ProgrammaticSeoController::class, 'bestCrmFor'])->where('industry', '[a-z0-9-]+');
Route::get('/best-crm-for-{industry}-{year}', [ProgrammaticSeoController::class, 'bestCrmFor'])->where(['industry' => '[a-z0-9-]+', 'year' => '20[0-9]{2}']);
Route::get('/alternatives-to-{competitor}', [ProgrammaticSeoController::class, 'alternativesTo'])->where('competitor', '[a-z0-9-]+');
Route::get('/compare/{a}-vs-{b}', [ProgrammaticSeoController::class, 'compare'])->where(['a' => '[a-z0-9-]+', 'b' => '[a-z0-9-]+']);
Route::get('/crm-for-{country}', [ProgrammaticSeoController::class, 'crmFor'])->where('country', '[a-z-]+');
Route::get('/{feature}', [ProgrammaticSeoController::class, 'featureCentric'])
    ->where('feature', '(recurring-invoice-software|proposal-software-for-agencies|time-tracking-with-invoicing|client-portal-software|helpdesk-for-agencies|contract-management-for-small-business|self-hosted-crm|open-source-crm-alternative)');
```

### 4.5 Caching

- Each rendered HTML cached 24h via Redis (key: full URL)
- Cache invalidated on:
  - Pricing/features content edit (admin UI in Phase 3)
  - Competitor JSON file change (file watcher → cache flush in dev; manual button in prod)
- Sitemap cached 1h, scheduled rebuild

### 4.6 Generation Counts at Launch

| Route Pattern | # Pages |
|---|---|
| `best-crm-for-{industry}` × (current year + next year) | 16 industries × 2 years = **32** |
| `alternatives-to-{competitor}` | **20** |
| `compare/{a}-vs-{b}` (initial subset) | **30** |
| `crm-for-{country}` | **8** |
| Feature-centric | **8** |
| KB articles (assumed at launch) | **30+** |
| KB categories | **8** |
| Static (home, features, pricing, etc.) | **6** |

**Total at launch: ~140+ pages.** Grows with KB additions and quarterly competitor table updates.

---

## 5. Performance Requirements

- Each pSEO page TTFB < 200ms (Redis cache hit)
- Core Web Vitals all green (LCP < 2.5s, INP < 200ms, CLS < 0.1)
- HTML stream-able (no SPA hydration delay for content)
- Critical CSS inlined; rest async
- Images lazy-loaded, WebP, dimensions set
- No client-side JS required to read content

**Implementation:** Inertia SSR is overkill if Filament admin doesn't need it. Decision: use **server-rendered Blade for pSEO pages specifically** (not Inertia) — gives best SEO + zero hydration cost. Customer portal stays on Inertia/Vue. Marketing pages → Blade + Tailwind + minimal Alpine for accordion/tabs.

**Update from §05 modules:** Adjusted public marketing surface to use Blade-only for pSEO routes (better TTFB, no JS needed). Customer portal keeps Inertia/Vue 3 as planned.

---

## 6. Quality Gates (Anti-Thin-Content)

Each pSEO page MUST pass before deploy:
- [ ] ≥ 300 words of unique body content (counted excluding nav/footer)
- [ ] Original H1 (not duplicated across pages)
- [ ] Original meta description (not duplicated)
- [ ] At least 3 internal links to related pages
- [ ] At least 1 H2 section beyond hero
- [ ] FAQ has ≥ 4 Q&A pairs
- [ ] Comparison table has ≥ 10 rows (where applicable)
- [ ] JSON-LD validates (test via Google's Rich Results Test)
- [ ] No `Lorem ipsum`, no placeholder text

Automated check: `php artisan pseo:audit` walks all routes, validates word count + meta + JSON-LD shape, fails CI if any page is thin.

---

## 7. Optional Boosters (Phase 3+)

- **User-generated comparisons** — `/compare/perfex-vs-zoho-crm` allows submitted reviews
- **Embed widgets** — "Calculate CRM TCO" interactive form
- **AI-assisted refresh** — quarterly LLM-driven content update for competitor sections (still owner-reviewed before publish)
- **Hreflang** for multi-language pSEO (id, en, ms, vi, th, tl variants of country pages)

---

## 8. Tracking & Iteration

- **Plausible / GA4** (owner-configurable provider) tracks pageviews per route
- Admin dashboard: top pSEO pages by traffic
- Conversion: from pSEO landing → trial signup
- Monthly review: prune low-performing routes (combine, redirect, or delete with 301)

---

## 9. Compliance

- All competitor claims must be **verifiable and fair**
- Comparison tables source dates clearly noted (e.g., "as of May 2026")
- No trademark misuse — "Perfex CRM" used factually as comparison, not falsely affiliated
- If a competitor objects, route to negotiation; never resort to misleading content
