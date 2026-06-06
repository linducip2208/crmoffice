# 08 — Integrations (Dynamic Provider Architecture)

**Project:** crmoffice
**Governing principle:** [No Hardcoded Providers — Global Rule](../../CLAUDE.md)
**Last updated:** 2026-05-30

> **Implementation note (Phase 1–6):** Providers resource implemented with dynamic adapter pattern supporting Payment, Mail, SMS, Storage, and LLM provider types — all configurable via Admin UI with no hardcoded vendor names.

> **Rule of thumb:** Never reference a vendor by name in code. Adapters are named by **API format**, not by vendor. The owner inputs everything via Admin UI.

---

## 1. Why Dynamic?

| Hardcoded approach (Perfex-style) | Dynamic approach (this app) |
|---|---|
| `MidtransAdapter`, `StripeAdapter`, `XenditAdapter`, … | `RedirectFlowAdapter`, `EmbedFlowAdapter`, `QrFlowAdapter` |
| New gateway → developer release | New gateway → owner adds via admin UI |
| Pricing constants in code | Owner inputs rate per-provider |
| Vendor SDK lock-in | HTTP-based, format-agnostic |
| API key in `.env` or DB row tied to vendor | Encrypted at rest, per-provider |
| "Switch payment gateway" = re-architect | "Switch gateway" = toggle active flag |

**Outcome:** Code stays slim (3–4 adapters per integration type), owners stay in control, no stale pricing/model lists.

---

## 2. Provider Types (`providers.type` enum)

| Type | Purpose |
|---|---|
| `payment` | Accept invoice payments |
| `mail` | Outbound email |
| `mail_inbound` | Receive email → ticket pipe |
| `sms` | Outbound SMS notifications |
| `storage` | File storage (S3-compat) |
| `llm` | AI features (proposal draft, email summarize, KB suggest) |
| `currency_rate` | Fetch FX rates |
| `analytics` | Future: GA4, Plausible, PostHog (event sink) |

---

## 3. Provider Schema (`providers` table)

```
providers
├── id
├── name              (user-input, e.g., "Midtrans Production", "My Mailgun", "DeepSeek")
├── type              (one of above)
├── api_format        (see §4 per-type)
├── base_url          (e.g., "https://api.midtrans.com" — user inputs)
├── api_key_encrypted (Laravel encrypter)
├── extra_headers     (JSON — for custom auth headers, e.g., "Account-ID")
├── extra_config      (JSON — type-specific switches, e.g., "is_sandbox": true)
├── is_active
├── priority          (when multiple of same type, lower priority = preferred)
```

`provider_credentials` for **multiple secret fields** (e.g., when an integration needs `client_id` + `client_secret` + `webhook_secret` separately).

---

## 4. Adapters by Type

### 4.1 Payment Adapters

Three format-based adapters cover virtually all gateways:

#### 4.1.1 `RedirectFlowAdapter`
**Pattern:** App creates payment intent → returns redirect URL → customer goes to gateway-hosted page → gateway POSTs callback → app marks paid.

**Supports (autofill presets):**
- Midtrans Snap, Xendit Invoice, Stripe Checkout, PayPal Standard, Razorpay Hosted Page, Mollie Checkout, Doku Checkout, FawryPay Checkout, …

**Endpoints needed (filled per provider):**
- `create_intent_url` — POST to create a payment session
- `callback_signature_header` — name of header containing HMAC
- `callback_signature_algo` — `hmac-sha256`, `hmac-sha512`, etc.
- `status_field_path` — JSONPath in callback body to status ("settled", "succeeded", "paid")
- `amount_field_path`, `currency_field_path`, `reference_field_path`

#### 4.1.2 `EmbedFlowAdapter`
**Pattern:** Gateway provides JS SDK rendered inline → tokenizes card → returns token → app charges with server API call.

**Supports:** Stripe Elements, Braintree, Adyen, …

**Endpoints needed:**
- `client_token_url` — fetch client-side token
- `charge_url` — server-side charge with token
- Frontend script URL — gateway JS

#### 4.1.3 `QrFlowAdapter`
**Pattern:** App requests dynamic QR → displays QR → poll/webhook for payment confirmation.

**Supports:** Indonesian QRIS aggregators, OVO, GoPay direct, PromptPay (Thailand), PayNow (SG), VietQR, …

**Endpoints needed:**
- `create_qr_url` — returns QR string/image URL
- `status_poll_url` — GET status
- `expiry_seconds`

#### Common Payment Adapter Contract

```php
interface PaymentAdapterContract
{
    public function createIntent(Invoice $invoice, array $options): PaymentIntent;
    public function verifyCallback(Request $request): ?ParsedPayment;
    public function refund(Payment $payment, float $amount): RefundResult;
    public function status(Payment $payment): PaymentStatus;
}
```

Each adapter implements via HTTP calls driven by `provider.extra_config` field mappings — no vendor SDK required.

---

### 4.2 Mail Adapters

#### 4.2.1 `SmtpAdapter`
Standard SMTP via Laravel Mail. Provider config: host, port, encryption, username, password.

