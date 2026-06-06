# 16 — AI Features (Phase 9 stub)

**Status:** 🟡 Adapter ready (Phase 7) · Features wiring deferred to Phase 9
**Last updated:** 2026-05-14

---

## Architecture (Already Built)

All LLM access via **format-based adapters** per global no-hardcoded-providers rule. See:

- `app/Adapters/Llm/LlmAdapterContract.php` — interface
- `app/Adapters/Llm/OpenAICompatibleAdapter.php` — covers OpenAI/DeepSeek/Groq/Mistral/Together/OpenRouter/xAI/Ollama/LM Studio/vLLM
- `app/Adapters/Llm/AnthropicFormatAdapter.php` — for native Anthropic API format
- `app/Adapters/Llm/LlmAdapterFactory.php` — `LlmAdapterFactory::active()` returns adapter for the active provider

**Owner inputs everything via admin UI:** base_url, api_key, default_model. Pricing per 1M tokens (optional, for cost tracking).

```php
$llm = LlmAdapterFactory::active();
if (! $llm) {
    // Feature disabled — owner hasn't configured an LLM provider
    return ['error' => 'No LLM provider configured. Set up in Settings → Providers.'];
}

$response = $llm->chat([
    ['role' => 'system', 'content' => 'You are a helpful sales assistant.'],
    ['role' => 'user', 'content' => "Draft a proposal for {$client->company_name} about web design."],
], ['model' => $userPickedModel, 'temperature' => 0.7]);

echo $response->content;
```

## Features to Build (Phase 9)

### 9.A AI Proposal Drafting

**Trigger:** Proposal create form → "Draft with AI" button
**Input:**
- Lead/Client context (industry, size, prior projects)
- Selected service template (web design, app dev, consulting)
- Tone (formal, friendly, casual)

**Process:**
1. Build prompt from template (`config/ai-prompts/proposal-drafting.php`)
2. Call `LlmAdapterFactory::active()->chat(...)`
3. Stream response into TipTap editor
4. User reviews, edits, finalizes

**Per-feature model picker:** Owner sets which provider for proposal drafting in Settings → AI. Empty → feature disabled.

### 9.B Email Thread Summarization

**Trigger:** Long ticket thread → "Summarize for me" button on ticket detail
**Output:** Bullet-point summary of issue + actions taken so far

### 9.C Smart Lead Scoring

**Trigger:** Lead create / activity log update
**Process:** LLM scores 1-10 based on company size, industry, expressed urgency, source quality
**Display:** Lead list shows AI score badge

### 9.D KB Article Suggestion (Ticket Form)

**Trigger:** Customer types ticket subject + body
**Process:** Semantic search against published KB articles using embeddings (if embedding endpoint configured) or LLM-based relevance
**Display:** "Maybe this helps?" — suggest top 3 articles before submit

### 9.E Auto-Tag Ticket

**Trigger:** Ticket created
**Process:** LLM classifies into: bug, feature-request, billing, how-to, complaint
**Display:** Auto-set tag, agent can override

### 9.F Customer Communication Drafting

**Trigger:** Ticket reply, follow-up email
**Process:** LLM drafts response based on conversation history + tone preference
**Display:** Editable draft in reply box

## Cost Control

- Per-feature toggle on/off (Settings → AI Features)
- Per-feature provider assignment (different model for different task — owner choice)
- Usage tracking: `llm_usage` table logs prompt_tokens + completion_tokens per call
- Owner inputs `cost_per_1m_input` and `cost_per_1m_output` per provider for spend dashboard
- Monthly budget cap with auto-disable when exceeded (optional)

## Privacy

- Sensitive data (full SSN, bank numbers, credit cards) stripped from prompts via regex pre-processor
- Owner can opt-in/opt-out per data type
- LLM calls logged with redacted prompt (for debugging only, retention 30 days)
- Documentation explicitly states what data goes to LLM provider

## Local-First Option

Configure `OpenAICompatibleAdapter` with:
- `base_url`: `http://localhost:11434` (Ollama)
- `api_key`: (empty)
- `default_model`: `llama3.1:8b` (or whatever you pulled)

Everything works the same — no data leaves your network.

## Implementation Order (Phase 9)

1. **Settings UI** for per-feature provider assignment + toggle
2. **Usage tracking** (`llm_usage` table + observer)
3. **Proposal drafting** (simplest, highest visible value)
4. **Ticket auto-tag** (background, low risk)
5. **KB suggestion** (requires embedding endpoint OR LLM relevance)
6. **Email summarization**
7. **Lead scoring**
8. **Communication drafting**

Each feature is **opt-in** — admin sets provider in Settings → AI; if no provider, feature shows "Configure LLM provider to enable" message.
