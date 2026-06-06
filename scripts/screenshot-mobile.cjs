/**
 * scripts/screenshot-mobile.cjs
 * ----------------------------------------------------------------
 * Verify responsive: capture minimum 3 halaman di viewport iPhone 11 Pro Max
 * (414×896 @2x) untuk visual QA mobile theme.
 *
 * Run:
 *   1. php artisan serve --host=127.0.0.1 --port=8765 &
 *   2. node scripts/screenshot-mobile.cjs
 *
 * Output: public/marketing/screens/mobile-*.png
 */
const { chromium, devices } = require('playwright');
const path = require('path');
const fs = require('fs');

const BASE_URL = process.env.BASE_URL || 'http://127.0.0.1:8765';
const EMAIL    = process.env.DEMO_EMAIL    || 'owner@crmoffice.local';
const PASSWORD = process.env.DEMO_PASSWORD || 'password';
const OUT_DIR  = path.resolve(__dirname, '..', 'public', 'marketing', 'screens');

const PAGES = [
    { url: '/admin',          file: 'mobile-dashboard.png' },
    { url: '/admin/leads',    file: 'mobile-leads.png' },
    { url: '/admin/invoices', file: 'mobile-invoices.png' },
    { url: '/admin/tickets',  file: 'mobile-tickets.png' },
];

(async () => {
    if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        viewport: { width: 414, height: 896 },
        deviceScaleFactor: 2,
        isMobile: true,
        hasTouch: true,
        locale: 'id-ID',
        timezoneId: 'Asia/Jakarta',
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
    });
    const page = await context.newPage();

    await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[type=email], input[name=email]', EMAIL);
    await page.fill('input[type=password], input[name=password]', PASSWORD);
    await Promise.all([
        page.waitForURL(/\/admin/, { timeout: 15000 }).catch(() => null),
        page.click('button[type=submit]'),
    ]);

    if (page.url().includes('/two-factor')) {
        console.warn('⚠  2FA challenge detected. Disable 2FA for owner@ in demo env.');
        await browser.close();
        process.exit(1);
    }

    for (const p of PAGES) {
        const out = path.join(OUT_DIR, p.file);
        try {
            await page.goto(`${BASE_URL}${p.url}`, { waitUntil: 'networkidle', timeout: 20000 });
            await page.waitForTimeout(800);
            await page.screenshot({ path: out, fullPage: true });
            console.log(`  ✓ ${p.file.padEnd(28)} ← ${p.url}`);
        } catch (err) {
            console.error(`  ✗ ${p.file.padEnd(28)} ← ${p.url} (${err.message.split('\n')[0]})`);
        }
    }

    await browser.close();
    console.log(`\n→ Done. Output: ${OUT_DIR}`);
})();
