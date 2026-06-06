<?php

use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\KbController;
use App\Http\Controllers\Public\ProgrammaticSeoController;
use App\Http\Controllers\Public\PublicDocumentController;
use App\Http\Controllers\Public\SitemapController;
use Illuminate\Support\Facades\Route;

// Public document links (token-based, no auth)
Route::prefix('public')->name('public.')->group(function () {
    Route::get('/invoices/{token}', [PublicDocumentController::class, 'showInvoice'])->name('invoices.show');
    Route::get('/invoices/{token}/pdf', [PublicDocumentController::class, 'downloadInvoicePdf'])->name('invoices.pdf');
    Route::get('/invoices/{token}/pay', [PublicDocumentController::class, 'payInvoice'])->name('invoices.pay');

    Route::get('/estimates/{token}', [PublicDocumentController::class, 'showEstimate'])->name('estimates.show');
    Route::post('/estimates/{token}/accept', [PublicDocumentController::class, 'acceptEstimate'])->name('estimates.accept');
    Route::post('/estimates/{token}/decline', [PublicDocumentController::class, 'declineEstimate'])->name('estimates.decline');

    Route::get('/proposals/{token}', [PublicDocumentController::class, 'showProposal'])->name('proposals.show');
    Route::post('/proposals/{token}/accept', [PublicDocumentController::class, 'acceptProposal'])->name('proposals.accept');

    Route::get('/contracts/{token}', [PublicDocumentController::class, 'showContract'])->name('contracts.show');
    Route::post('/contracts/{token}/sign', [PublicDocumentController::class, 'signContract'])->name('contracts.sign');

    Route::get('/surveys/{token}', [\App\Http\Controllers\Public\SurveyResponseController::class, 'show'])->name('surveys.show');
    Route::post('/surveys/{token}', [\App\Http\Controllers\Public\SurveyResponseController::class, 'submit'])->name('surveys.submit');
});

// Knowledge base (public, SEO-indexed)
Route::prefix('kb')->name('kb.')->group(function () {
    Route::get('/', [KbController::class, 'index'])->name('index');
    Route::get('/search', [KbController::class, 'search'])->name('search');
    Route::get('/{category}', [KbController::class, 'category'])->name('category');
    Route::get('/{category}/{article}', [KbController::class, 'article'])->name('article');
    Route::post('/{category}/{article}/vote', [KbController::class, 'vote'])
        ->middleware('throttle:5,60')
        ->name('article.vote');
});

// Blog (public, SEO-indexed)
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/feed.xml', function () {
        $posts = \App\Models\BlogPost::with(['author', 'category'])
            ->published()
            ->latest('published_at')
            ->limit(20)
            ->get();

        $feed = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/"></rss>');
        $channel = $feed->addChild('channel');
        $channel->addChild('title', config('app.name', 'crmoffice') . ' Blog');
        $channel->addChild('link', route('blog.index'));
        $channel->addChild('description', 'Artikel, tips, dan update seputar CRM, manajemen klien, pipeline sales, dan produktivitas bisnis.');
        $channel->addChild('language', 'id-ID');
        $channel->addChild('lastBuildDate', now()->toRfc2822String());

        $atom = $channel->addChild('atom:link', '', 'http://www.w3.org/2005/Atom');
        $atom->addAttribute('href', route('blog.feed'));
        $atom->addAttribute('rel', 'self');
        $atom->addAttribute('type', 'application/rss+xml');

        foreach ($posts as $post) {
            $item = $channel->addChild('item');
            $item->addChild('title', htmlspecialchars($post->title, ENT_XML1, 'UTF-8'));
            $item->addChild('link', route('blog.show', $post->slug));
            $item->addChild('guid', route('blog.show', $post->slug));
            $item->addChild('pubDate', $post->published_at->toRfc2822String());
            $item->addChild('description', htmlspecialchars($post->excerpt ?: \Illuminate\Support\Str::limit(strip_tags($post->content), 300), ENT_XML1, 'UTF-8'));
            if ($post->author) {
                $item->addChild('author', $post->author->email . ' (' . htmlspecialchars($post->author->name, ENT_XML1, 'UTF-8') . ')');
            }
            if ($post->category) {
                $item->addChild('category', htmlspecialchars($post->category->name, ENT_XML1, 'UTF-8'));
            }
            $contentEncoded = $item->addChild('content:encoded', '', 'http://purl.org/rss/1.0/modules/content/');
            $dom = dom_import_simplexml($contentEncoded);
            $cdata = $dom->ownerDocument->createCDATASection($post->content);
            $dom->appendChild($cdata);
        }

        return response($feed->asXML(), 200, ['Content-Type' => 'application/rss+xml; charset=utf-8']);
    })->name('feed');
    Route::get('/category/{slug}', [BlogController::class, 'category'])->name('category');
    Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
});

