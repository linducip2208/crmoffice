<?php

test('pseo: best-crm-for-agencies returns 200', function () {
    $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class)
        ->get('/best-crm-for-agencies')
        ->assertOk();
});

test('pseo: beli-aplikasi-crm returns 200', function () {
    $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class)
        ->get('/beli-aplikasi-crm')
        ->assertOk();
});

test('pseo: jual-source-code-crm returns 200', function () {
    $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class)
        ->get('/jual-source-code-crm')
        ->assertOk();
});

test('pseo: download-source-code-crm returns 200', function () {
    $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class)
        ->get('/download-source-code-crm')
        ->assertOk();
});

test('pseo: alternatives-to-perfex returns 200', function () {
    $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class)
        ->get('/alternatives-to-perfex')
        ->assertOk();
});

test('pseo: compare-crmoffice-vs-perfex returns 200', function () {
    $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class)
        ->get('/compare/crmoffice-vs-perfex')
        ->assertOk();
});

test('pseo: best-crm-for-agencies contains json-ld', function () {
    $response = $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class)
        ->get('/best-crm-for-agencies');

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('application/ld+json');
    expect($content)->toContain('Best CRM for');
});

test('pseo: beli-aplikasi-crm contains source code cta', function () {
    $response = $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class)
        ->get('/beli-aplikasi-crm');

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('Beli Aplikasi');
});

test('pseo: jual-source-code-crm contains whitelabel info', function () {
    $response = $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class)
        ->get('/jual-source-code-crm');

    $response->assertOk();
    $content = $response->getContent();

    expect($content)->toContain('Jual Source Code');
});

test('pseo: sitemap.xml returns 200', function () {
    $this->get('/sitemap.xml')->assertOk();
});

test('pseo: best-crm-under-500 returns 200', function () {
    $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class)
        ->get('/best-crm-under-500')
        ->assertOk();
});

test('pseo: invalid industry returns 404', function () {
    $this->withoutMiddleware(\App\Http\Middleware\RequirePair::class)
        ->get('/best-crm-for-nonexistent-industry-9999')
        ->assertNotFound();
});
