/**
 * scripts/screenshot.cjs
 * ----------------------------------------------------------------
 * Capture real desktop screenshots dari admin panel untuk landing.
 *
 * Run:
 *   1. php artisan serve --host=127.0.0.1 --port=8765 &  (atau di terminal lain)
 *   2. php artisan migrate:fresh --seed                  (kalau belum ada demo data)
 *   3. node scripts/screenshot.cjs
 *
 * Output: public/marketing/screens/*.png
 *
 * Override base URL: BASE_URL=http://localhost:8000 node scripts/screenshot.cjs
 */
const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = process.env.BASE_URL || 'http://127.0.0.1:8765';
const EMAIL    = process.env.DEMO_EMAIL    || 'owner@crmoffice.local';
const PASSWORD = process.env.DEMO_PASSWORD || 'password';
const OUT_DIR  = path.resolve(__dirname, '..', 'public', 'marketing', 'screens');

/**
 * URL list untuk capture. Tambahkan/buang sesuai kebutuhan landing.
 * `file` = nama output PNG (sinkron dengan marketing.blade.php $features[].screen + $gallery[].file)
 * `clip` (optional) = { x, y, width, height } untuk crop area tertentu
 * `waitFor` (optional) = selector yang harus muncul sebelum screenshot
 */
const PAGES = [
    // Feature sections (alternating layout)
    { url: '/admin',                    file: 'dashboard.png', lazyWidgets: true },
    { url: '/admin/leads-kanban',       file: 'leads-kanban.png' },
    { url: '/admin/invoices',           file: 'invoices.png' },
    { url: '/admin/projects',           file: 'projects.png' },
    { url: '/admin/tickets',            file: 'tickets.png' },
    { url: '/admin/providers',          file: 'providers.png' },
    { url: '/admin/estimates',          file: 'estimates.png' },
    { url: '/admin/sales-report',       file: 'reports.png' },

    // Gallery grid
    { url: '/admin/contacts',           file: 'gallery-contacts.png' },
    { url: '/admin/task-gantt',         file: 'gallery-tasks-gantt.png' },
    { url: '/admin/time-report',        file: 'gallery-time.png' },
    { url: '/admin/kb-articles',        file: 'gallery-kb.png' },
    { url: '/admin/calendar',           file: 'gallery-calendar.png' },
    { url: '/admin/audit-log',          file: 'gallery-audit.png' },
    { url: '/admin/aging-report',       file: 'gallery-aging.png' },
    { url: '/admin/settings',           file: 'gallery-settings.png' },
];

(async () => {
    if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

    console.log(`→ Launching Chromium…`);
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 1680, height: 1050 },
        deviceScaleFactor: 1,
        locale: 'id-ID',
        timezoneId: 'Asia/Jakarta',
    });
    const page = await context.newPage();

    // ----- Login flow -----
    console.log(`→ Logging in as ${EMAIL}…`);
    await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[type=email], input[name=email]', EMAIL);
    await page.fill('input[type=password], input[name=password]', PASSWORD);
    await Promise.all([
        // Hindari race: /admin/login juga match regex /\/admin/ lama — pakai predicate pathname.
        page.waitForURL((url) => url.pathname.startsWith('/admin') && url.pathname !== '/admin/login', { timeout: 30000 }),
        page.click('button[type=submit]'),
    ]);

    // Skip 2FA challenge if shown — for owner demo account we leave 2FA disabled
    if (page.url().includes('/two-factor')) {
        console.warn('⚠  2FA challenge detected. Disable 2FA for owner@ in demo env or extend script to enter TOTP.');
        await browser.close();
        process.exit(1);
    }

    console.log(`→ Logged in. Capturing ${PAGES.length} pages…`);

    let captured = 0;
    let failed = 0;

    for (const p of PAGES) {
        const target = `${BASE_URL}${p.url}`;
        const out = path.join(OUT_DIR, p.file);
        try {
            await page.goto(target, { waitUntil: 'domcontentloaded', timeout: 30000 });
            await page.waitForSelector('.fi-page-content', { timeout: 15000 }).catch(() => null);
            if (p.waitFor) {
                await page.waitForSelector(p.waitFor, { timeout: 8000 }).catch(() => null);
            }
            if (p.lazyWidgets) {
                // Dashboard: widget Livewire lazy — tunggu semua render selesai.
                const deadline = Date.now() + 60000;
                while (Date.now() < deadline) {
                    const ready = await page.evaluate(() => {
                        const els = Array.from(document.querySelectorAll('.fi-page-content [wire\\:id]'));
                        return els.length > 0 && els.every((el) => el.innerText.trim().length > 0);
                    }).catch(() => false);
                    if (ready) break;
                    await page.waitForTimeout(500);
                }
            }
            // Pause sebentar agar chart/animation settle
            await page.waitForTimeout(1200);
            await page.evaluate(() => window.scrollTo(0, 0));
            await page.waitForTimeout(300);

            await page.screenshot({
                path: out,
                fullPage: false,
                ...(p.clip ? { clip: p.clip } : {}),
            });
            console.log(`  ✓ ${p.file.padEnd(28)} ← ${p.url}`);
            captured++;
        } catch (err) {
            console.error(`  ✗ ${p.file.padEnd(28)} ← ${p.url} (${err.message.split('\n')[0]})`);
            failed++;
        }
    }

    await browser.close();
    console.log(`\n→ Done. ${captured} captured, ${failed} failed. Output: ${OUT_DIR}`);
    process.exit(failed > 0 ? 1 : 0);
})();