// ═══════════════════════════════════════════════════════════
// Programmatic SEO routes
// ═══════════════════════════════════════════════════════════

// --- Core PSEO (existing) ---
Route::get('/best-crm-for-{industry}', [ProgrammaticSeoController::class, 'bestCrmFor'])
    ->where('industry', '[a-z0-9-]+')->name('pseo.best');
Route::get('/best-crm-for-{industry}-{year}', [ProgrammaticSeoController::class, 'bestCrmFor'])
    ->where(['industry' => '[a-z0-9-]+', 'year' => '20[0-9]{2}'])->name('pseo.best.year');
Route::get('/alternatives-to-{competitor}', [ProgrammaticSeoController::class, 'alternativesTo'])
    ->where('competitor', '[a-z0-9-]+')->name('pseo.alternatives');
Route::get('/compare/{a}-vs-{b}', [ProgrammaticSeoController::class, 'compare'])
    ->where(['a' => '[a-z0-9-]+', 'b' => '[a-z0-9-]+'])->name('pseo.compare');
Route::get('/crm-for-{country}', [ProgrammaticSeoController::class, 'crmFor'])
    ->where('country', '[a-z-]+')->name('pseo.country');
Route::get('/crm-for-{industry}-in-{city}', [ProgrammaticSeoController::class, 'crmForIndustryInCity'])
    ->where(['industry' => '[a-z0-9-]+', 'city' => '[a-z-]+'])->name('pseo.industry-city');
Route::get('/crm-with-{feature}', [ProgrammaticSeoController::class, 'crmWithFeature'])
    ->where('feature', '[a-z0-9-]+')->name('pseo.feature');
Route::get('/best-crm-under-{price}', [ProgrammaticSeoController::class, 'bestCrmUnderPrice'])
    ->where('price', '[0-9]+')->name('pseo.under-price');

// Static source code sales pages (must be BEFORE /{role}-crm to avoid route conflict)
Route::get('/beli-aplikasi-crm', [ProgrammaticSeoController::class, 'beliAplikasiCrm'])->name('pseo.beli');
Route::get('/jual-source-code-crm', [ProgrammaticSeoController::class, 'jualSourceCodeCrm'])->name('pseo.jual');
Route::get('/download-source-code-crm', [ProgrammaticSeoController::class, 'downloadSourceCodeCrm'])->name('pseo.download');

Route::get('/{role}-crm', [ProgrammaticSeoController::class, 'roleCrm'])
    ->where('role', '[a-z-]+')->name('pseo.role');

// --- Industry × City multi-pattern (60K pages: 3 patterns × 100 industries × 200 cities) ---
Route::get('/software-crm-{industry}-{city}', [ProgrammaticSeoController::class, 'softwareCrmIndustryCity'])
    ->where(['industry' => '[a-z0-9-]+', 'city' => '[a-z-]+'])->name('pseo.software.industry-city');
Route::get('/aplikasi-crm-{industry}-{city}', [ProgrammaticSeoController::class, 'aplikasiCrmIndustryCity'])
    ->where(['industry' => '[a-z0-9-]+', 'city' => '[a-z-]+'])->name('pseo.aplikasi.industry-city');
Route::get('/sistem-crm-{industry}-{city}', [ProgrammaticSeoController::class, 'sistemCrmIndustryCity'])
    ->where(['industry' => '[a-z0-9-]+', 'city' => '[a-z-]+'])->name('pseo.sistem.industry-city');

// --- Triple combos (100K+ pages) ---
Route::get('/best-crm-for-{industry}-in-{city}-{year}', [ProgrammaticSeoController::class, 'bestCrmIndustryCityYear'])
    ->where(['industry' => '[a-z0-9-]+', 'city' => '[a-z-]+', 'year' => '20[0-9]{2}'])->name('pseo.best.industry-city-year');
