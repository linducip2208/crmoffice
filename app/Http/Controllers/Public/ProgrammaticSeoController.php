<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class ProgrammaticSeoController extends Controller
{
    private const CACHE_TTL = 60 * 60 * 24; // 24h

    // ──────────────────────────────────────
    // Original methods (kept + enhanced)
    // ──────────────────────────────────────

    public function bestCrmFor(string $industry, ?int $year = null): Response
    {
        $catalog = config('pseo.industries');
        if (! isset($catalog[$industry])) {
            abort(404);
        }

        $year ??= (int) now()->year;
        $data = $catalog[$industry];

        $competitors = array_slice(config('pseo.competitors'), 0, 4, true);

        return response(view('public.pseo.best-crm-for', [
            'industry' => $industry,
            'data' => $data,
            'year' => $year,
            'competitors' => $competitors,
            'canonical' => url("/best-crm-for-$industry".($year !== (int) now()->year ? "-$year" : '')),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    public function alternativesTo(string $competitor): Response
    {
        $catalog = config('pseo.competitors');
        if (! isset($catalog[$competitor])) {
            abort(404);
        }

        $others = array_slice(array_filter($catalog, fn ($k) => $k !== $competitor, ARRAY_FILTER_USE_KEY), 0, 4, true);

        return response(view('public.pseo.alternatives-to', [
            'competitor' => $competitor,
            'data' => $catalog[$competitor],
            'others' => $others,
            'canonical' => url("/alternatives-to-$competitor"),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    public function compare(string $a, string $b): Response
    {
        $catalog = config('pseo.competitors');
        $catalog['crmoffice'] = [
            'display' => 'crmoffice',
            'tagline' => 'Modern self-hostable CRM',
            'pros' => ['Modern Laravel + Filament stack', 'Self-hostable, own your data', 'BYO integrations (no lock-in)', 'pSEO bundled'],
            'cons' => ['Newer product (less marketplace)'],
        ];

        if (! isset($catalog[$a]) || ! isset($catalog[$b])) {
            abort(404);
        }

        return response(view('public.pseo.compare', [
            'a' => $a, 'b' => $b,
            'aData' => $catalog[$a], 'bData' => $catalog[$b],
            'canonical' => url("/compare/$a-vs-$b"),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    public function crmFor(string $country): Response
    {
        $catalog = config('pseo.countries');
        if (! isset($catalog[$country])) {
            abort(404);
        }

        return response(view('public.pseo.crm-for-country', [
            'country' => $country,
            'data' => $catalog[$country],
            'canonical' => url("/crm-for-$country"),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    public function crmForIndustryInCity(string $industry, string $city): Response
    {
        $industries = config('pseo.industries');
        $cities = config('pseo.cities');

        if (! isset($industries[$industry]) || ! isset($cities[$city])) {
            abort(404);
        }

        return response(view('public.pseo.industry-in-city', [
            'industry' => $industry,
            'industryData' => $industries[$industry],
            'city' => $city,
            'cityName' => $cities[$city],
            'canonical' => url("/crm-for-$industry-in-$city"),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    public function crmWithFeature(string $feature): Response
    {
        $features = config('pseo.features');
        if (! isset($features[$feature])) {
            abort(404);
        }

        return response(view('public.pseo.crm-with-feature', [
            'feature' => $feature,
            'data' => $features[$feature],
            'canonical' => url("/crm-with-$feature"),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    public function roleCrm(string $role): Response
    {
        $roles = config('pseo.roles');
        if (! isset($roles[$role])) {
            abort(404);
        }

        return response(view('public.pseo.role-crm', [
            'role' => $role,
            'roleName' => $roles[$role],
            'canonical' => url("/$role-crm"),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    public function bestCrmUnderPrice(string $price): Response
    {
        $bands = config('pseo.pricing-bands');
        if (! isset($bands[$price])) {
            abort(404);
        }

        return response(view('public.pseo.crm-under-price', [
            'price' => (int) $price,
            'priceLabel' => '$'.$bands[$price],
            'competitors' => config('pseo.competitors'),
            'canonical' => url("/best-crm-under-$price"),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    // ──────────────────────────────────────
    // Industry × City multi-pattern (60K pages)
    // ──────────────────────────────────────

    public function softwareCrmIndustryCity(string $industry, string $city): Response
    {
        return $this->industryCityPage($industry, $city, 'software-crm', 'Software CRM');
    }

    public function aplikasiCrmIndustryCity(string $industry, string $city): Response
    {
        return $this->industryCityPage($industry, $city, 'aplikasi-crm', 'Aplikasi CRM');
    }

    public function sistemCrmIndustryCity(string $industry, string $city): Response
    {
        return $this->industryCityPage($industry, $city, 'sistem-crm', 'Sistem CRM');
    }

    private function industryCityPage(string $industry, string $city, string $prefix, string $labelPrefix): Response
    {
        $industries = config('pseo.industries');
        $cities = config('pseo.cities');

        if (! isset($industries[$industry]) || ! isset($cities[$city])) {
            abort(404);
        }

        return response(view('public.pseo.industry-in-city', [
            'industry' => $industry,
            'industryData' => $industries[$industry],
            'city' => $city,
            'cityName' => $cities[$city],
            'labelPrefix' => $labelPrefix,
            'canonical' => url("/$prefix-$industry-$city"),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    // ──────────────────────────────────────
    // Triple combo: industry × city × year (100K pages)
    // ──────────────────────────────────────

    public function bestCrmIndustryCityYear(string $industry, string $city, int $year): Response
    {
        $industries = config('pseo.industries');
        $cities = config('pseo.cities');

        if (! isset($industries[$industry]) || ! isset($cities[$city])) {
            abort(404);
        }

        if ($year < 2020 || $year > (int) now()->year + 1) {
            abort(404);
        }

        $data = $industries[$industry];
        $cityName = $cities[$city];
        $competitors = array_slice(config('pseo.competitors'), 0, 4, true);

        return response(view('public.pseo.best-industry-city-year', [
            'industry' => $industry,
            'data' => $data,
            'city' => $city,
            'cityName' => $cityName,
            'year' => $year,
            'competitors' => $competitors,
            'canonical' => url("/best-crm-for-$industry-in-$city-$year"),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    // ──────────────────────────────────────
    // Feature × Industry × City (1M pages via generic + this explicit route)
    // ──────────────────────────────────────

    public function crmFeatureIndustryCity(string $feature, string $industry, string $city): Response
    {
        $features = config('pseo.features');
        $industries = config('pseo.industries');
        $cities = config('pseo.cities');

        if (! isset($features[$feature]) || ! isset($industries[$industry]) || ! isset($cities[$city])) {
            abort(404);
        }

        return response(view('public.pseo.feature-industry-city', [
            'feature' => $feature,
            'featureData' => $features[$feature],
            'industry' => $industry,
            'industryData' => $industries[$industry],
            'city' => $city,
            'cityName' => $cities[$city],
            'canonical' => url("/crm-$feature-for-$industry-in-$city"),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    // ──────────────────────────────────────
    // Source Code Sales PSEO (300K pages)
    // ──────────────────────────────────────

    public function beliAplikasiCrm(): Response
    {
        $sc = config('pseo-source-code');

        return response(view('public.pseo.source-code-sales', [
            'title' => 'Beli Aplikasi CRM — Source Code Self-Hosted',
            'description' => 'Beli source code aplikasi CRM self-hosted. Full source code Laravel 13, one-time payment Rp 3.5jt, tidak ada biaya bulanan. WhatsApp 081296052010.',
            'heroTitle' => 'Beli Aplikasi CRM Self-Hosted',
            'heroSubtitle' => 'Source code full, one-time payment mulai Rp 3.500.000. Modifikasi sesuka hati, jual ulang ke client Anda. WhatsApp 081296052010.',
            'canonical' => url('/beli-aplikasi-crm'),
            'jsonld' => $sc['jsonld'],
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    public function sourceCodeCrmCity(string $city): Response
    {
        $cities = config('pseo.cities');
        if (! isset($cities[$city])) {
            abort(404);
        }

        $cityName = $cities[$city];
        $sc = config('pseo-source-code');

        return response(view('public.pseo.source-code-sales', [
            'title' => "Source Code CRM di {$cityName} — Beli Aplikasi CRM Self-Hosted",
            'description' => "Beli source code CRM di {$cityName}. Aplikasi CRM self-hosted Laravel 13, one-time payment, tidak ada biaya bulanan. Kirim ke seluruh Indonesia. WhatsApp 081296052010.",
            'heroTitle' => "Source Code CRM di {$cityName}",
            'heroSubtitle' => "Kirim source code CRM ke {$cityName} dan seluruh Indonesia. One-time payment, full source code, bebas modifikasi. Chat WhatsApp sekarang.",
            'canonical' => url("/source-code-crm-$city"),
            'jsonld' => array_merge($sc['jsonld'], [
                'name' => "crmoffice — Source Code CRM di {$cityName}",
                'description' => "Beli source code CRM self-hosted di {$cityName}. Laravel 13 + Filament 5. One-time payment.",
            ]),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    public function aplikasiCrmFeature(string $feature): Response
    {
        $features = config('pseo.features');
        if (! isset($features[$feature])) {
            abort(404);
        }

        $featureData = $features[$feature];
        $sc = config('pseo-source-code');

        return response(view('public.pseo.source-code-sales', [
            'title' => "Aplikasi CRM dengan {$featureData['display']} — Source Code Tersedia",
            'description' => "Beli source code aplikasi CRM dengan fitur {$featureData['display']}. {$featureData['solution']}. One-time payment, self-hosted. WhatsApp 081296052010.",
            'heroTitle' => "Aplikasi CRM dengan {$featureData['display']}",
            'heroSubtitle' => "{$featureData['pain']}? {$featureData['solution']}. Beli source code CRM sekarang, full source code, modifikasi sesuka hati.",
            'canonical' => url("/aplikasi-crm-$feature"),
            'jsonld' => array_merge($sc['jsonld'], [
                'name' => "crmoffice — Aplikasi CRM dengan {$featureData['display']}",
                'description' => "Source code CRM self-hosted dengan {$featureData['display']}. {$featureData['solution']}",
            ]),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    public function crmIndustrySourceCode(string $industry): Response
    {
        $industries = config('pseo.industries');
        if (! isset($industries[$industry])) {
            abort(404);
        }

        $industryData = $industries[$industry];
        $sc = config('pseo-source-code');

        return response(view('public.pseo.source-code-sales', [
            'title' => "CRM {$industryData['display']} Source Code — Beli Aplikasi CRM Self-Hosted",
            'description' => "Beli source code CRM untuk {$industryData['display']}. {$industryData['tagline']}. One-time payment, full source code Laravel 13. WhatsApp 081296052010.",
            'heroTitle' => "CRM {$industryData['display']} Source Code",
            'heroSubtitle' => "{$industryData['tagline']}. Beli source code CRM self-hosted, modifikasi sesuai kebutuhan {$industryData['display']} Anda.",
            'canonical' => url("/crm-$industry-source-code"),
            'jsonld' => array_merge($sc['jsonld'], [
                'name' => "crmoffice — CRM {$industryData['display']} Source Code",
                'description' => "Source code CRM self-hosted untuk {$industryData['display']}. {$industryData['tagline']}",
            ]),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    public function jualSourceCodeCrm(): Response
    {
        $sc = config('pseo-source-code');

        return response(view('public.pseo.source-code-sales', [
            'title' => 'Jual Source Code CRM — Aplikasi CRM Self-Hosted Siap Pakai',
            'description' => 'Jual source code CRM self-hosted. Laravel 13 + Filament 5, one-time payment, full source code. Jual ulang ke client Anda dengan brand sendiri. WhatsApp 081296052010.',
            'heroTitle' => 'Jual Source Code CRM Self-Hosted',
            'heroSubtitle' => 'Beli source code CRM, jual ulang ke client Anda dengan brand sendiri. Unlimited domain, lifetime update. Mulai Rp 15.000.000 (Whitelabel).',
            'canonical' => url('/jual-source-code-crm'),
            'jsonld' => $sc['jsonld'],
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    public function downloadSourceCodeCrm(): Response
    {
        $sc = config('pseo-source-code');

        return response(view('public.pseo.source-code-sales', [
            'title' => 'Download Source Code CRM — Aplikasi CRM Self-Hosted',
            'description' => 'Download source code CRM self-hosted. Full source code Laravel 13 + Filament 5, one-time payment. Instal di server sendiri. WhatsApp 081296052010.',
            'heroTitle' => 'Download Source Code CRM',
            'heroSubtitle' => 'Dapatkan source code CRM lengkap. Instal dalam 30 menit di server Anda sendiri. Full source code, bebas modifikasi.',
            'canonical' => url('/download-source-code-crm'),
            'jsonld' => $sc['jsonld'],
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    // ──────────────────────────────────────
    // Source Code × Industry × City combos
    // ──────────────────────────────────────

    public function sourceCodeCrmIndustryCity(string $industry, string $city): Response
    {
        $industries = config('pseo.industries');
        $cities = config('pseo.cities');

        if (! isset($industries[$industry]) || ! isset($cities[$city])) {
            abort(404);
        }

        $industryData = $industries[$industry];
        $cityName = $cities[$city];
        $sc = config('pseo-source-code');

        return response(view('public.pseo.source-code-sales', [
            'title' => "Source Code CRM {$industryData['display']} di {$cityName}",
            'description' => "Beli source code CRM untuk {$industryData['display']} di {$cityName}. {$industryData['tagline']}. One-time payment, full source code. WhatsApp 081296052010.",
            'heroTitle' => "Source Code CRM {$industryData['display']} di {$cityName}",
            'heroSubtitle' => "{$industryData['tagline']}. Kirim source code CRM ke {$cityName}. One-time payment, full source code Laravel 13 + Filament 5.",
            'canonical' => url("/source-code-crm-$industry-$city"),
            'jsonld' => array_merge($sc['jsonld'], [
                'name' => "crmoffice — Source Code CRM {$industryData['display']} di {$cityName}",
                'description' => "Source code CRM self-hosted untuk {$industryData['display']} di {$cityName}. {$industryData['tagline']}",
            ]),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    public function beliSourceCodeCrmIndustry(string $industry): Response
    {
        $industries = config('pseo.industries');
        if (! isset($industries[$industry])) {
            abort(404);
        }

        $industryData = $industries[$industry];
        $sc = config('pseo-source-code');

        return response(view('public.pseo.source-code-sales', [
            'title' => "Beli Source Code CRM {$industryData['display']} — Aplikasi Self-Hosted",
            'description' => "Beli source code CRM untuk {$industryData['display']}. {$industryData['tagline']}. One-time payment, full source code. WhatsApp 081296052010.",
            'heroTitle' => "Beli Source Code CRM {$industryData['display']}",
            'heroSubtitle' => "{$industryData['tagline']}. Dapatkan source code CRM lengkap, instal di server sendiri, modifikasi sesuka hati.",
            'canonical' => url("/beli-source-code-crm-$industry"),
            'jsonld' => array_merge($sc['jsonld'], [
                'name' => "crmoffice — Beli Source Code CRM {$industryData['display']}",
                'description' => "Beli source code CRM self-hosted untuk {$industryData['display']}. {$industryData['tagline']}",
            ]),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    public function jualAplikasiCrmIndustry(string $industry): Response
    {
        $industries = config('pseo.industries');
        if (! isset($industries[$industry])) {
            abort(404);
        }

        $industryData = $industries[$industry];
        $sc = config('pseo-source-code');

        return response(view('public.pseo.source-code-sales', [
            'title' => "Jual Aplikasi CRM {$industryData['display']} — Source Code Siap Pakai",
            'description' => "Jual aplikasi CRM {$industryData['display']} source code. {$industryData['tagline']}. One-time payment, jual ulang ke client. WhatsApp 081296052010.",
            'heroTitle' => "Jual Aplikasi CRM {$industryData['display']}",
            'heroSubtitle' => "{$industryData['tagline']}. Beli source code, jual ulang ke client {$industryData['display']} dengan brand sendiri.",
            'canonical' => url("/jual-aplikasi-crm-$industry"),
            'jsonld' => array_merge($sc['jsonld'], [
                'name' => "crmoffice — Jual Aplikasi CRM {$industryData['display']}",
                'description' => "Jual source code aplikasi CRM untuk {$industryData['display']}. {$industryData['tagline']}",
            ]),
        ])->render())->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    // ──────────────────────────────────────
    // Generic Catch-All Handler (∞ pages)
    // ──────────────────────────────────────

    public function genericHandler(string $any): Response
    {
        $path = trim($any, '/');
        $segments = explode('/', $path);

        // Try to parse known patterns from the URL structure
        $view = null;
        $viewData = $this->buildGenericViewData($path, $segments);

        if ($viewData === null) {
            abort(404);
        }

        return response(view($viewData['view'], $viewData['data'])->render())
            ->header('Cache-Control', 'public, max-age='.self::CACHE_TTL);
    }

    private function buildGenericViewData(string $path, array $segments): ?array
    {
        $industries = config('pseo.industries');
        $cities = config('pseo.cities');
        $features = config('pseo.features');
        $competitors = config('pseo.competitors');
        $roles = config('pseo.roles');
        $pricingBands = config('pseo.pricing-bands');

        // Pattern: best-crm-for-{industry}-in-{city} (handled by specific route, but catch-any variant)
        if (preg_match('/^best-crm-for-([a-z0-9-]+)-in-([a-z-]+)$/', $path, $m)) {
            $industry = $m[1];
            $city = $m[2];
            if (! isset($industries[$industry], $cities[$city])) return null;

            return [
                'view' => 'public.pseo.best-industry-city-year',
                'data' => [
                    'industry' => $industry,
                    'data' => $industries[$industry],
                    'city' => $city,
                    'cityName' => $cities[$city],
                    'year' => (int) now()->year,
                    'competitors' => array_slice($competitors, 0, 4, true),
                    'canonical' => url("/$path"),
                ],
            ];
        }

        // Pattern: crm-{feature}-for-{industry}-in-{city} (catch via generic)
        if (preg_match('/^crm-([a-z0-9-]+)-for-([a-z0-9-]+)-in-([a-z-]+)$/', $path, $m)) {
            $feature = $m[1];
            $industry = $m[2];
            $city = $m[3];
            if (! isset($features[$feature], $industries[$industry], $cities[$city])) return null;

            return [
                'view' => 'public.pseo.feature-industry-city',
                'data' => [
                    'feature' => $feature,
                    'featureData' => $features[$feature],
                    'industry' => $industry,
                    'industryData' => $industries[$industry],
                    'city' => $city,
                    'cityName' => $cities[$city],
                    'canonical' => url("/$path"),
                ],
            ];
        }

        // Pattern: best-crm-under-{price} (catch via generic)
        if (preg_match('/^best-crm-under-([0-9]+)$/', $path, $m)) {
            $price = $m[1];
            if (! isset($pricingBands[$price])) return null;

            return [
                'view' => 'public.pseo.crm-under-price',
                'data' => [
                    'price' => (int) $price,
                    'priceLabel' => '$'.$pricingBands[$price],
                    'competitors' => $competitors,
                    'canonical' => url("/$path"),
                ],
            ];
        }

        // Pattern: {role}-crm (catch via generic for roles not in the whitelist)
        if (preg_match('/^([a-z-]+)-crm$/', $path, $m)) {
            $role = $m[1];
            if (! isset($roles[$role])) return null;

            return [
                'view' => 'public.pseo.role-crm',
                'data' => [
                    'role' => $role,
                    'roleName' => $roles[$role],
                    'canonical' => url("/$path"),
                ],
            ];
        }

        // Pattern: crm-with-{feature} (catch via generic)
        if (preg_match('/^crm-with-([a-z0-9-]+)$/', $path, $m)) {
            $feature = $m[1];
            if (! isset($features[$feature])) return null;

            return [
                'view' => 'public.pseo.crm-with-feature',
                'data' => [
                    'feature' => $feature,
                    'data' => $features[$feature],
                    'canonical' => url("/$path"),
                ],
            ];
        }

        // Pattern: crm-for-{industry}-in-{city} (generic catch variant)
        if (preg_match('/^crm-for-([a-z0-9-]+)-in-([a-z-]+)$/', $path, $m)) {
            $industry = $m[1];
            $city = $m[2];
            if (! isset($industries[$industry], $cities[$city])) return null;

            return [
                'view' => 'public.pseo.industry-in-city',
                'data' => [
                    'industry' => $industry,
                    'industryData' => $industries[$industry],
                    'city' => $city,
                    'cityName' => $cities[$city],
                    'canonical' => url("/$path"),
                ],
            ];
        }

        // Pattern: software-crm-{industry}-{city} / aplikasi-crm-{industry}-{city} / sistem-crm-{industry}-{city}
        if (preg_match('/^(software-crm|aplikasi-crm|sistem-crm)-([a-z0-9-]+)-([a-z-]+)$/', $path, $m)) {
            $industry = $m[2];
            $city = $m[3];
            if (! isset($industries[$industry], $cities[$city])) return null;

            $labelMap = ['software-crm' => 'Software CRM', 'aplikasi-crm' => 'Aplikasi CRM', 'sistem-crm' => 'Sistem CRM'];

            return [
                'view' => 'public.pseo.industry-in-city',
                'data' => [
                    'industry' => $industry,
                    'industryData' => $industries[$industry],
                    'city' => $city,
                    'cityName' => $cities[$city],
                    'labelPrefix' => $labelMap[$m[1]] ?? 'CRM',
                    'canonical' => url("/$path"),
                ],
            ];
        }

        // Pattern: best-crm-for-{industry}-in-{city}-{year}
        if (preg_match('/^best-crm-for-([a-z0-9-]+)-in-([a-z-]+)-(\d{4})$/', $path, $m)) {
            $industry = $m[1];
            $city = $m[2];
            $year = (int) $m[3];
            if (! isset($industries[$industry], $cities[$city])) return null;
            if ($year < 2020 || $year > (int) now()->year + 1) return null;

            return [
                'view' => 'public.pseo.best-industry-city-year',
                'data' => [
                    'industry' => $industry,
                    'data' => $industries[$industry],
                    'city' => $city,
                    'cityName' => $cities[$city],
                    'year' => $year,
                    'competitors' => array_slice($competitors, 0, 4, true),
                    'canonical' => url("/$path"),
                ],
            ];
        }

        // Pattern: compare/{a}-vs-{b} (generic catch)
        if (preg_match('#^compare/([a-z0-9-]+)-vs-([a-z0-9-]+)$#', $path, $m)) {
            $a = $m[1];
            $b = $m[2];
            $catalog = $competitors;
            $catalog['crmoffice'] = [
                'display' => 'crmoffice',
                'tagline' => 'Modern self-hostable CRM',
                'pros' => ['Modern Laravel + Filament stack', 'Self-hostable, own your data', 'BYO integrations', 'pSEO bundled'],
                'cons' => ['Newer product (less marketplace)'],
            ];
            if (! isset($catalog[$a], $catalog[$b])) return null;

            return [
                'view' => 'public.pseo.compare',
                'data' => [
                    'a' => $a, 'b' => $b,
                    'aData' => $catalog[$a], 'bData' => $catalog[$b],
                    'canonical' => url("/$path"),
                ],
            ];
        }

        // Pattern: alternatives-to-{competitor}
        if (preg_match('/^alternatives-to-([a-z0-9-]+)$/', $path, $m)) {
            $comp = $m[1];
            if (! isset($competitors[$comp])) return null;

            $others = array_slice(array_filter($competitors, fn ($k) => $k !== $comp, ARRAY_FILTER_USE_KEY), 0, 4, true);

            return [
                'view' => 'public.pseo.alternatives-to',
                'data' => [
                    'competitor' => $comp,
                    'data' => $competitors[$comp],
                    'others' => $others,
                    'canonical' => url("/$path"),
                ],
            ];
        }

        // Pattern: crm-for-{country}
        if (preg_match('/^crm-for-([a-z-]+)$/', $path, $m)) {
            $country = $m[1];
            $countries = config('pseo.countries');
            if (! isset($countries[$country])) return null;

            return [
                'view' => 'public.pseo.crm-for-country',
                'data' => [
                    'country' => $country,
                    'data' => $countries[$country],
                    'canonical' => url("/$path"),
                ],
            ];
        }

        // Pattern: best-crm-for-{industry}-{year}
        if (preg_match('/^best-crm-for-([a-z0-9-]+)-(\d{4})$/', $path, $m)) {
            $industry = $m[1];
            $year = (int) $m[2];
            if (! isset($industries[$industry])) return null;
            if ($year < 2020 || $year > (int) now()->year + 1) return null;

            return [
                'view' => 'public.pseo.best-crm-for',
                'data' => [
                    'industry' => $industry,
                    'data' => $industries[$industry],
                    'year' => $year,
                    'competitors' => array_slice($competitors, 0, 4, true),
                    'canonical' => url("/$path"),
                ],
            ];
        }

        // Pattern: best-crm-for-{industry}
        if (preg_match('/^best-crm-for-([a-z0-9-]+)$/', $path, $m)) {
            $industry = $m[1];
            if (! isset($industries[$industry])) return null;

            return [
                'view' => 'public.pseo.best-crm-for',
                'data' => [
                    'industry' => $industry,
                    'data' => $industries[$industry],
                    'year' => (int) now()->year,
                    'competitors' => array_slice($competitors, 0, 4, true),
                    'canonical' => url("/$path"),
                ],
            ];
        }

        // Pattern: source-code-crm-{industry}-{city}
        if (preg_match('/^source-code-crm-([a-z0-9-]+)-([a-z-]+)$/', $path, $m)) {
            $industry = $m[1];
            $city = $m[2];
            if (! isset($industries[$industry], $cities[$city])) return null;

            $sc = config('pseo-source-code');
            $industryData = $industries[$industry];
            $cityName = $cities[$city];

            return [
                'view' => 'public.pseo.source-code-sales',
                'data' => [
                    'title' => "Source Code CRM {$industryData['display']} di {$cityName}",
                    'description' => "Beli source code CRM untuk {$industryData['display']} di {$cityName}. {$industryData['tagline']}. One-time payment. WhatsApp 081296052010.",
                    'heroTitle' => "Source Code CRM {$industryData['display']} di {$cityName}",
                    'heroSubtitle' => "{$industryData['tagline']}. Kirim source code CRM ke {$cityName}. One-time payment, full source code.",
                    'canonical' => url("/$path"),
                    'jsonld' => array_merge($sc['jsonld'], [
                        'name' => "crmoffice — Source Code CRM {$industryData['display']} di {$cityName}",
                        'description' => "Source code CRM self-hosted untuk {$industryData['display']} di {$cityName}.",
                    ]),
                ],
            ];
        }

        // Pattern: beli-source-code-crm-{industry}
        if (preg_match('/^beli-source-code-crm-([a-z0-9-]+)$/', $path, $m)) {
            $industry = $m[1];
            if (! isset($industries[$industry])) return null;

            $sc = config('pseo-source-code');
            $industryData = $industries[$industry];

            return [
                'view' => 'public.pseo.source-code-sales',
                'data' => [
                    'title' => "Beli Source Code CRM {$industryData['display']}",
                    'description' => "Beli source code CRM untuk {$industryData['display']}. {$industryData['tagline']}. One-time payment. WhatsApp 081296052010.",
                    'heroTitle' => "Beli Source Code CRM {$industryData['display']}",
                    'heroSubtitle' => "{$industryData['tagline']}. Dapatkan source code CRM lengkap, instal di server sendiri.",
                    'canonical' => url("/$path"),
                    'jsonld' => array_merge($sc['jsonld'], [
                        'name' => "crmoffice — Beli Source Code CRM {$industryData['display']}",
                        'description' => "Beli source code CRM self-hosted untuk {$industryData['display']}.",
                    ]),
                ],
            ];
        }

        // Pattern: jual-aplikasi-crm-{industry}
        if (preg_match('/^jual-aplikasi-crm-([a-z0-9-]+)$/', $path, $m)) {
            $industry = $m[1];
            if (! isset($industries[$industry])) return null;

            $sc = config('pseo-source-code');
            $industryData = $industries[$industry];

            return [
                'view' => 'public.pseo.source-code-sales',
                'data' => [
                    'title' => "Jual Aplikasi CRM {$industryData['display']} — Source Code Siap Pakai",
                    'description' => "Jual aplikasi CRM {$industryData['display']} source code. {$industryData['tagline']}. One-time payment. WhatsApp 081296052010.",
                    'heroTitle' => "Jual Aplikasi CRM {$industryData['display']}",
                    'heroSubtitle' => "{$industryData['tagline']}. Beli source code, jual ulang ke client dengan brand sendiri.",
                    'canonical' => url("/$path"),
                    'jsonld' => array_merge($sc['jsonld'], [
                        'name' => "crmoffice — Jual Aplikasi CRM {$industryData['display']}",
                        'description' => "Jual source code aplikasi CRM untuk {$industryData['display']}.",
                    ]),
                ],
            ];
        }

        return null;
    }
}