Covers: any SMTP server (Mailgun SMTP, Postmark SMTP, SendGrid SMTP, Amazon SES SMTP, self-hosted, etc.).

#### 4.2.2 `RestApiAdapter`
Generic REST email API. Provider config: base_url, send_endpoint, headers, request body template (mustache).

Covers: Mailgun API, Postmark API, SendGrid API, SES via API, custom transactional services.

**Body template example** (user-editable):
```json
{
  "from": "{{from_address}}",
  "to": [{ "email": "{{to_address}}", "name": "{{to_name}}" }],
  "subject": "{{subject}}",
  "html": "{{html_body}}",
  "text": "{{text_body}}",
  "headers": { "X-Tag": "{{tag}}" }
}
```

---

### 4.3 Mail Inbound Adapters

#### 4.3.1 `ImapPollAdapter`
Configure IMAP host + credentials per department → poll every N min via scheduler.

#### 4.3.2 `WebhookRouteAdapter`
Provider posts inbound emails to `/webhooks/inbound-email/{token}`.

Body parsing per provider format (Mailgun, Postmark, SendGrid Inbound Parse all have different JSON shapes). Owner picks `api_format` value: `mailgun-routes` / `postmark-inbound` / `sendgrid-inbound` / `cloudmailin` — adapter parses accordingly.

---

### 4.4 SMS Adapters

#### 4.4.1 `RestSmsAdapter`
Generic REST: POST to `send_url` with body template, parse response.

Covers: Twilio (REST), Vonage, MessageBird, Indonesian SMS gateways (Zenziva, Vonage, MISMS), Africa's Talking, etc.

#### 4.4.2 `SmppAdapter` (deferred — Phase 3)
For direct SMPP integration (operator-level). Most users won't need.

---

### 4.5 Storage Adapters

#### 4.5.1 `S3CompatibleAdapter`
Single adapter covers all S3-compatible providers (AWS S3, Cloudflare R2, Wasabi, Backblaze B2, MinIO, DigitalOcean Spaces, Linode, Vultr, etc.).

Provider config: endpoint, region, bucket, access_key, secret_key, use_path_style.

Default disk for single-server: `local` (`storage/app/private`).

---

### 4.6 LLM Adapters

#### 4.6.1 `OpenAICompatibleAdapter`
Single adapter for any OpenAI-compatible API:
- OpenAI, DeepSeek, Groq, Mistral, Together, Fireworks, OpenRouter, xAI Grok, Anyscale, Cerebras, Ollama (local), LM Studio, vLLM, …

Provider config: `base_url`, `api_key`, default `model_name` (user inputs string — no hardcoded model ID).

#### 4.6.2 `AnthropicFormatAdapter`
For native Anthropic API format (Claude direct, or compatible proxies). Provider config: `base_url`, `api_key`, `model_name`, `anthropic-version` header.

#### 4.6.3 `GeminiFormatAdapter`
For Google Gemini native API. Provider config: `base_url`, `api_key`, `model_name`.

#### Common LLM Adapter Contract

```php
interface LlmAdapterContract
{
    public function chat(array $messages, array $options): ChatResponse;
    public function listModels(): array;     // tries provider's /v1/models or equivalent
    public function estimateCost(int $promptTokens, int $completionTokens): ?float;
}
```

#### LLM Per-Feature Provider Assignment
Owner picks which provider for which feature in admin UI:
- **Proposal drafting** → provider dropdown
- **Email summary** → provider dropdown
- **KB article suggestion** → provider dropdown

**NO hardcoded defaults.** Empty until owner sets it. Each feature checks: if provider set → use; else → feature shows "Configure an LLM provider to enable."

Pricing: owner inputs per-1M-token rate (input + output) per provider in admin UI. Used for usage tracking dashboard. App does NOT maintain pricing list.

---

### 4.7 Currency Rate Adapter

#### 4.7.1 `JsonFeedAdapter`
Provider config: URL returning JSON, `rates_path` (JSONPath to rates object), `base_currency_field`.

Compatible with: exchangerate-api.com, fixer.io, openexchangerates.org, currencylayer, BI (Indonesian Central Bank) JSON exports, etc.

Owner picks update frequency (daily, 6h, manual).

---

## 5. Preset Templates (Autofill Convenience, NOT Runtime References)

Per global rule, preset JSON files exist purely as **admin UI autofill help**. Code never reads them at runtime.

### Location
`storage/app/provider-presets/{type}/*.json`

### Example: `storage/app/provider-presets/payment/midtrans-snap.json`
```json
{
  "display_name": "Midtrans Snap (Indonesia)",
  "api_format": "redirect_flow",
  "base_url": "https://app.midtrans.com",
  "sandbox_base_url": "https://app.sandbox.midtrans.com",
  "create_intent_endpoint": "/snap/v1/transactions",
  "callback_signature_header": "X-Notification-Signature",
  "callback_signature_algo": "hmac-sha512",
  "callback_payload_fields": {
    "status": "transaction_status",
    "amount": "gross_amount",
    "currency": "currency",
    "reference": "order_id"
  },
  "required_credentials": ["server_key", "client_key"],
  "docs_url": "https://docs.midtrans.com/"
}
```

