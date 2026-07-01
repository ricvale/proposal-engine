# AI Proposal Engine — Build Plan

Think:
  "I'm building the fastest way to go from client brief → professional proposal."

A local-first SaaS tool that turns a client brief into a complete, editable proposal using an LLM. Built single-user first, on a clean Laravel foundation, at near-zero cost. The goal is a tool you personally use for every freelance opportunity, improved from real usage.

---

## Stack

- **Framework:** Laravel 13 (released March 17, 2026; zero breaking changes from 12). Blade + Livewire.
- **PHP:** 8.3+ required by Laravel 13 — confirm `php -v` before `laravel new`, or install won't run. Composer 2.x.
- **DB:** SQLite to start (one file, zero setup). Swap to MySQL later via env if needed.
- **Local env:** Laravel Herd (free; bundles PHP, nginx, `.test` domains)
- **LLM abstraction:** Laravel 13 **AI SDK** (first-party, provider-agnostic, stable). Handles retries, error normalization, queue integration. Native Anthropic + OpenAI providers.
- **LLM (dev):** Ollama running locally — free, offline (`llama3.1:8b` or `qwen2.5:7b`)
- **LLM (prod-quality):** Anthropic via the AI SDK — config swap, same code path
- **PDF export:** `barryvdh/laravel-dompdf` (pure PHP, runs anywhere)
- **Quality tools:** Pint (style), Larastan (static analysis), Pest (tests)

---

## Architecture

Standard layered Laravel. Laravel 13's AI SDK is already the provider-agnostic seam, so the architecture is simpler than originally planned — but keep a **thin `LlmClient` interface over the SDK** for two reasons: trivial mocking in tests, and a single place to wrap Ollama for local dev (the SDK's native providers are Anthropic/OpenAI; local models need a small wrapper).

```
Presentation (Blade + Livewire)
  └─ create form, section editor, profile page
HTTP (thin controllers + Form Requests)
  └─ validate, delegate, return view
Application / Services
  ├─ ProposalGenerator  (orchestrates generation)
  └─ PdfExporter
Domain
  ├─ Models: User, Proposal, ProfileContext
  └─ Value objects: GeneratedProposal, ProposalInput
Infrastructure (swappable adapters)
  └─ LlmClient (interface)
       ├─ AiSdkClient   (wraps Laravel AI SDK → Anthropic, prod-quality)
       └─ OllamaClient  (local, free, dev)
```

**SDK vs. interface — the decision:** Use the AI SDK for the actual Anthropic call (you get its retry/error/queue handling for free), but keep your own `LlmClient` interface in front of it. The interface costs ~20 lines and buys you clean tests + a home for the Ollama path. Don't call the SDK directly from `ProposalGenerator`.

**Generation flow:**
Controller → `ProposalGenerator::generate(ProposalInput, ProfileContext)` → build system prompt (inject ProfileContext) → build user prompt (brief + hints) → `LlmClient::generate()` → parse + validate JSON → `GeneratedProposal` → persist → redirect to editor.

Keep `ProfileContext` in the **system prompt** (stable across generations → benefits from prompt caching on the Anthropic side later).

---

## Data model (3 tables)

**proposals**
- id, user_id (FK), title
- client_brief (text — pasted email/brief, raw)
- project_type, budget_hint, timeline_hint, tech_stack (all nullable strings)
- generated_content (json — section-keyed, see below)
- status (enum: draft | final, default draft)
- timestamps

**profile_context** (one row per user — your differentiator)
- id, user_id (FK)
- bio (text — positioning, years exp)
- rate_card (text — how you price)
- past_projects (text — 3–5 wins to anchor the AI)
- default_assumptions (text — boilerplate scope assumptions)
- timestamps

**users** — Laravel default

**generated_content JSON shape:**
```json
{
  "summary": "...",
  "scope": ["...", "..."],
  "deliverables": ["...", "..."],
  "timeline": "...",
  "pricing": "...",
  "assumptions": ["...", "..."],
  "next_steps": ["...", "..."]
}
```

---

## Routes (v1)

```
GET   /proposals                  list
GET   /proposals/create           form (brief + hints)
POST  /proposals                  generate → store → redirect to edit
GET   /proposals/{id}/edit        section editor
PATCH /proposals/{id}             save edits
POST  /proposals/{id}/regenerate  (optional) regen one section
GET   /proposals/{id}/pdf         export
GET   /profile                    edit profile_context
PATCH /profile
```

---

## Key code seam (write this first)

```php
interface LlmClient {
    public function generate(string $system, string $prompt): string;
}
```

```php
// config/llm.php
return [
    'driver'       => env('LLM_DRIVER', 'ollama'), // ollama | aisdk
    'ollama_model' => env('OLLAMA_MODEL', 'llama3.1:8b'),
];

// AppServiceProvider::register()
$this->app->bind(LlmClient::class, fn () =>
    config('llm.driver') === 'aisdk'
        ? new AiSdkClient()      // wraps Laravel 13 AI SDK → Anthropic
        : new OllamaClient()
);
```

- `AiSdkClient` calls the Laravel AI SDK (Anthropic provider). The SDK handles retries/error normalization — keep this wrapper thin, just adapt the SDK response to a JSON string.
- `OllamaClient` hits `http://localhost:11434/api/chat` with `"format":"json"` to force valid JSON.
- Tests mock `LlmClient` so they never call the SDK or Ollama.

Check tomorrow whether the AI SDK has a clean Ollama/local provider. If yes, you could route everything through the SDK and drop `OllamaClient`. If not (likely), the interface above keeps both paths clean.

---

## Build order (tomorrow)

1. Confirm `php -v` is **8.3+**. Then `laravel new proposal-gen` (installer pulls Laravel 13). Set SQLite, run under Herd.
2. Install: Livewire, Laravel AI SDK, dompdf, Pint, Larastan, Pest.
3. Write 3 migrations + models (User, Proposal, ProfileContext).
4. `LlmClient` interface + `OllamaClient` + `AiSdkClient` + config binding. **Do this before anything LLM-related.**
5. `ProposalGenerator` service + the prompt. Iterate in Tinker against Ollama until JSON shape is solid and reliable.
6. Profile page (enter your real bio, rates, past projects).
7. Create form → generate → store → redirect.
8. Livewire section editor (edit per-section, save).
9. Dompdf export with one clean Blade template.
10. Flip `LLM_DRIVER=aisdk`, add Anthropic key, compare output quality on a real brief.
11. **Use it on your next real Upwork bid. Fix whatever annoyed you.**

---

## Cost

- Hosting/dev: $0 (Herd + SQLite + local Ollama, all free, offline)
- LLM while building: $0 on Ollama
- Anthropic (only when you want client-quality output): ~1 cent per proposal on Haiku 4.5; ~100 proposals < $1. No subscription — pay per token.

---

## Explicitly deferred (do NOT build yet)

Workspaces, multi-tenancy, client CRUD, version history, DOCX export, shareable links, subscriptions, teams, integrations. Every one is a clean Laravel refactor when a real user or paying customer forces it. The `LlmClient` interface is the only seam built ahead of need, because the provider boundary is the one thing genuinely expensive to retrofit.

---

## The real risk (keep in focus)

The engineering is the easy part. The product value lives in the **prompt + your ProfileContext** — that's what stops the output reading like generic AI. An 8B local model gives structurally correct proposals but weak prose; validate real client-facing output on Anthropic before sending anything. Spend disproportionate time on step 5.