Route::get('/crm-{feature}-for-{industry}-in-{city}', [ProgrammaticSeoController::class, 'crmFeatureIndustryCity'])
    ->where(['feature' => '[a-z0-9-]+', 'industry' => '[a-z0-9-]+', 'city' => '[a-z-]+'])->name('pseo.feature-industry-city');

// --- Source Code Sales PSEO (300K pages) ---
Route::get('/source-code-crm-{city}', [ProgrammaticSeoController::class, 'sourceCodeCrmCity'])
    ->where('city', '[a-z0-9-]+')->name('pseo.source-code.city');
Route::get('/aplikasi-crm-{feature}', [ProgrammaticSeoController::class, 'aplikasiCrmFeature'])
    ->where('feature', '[a-z0-9-]+')->name('pseo.aplikasi.feature');
Route::get('/crm-{industry}-source-code', [ProgrammaticSeoController::class, 'crmIndustrySourceCode'])
    ->where('industry', '[a-z0-9-]+')->name('pseo.source-code.industry');

// Source code x city x industry combos
Route::get('/source-code-crm-{industry}-{city}', [ProgrammaticSeoController::class, 'sourceCodeCrmIndustryCity'])
    ->where(['industry' => '[a-z0-9-]+', 'city' => '[a-z-]+'])->name('pseo.source-code.industry-city');
Route::get('/beli-source-code-crm-{industry}', [ProgrammaticSeoController::class, 'beliSourceCodeCrmIndustry'])
    ->where('industry', '[a-z0-9-]+')->name('pseo.beli.industry');
Route::get('/jual-aplikasi-crm-{industry}', [ProgrammaticSeoController::class, 'jualAplikasiCrmIndustry'])
    ->where('industry', '[a-z0-9-]+')->name('pseo.jual.industry');

// Marketing pages
Route::view('/features', 'marketing.features')->name('marketing.features');
Route::view('/pricing', 'marketing.pricing')->name('marketing.pricing');
Route::get('/contact', [\App\Http\Controllers\Public\ContactController::class, 'show'])->name('marketing.contact');
Route::post('/contact', [\App\Http\Controllers\Public\ContactController::class, 'store'])
    ->middleware('throttle:public-form')
    ->name('marketing.contact.submit');

// Locale switch (sticky cookie)
Route::get('/locale/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['id', 'en'], true), 404);
    cookie()->queue(cookie()->forever('locale', $locale));

    return back();
})->where('locale', '[a-z]{2}')->name('locale.switch');

// Newsletter
Route::post('/newsletter/subscribe', [\App\Http\Controllers\Public\NewsletterController::class, 'store'])
    ->middleware('throttle:public-form')
    ->name('newsletter.subscribe');

// Sitemap & robots
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap-chunk/{file}', [SitemapController::class, 'chunk'])
    ->where('file', 'sitemap-[0-9]+\.xml')->name('sitemap.chunk');

Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /$',
        'Allow: /docs',
        'Allow: /kb',
        'Allow: /blog',
        'Allow: /marketing/',
        'Allow: /features',
        'Allow: /pricing',
        'Allow: /contact',
        'Allow: /best-crm-for-',
        'Allow: /alternatives-to-',
        'Allow: /compare/',
        'Allow: /crm-for-',
        'Allow: /crm-with-',
        'Allow: /best-crm-under-',
        'Allow: /software-crm-',
        'Allow: /aplikasi-crm-',
        'Allow: /sistem-crm-',
        'Allow: /beli-aplikasi-crm',
        'Allow: /source-code-crm-',
        'Allow: /beli-source-code-crm-',
        'Allow: /crm-',
        'Allow: /jual-source-code-crm',
        'Allow: /jual-aplikasi-crm-',
        'Allow: /download-source-code-crm',
        'Disallow: /admin',
        'Disallow: /portal',
        'Disallow: /api',
        'Disallow: /webhooks',
        'Disallow: /public/',
        'Disallow: /two-factor',
        'Disallow: /__pair',
        'Disallow: /livewire',
        'Disallow: /filament',
        '',
        'Sitemap: '.url('/sitemap.xml'),
        '',
    ];

    return response(implode("\n", $lines), 200, ['Content-Type' => 'text/plain; charset=utf-8']);
})->name('robots');

// --- Catch-all generic PSEO handler (∞ pages) ---
// MUST be the very last route to avoid swallowing static routes above
Route::get('/{any}', [ProgrammaticSeoController::class, 'genericHandler'])
    ->where('any', '.*')->name('pseo.generic');
