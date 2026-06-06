<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class WebToLeadSnippet extends Page
{
    protected string $view = 'filament.admin.pages.web-to-lead-snippet';

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCodeBracket;

    protected static ?string $navigationLabel = 'Web-to-Lead Embed';

    protected static string|\UnitEnum|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'web-to-lead';

    public string $source = 'Website';

    public function getEmbedSnippet(): string
    {
        $endpoint = url('/api/v1/public/leads');
        $source = htmlspecialchars($this->source);

        return <<<HTML
<!-- crmoffice Web-to-Lead form. Paste into your website. -->
<form id="crm-lead-form" style="max-width:480px;margin:0 auto;font-family:sans-serif">
  <h3 style="margin:0 0 12px;font-size:18px">Get in touch</h3>
  <input type="hidden" name="source" value="$source">
  <div style="margin-bottom:12px"><input name="name" placeholder="Your name *" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:14px"></div>
  <div style="margin-bottom:12px"><input name="company" placeholder="Company" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:14px"></div>
  <div style="margin-bottom:12px"><input name="email" type="email" placeholder="Email *" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:14px"></div>
  <div style="margin-bottom:12px"><input name="phone" placeholder="Phone" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:14px"></div>
  <div style="margin-bottom:12px"><textarea name="description" placeholder="Message" rows="3" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:6px;font-size:14px;font-family:inherit"></textarea></div>
  <button type="submit" style="width:100%;padding:12px;background:#4f46e5;color:#fff;border:0;border-radius:6px;font-weight:600;cursor:pointer">Send</button>
  <div id="crm-lead-status" style="margin-top:10px;font-size:13px"></div>
</form>
<script>
document.getElementById('crm-lead-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const status = document.getElementById('crm-lead-status');
  status.textContent = 'Submitting...';
  const data = Object.fromEntries(new FormData(e.target));
  try {
    const r = await fetch('$endpoint', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(data),
    });
    const j = await r.json();
    if (r.ok) { status.style.color = '#15803d'; status.textContent = '✓ Thanks! We\\'ll be in touch.'; e.target.reset(); }
    else { status.style.color = '#991b1b'; status.textContent = '✗ ' + (j.error?.message || 'Failed'); }
  } catch (err) { status.style.color = '#991b1b'; status.textContent = '✗ Network error'; }
});
</script>
HTML;
    }
}
