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
- **LLM abstraction:** Laravel 13 **AI SDK** (first-party, provider-agnostic, stable). Handles retries, error normalization, queue integration. Native providers include Anthropic, OpenAI, **and Ollama** — so both the dev and prod paths go through the SDK.
- **LLM (dev):** Ollama running locally — free, offline (`llama3.1:8b` or `qwen2.5:7b`), via the SDK's Ollama provider
- **LLM (prod-quality):** Anthropic via the AI SDK — env swap, same code path
- **PDF export:** `barryvdh/laravel-dompdf` (pure PHP, runs anywhere)
- **Quality tools:** Pint (style), Larastan (static analysis), Pest (tests)

---

## Architecture

Standard layered Laravel. Laravel 13's AI SDK **is** the provider-agnostic seam — it ships native Ollama and Anthropic providers, so there is no custom interface, no `OllamaClient`, no `AiSdkClient`. Switching local ↔ Anthropic is a config/env swap; tests use the SDK's built-in fakes instead of mocking a hand-rolled interface.

```
Presentation (Blade + Livewire)
  └─ create form, section editor, profile page
HTTP (thin controllers + Form Requests)
  └─ validate, delegate, return view
Application / Services
  ├─ ProposalGenerator  (orchestrates generation, calls the AI SDK)
  └─ PdfExporter
Domain
  ├─ Models: User, Proposal, ProfileContext
  └─ Value objects: GeneratedProposal, ProposalInput
Infrastructure
  └─ Laravel AI SDK (providers: Ollama for dev, Anthropic for prod)
```

**Generation flow:**
Controller → `ProposalGenerator::generate(ProposalInput, ProfileContext)` → build system prompt (inject ProfileContext) → build user prompt (brief + hints) → AI SDK call → **structured output** (JSON schema derived from the `generated_content` shape — check how the SDK exposes it, e.g. via `providerOptions()`; keep Ollama's `"format": "json"` on the dev path) → `GeneratedProposal` → persist → redirect to editor. Structured outputs remove the malformed-JSON retry problem on the Anthropic path.

Keep `ProfileContext` in the **system prompt** (stable across generations). Don't design around prompt caching, though — caching needs a ~2K-token minimum prefix, which a short bio + rate card may not reach, and at 1–3¢ per proposal the savings are noise.

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

## Key configuration (set this first)

No custom seam to write — the AI SDK is the seam. Provider and model come from env:

```
# .env — local dev (free, offline)
AI_PROVIDER=ollama
AI_MODEL=llama3.1:8b

# .env — prod-quality output
AI_PROVIDER=anthropic
AI_MODEL=claude-sonnet-5
ANTHROPIC_API_KEY=sk-ant-...
```

- `ProposalGenerator` reads provider/model from config and calls the SDK directly. The SDK handles retries and error normalization.
- Tests use the SDK's built-in fakes — no HTTP, no Ollama, no custom mock interface.
- Optional nicety: the SDK supports automatic provider failover (e.g. fall through to a second provider on rate limit) — not needed for v1.

### Models (exact IDs — copy these, don't guess)

| Model | ID for `.env` | ~Cost/proposal | Use |
|---|---|---|---|
| Claude Haiku 4.5 | `claude-haiku-4-5` | ~1¢ | cheapest |
| Claude Sonnet 5 | `claude-sonnet-5` | ~2–3¢ (intro pricing through Aug 2026) | **recommended** — weak prose is this project's top risk |
| Claude Opus 4.8 | `claude-opus-4-8` | ~5–8¢ | highest quality |

Use the aliases exactly as written: **no date suffixes** (`claude-sonnet-5-20260301` is wrong) and **no dots** (`claude-sonnet-4.6` is wrong). Wrong IDs 404.

### API gotchas (so tomorrow's code doesn't 400)

- Do **not** set `temperature`/`top_p` — rejected with a 400 on Sonnet 5 / Opus 4.7+.
- Do **not** use assistant-prefill to force JSON (seeding the reply with `{"summary": "`) — 400 on all current models. Structured outputs replace it.
- Haiku 4.5 differs slightly (200K context vs 1M, no `effort` param) — keep request options minimal and model-agnostic so any ID works.

---

## Build order (tomorrow)

1. Confirm `php -v` is **8.3+**. Then `laravel new proposal-gen` (installer pulls Laravel 13). Set SQLite, run under Herd.
2. Install: Livewire, Laravel AI SDK, dompdf, Pint, Larastan, Pest. Set `AI_PROVIDER=ollama` in `.env`.
3. Write 3 migrations + models (User, Proposal, ProfileContext).
4. `ProposalGenerator` service + the prompt. Iterate in Tinker against Ollama until the JSON shape is solid and reliable. **This is the day's time sink — budget for it.**
5. Profile page (enter your real bio, rates, past projects).
6. Create form → generate → store → redirect.
7. Livewire section editor (edit per-section, save).
8. Dompdf export with one clean Blade template. *(If the one-day target slips, this is the safest step to defer to day 2.)*
9. Flip `AI_PROVIDER=anthropic` + `AI_MODEL=claude-sonnet-5`, add the API key, compare output quality on a real brief.
10. **Use it on your next real Upwork bid. Fix whatever annoyed you.**

---

## Cost

- Hosting/dev: $0 (Herd + SQLite + local Ollama, all free, offline)
- LLM while building: $0 on Ollama
- Anthropic (only when you want client-quality output): ~1¢/proposal on Haiku 4.5, ~2–3¢ on Sonnet 5 (recommended), ~5–8¢ on Opus 4.8. Even on Sonnet, dozens of proposals < $1. No subscription — pay per token.

---

## Explicitly deferred (do NOT build yet)

Workspaces, multi-tenancy, client CRUD, version history, DOCX export, shareable links, subscriptions, teams, integrations. Every one is a clean Laravel refactor when a real user or paying customer forces it. Nothing is built ahead of need — the provider boundary, the one thing genuinely expensive to retrofit, is already owned by the AI SDK.

---

## The real risk (keep in focus)

The engineering is the easy part. The product value lives in the **prompt + your ProfileContext** — that's what stops the output reading like generic AI. An 8B local model gives structurally correct proposals but weak prose; validate real client-facing output on Anthropic before sending anything. Spend disproportionate time on step 4.