### Example: `storage/app/provider-presets/llm/deepseek.json`
```json
{
  "display_name": "DeepSeek",
  "api_format": "openai_compatible",
  "base_url": "https://api.deepseek.com",
  "suggested_models": ["deepseek-chat", "deepseek-reasoner"],
  "docs_url": "https://platform.deepseek.com/"
}
```

### Admin UI Flow
1. Owner clicks "Add Payment Provider"
2. Choose: **"Use template"** (browse preset list) OR **"Build from scratch"**
3. If template chosen → form auto-fills fields → owner enters credentials + can edit any field
4. Save → encrypted, persisted
5. Optional: "Test connection" button calls `test.provider` endpoint

**Important:** Templates are version-control-ignored for credentials. Owner can:
- Skip template entirely (manual entry)
- Edit any field after autofill (URL, signature header, etc.)
- Delete a template file — code keeps working

---

## 6. Adapter Resolution Logic

When app needs to send an email (or any provider action):

```php
$provider = Provider::query()
    ->where('type', 'mail')
    ->where('is_active', true)
    ->orderBy('priority')
    ->first();

if (!$provider) {
    throw new NoActiveProviderException('mail');
}

$adapter = match($provider->api_format) {
    'smtp'    => new SmtpAdapter($provider),
    'rest_api' => new RestApiAdapter($provider),
    default   => throw new UnsupportedFormatException($provider->api_format),
};

$adapter->send($message);
```

For payment with **multiple active gateways**, customer picks at checkout (or owner sets per-client default).

---

## 7. Security

| Concern | Mitigation |
|---|---|
| Secret-at-rest | `api_key_encrypted` via Laravel encrypter (env-key-derived) |
| Secret in logs | Custom log redaction middleware strips known patterns |
| Secret in API response | Always masked (`****abcd`) unless `reveal_secret.provider` permission AND `?reveal=true` |
| Secret in error traces | Sentry scrubbing configured for `api_key`, `secret`, `token` fields |
| Webhook spoofing | HMAC signature verified per provider's signature scheme |
| Replay attack | Timestamp + nonce check on webhook (configurable window) |
| SSRF via provider URLs | URL validator: blocks RFC1918, link-local, loopback unless `extra_config.allow_internal = true` |
| Credential rotation | "Rotate" button replaces value, retains audit trail |

---

## 8. Testing Providers

**`POST /api/v1/providers/{id}/test`** — adapter-defined probe:

| Type | Probe Action |
|---|---|
| `payment` | Call gateway's account/status endpoint, OR create $0 test intent if supported |
| `mail` | Send test email to specified address |
| `sms` | Send test SMS to specified phone |
| `storage` | Upload tiny file, read back, delete |
| `llm` | Call list-models or send single-token completion |
| `currency_rate` | Fetch latest, validate rates_path |

Returns `{ "success": bool, "details": {...}, "latency_ms": N }`.

---

## 9. Auto-Discovery (Convenience, opt-in)

For LLM providers with `/v1/models`-compatible endpoint:
- **"Fetch Models"** button → calls `GET {base_url}/v1/models` → returns list → owner picks default model
- For payment providers: not applicable (no standard discovery)

---

## 10. Webhook Inbound Routing

`/webhooks/payment/{provider_id}` — incoming payment callback:
1. Load provider, verify signature using `provider.extra_config.callback_signature_*`
2. Parse payload using configured JSONPath fields
3. Match reference → invoice → apply payment via `ApplyPaymentToInvoice` action
4. Respond 200 (must be fast — async heavy work)

`/webhooks/inbound-email/{token}` — incoming email:
1. Validate token matches a department's `email_pipe_token`
2. Parse via `WebhookRouteAdapter` according to `api_format`
3. Pipe to ticket (new or reply)

---

## 11. Provider Health Monitoring

- Each adapter call records: success/failure, latency, error message
- Stored in `provider_health_checks` table (Phase 3)
- Admin dashboard: provider uptime chart
- Auto-disable provider after N consecutive failures (configurable; default disabled to prevent surprise)

---

## 12. Migration from Hardcoded (For Existing Perfex Users)

For users migrating from Perfex:
- Documented migration script: reads Perfex `tblpayment_modes` table → creates one Provider row per active gateway
- Documented mapping table from Perfex gateway class names → our `api_format` values
- Migration tool in CLI: `php artisan migrate:from-perfex --source-db=... --map=payment`

---

## 13. Anti-Patterns Forbidden

- ❌ `if ($provider->name === 'Midtrans')` — name is user-input, never branch on it
- ❌ Hardcoded model IDs (`'gpt-4o'`, `'claude-3-5-sonnet'`) — always read from `provider.extra_config.model`
- ❌ Vendor-named adapter classes — only format-based names
- ❌ Default provider config in `config/{type}.php` files — empty defaults only
- ❌ Pricing constants in code — always per-provider DB value
- ❌ `env('MIDTRANS_KEY')` — never read provider credentials from env

---

## 14. Reference

Full background and decision history: see [Global CLAUDE.md "No Hardcoded Providers"](../../CLAUDE.md) + sister project `foodscan` docs.
