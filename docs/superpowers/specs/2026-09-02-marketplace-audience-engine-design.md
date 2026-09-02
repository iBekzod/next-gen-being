# Marketplace as Audience Engine — Design

> **Date:** 2026-09-02
> **Status:** Approved in brainstorming; awaiting spec review before an implementation plan is written.
> **Supersedes the goal (not the code) of:** `MARKETPLACE_PHASE1_SPEC.md` — Phase 1 built a storefront to sell products. This phase repurposes that storefront to grow an email list.
> **Working directory:** all paths are relative to `d:\projects\MyProjects\nextgenbeing\next-gen-being` unless prefixed `blog-bot/`.
> **Production-verified:** 2026-09-02 against `root@161.35.73.129` (`/var/www/nextgenbeing`, at commit `f9b8511` — no drift from local `main`). All figures below are measured, not inferred.

---

## 1. Context

Publishing stopped on 2026-08-04. The cause is **not** what a reading of the local repo suggests, and this section records the measured state.

### 1.1 Generation is off by choice, not by accident

`routes/console.php:26` returns early unless `BLOG_AUTO_PUBLISH=true`, and the same flag gates weekly tutorial generation at `routes/console.php:252`. On production the variable is **present and explicitly set to `false`** — the code comment names why: it was disabled during the "de-AI-ify" of the corpus ahead of an AdSense application. This is a deliberate decision by the owner, not an oversight. **Re-enabling it reverses that decision and must be an explicit choice, not a side effect of this work.**

The scheduler itself is healthy: `* * * * * php artisan schedule:run` is live in the root crontab and fires every minute. The crons run; they self-skip.

### 1.2 The publisher is clogged, not starved

`content:drip` (daily 18:00) is ungated and still runs. Its own `--dry-run` on production reports both types **due** and nothing publishable. The backlog is not empty — it is large and almost entirely unusable:

| Draft cohort | Count |
|---|---|
| Total drafts | 255 |
| Under 1500 words (fails gate 1) | 242 — of which 195 are under 500 words |
| Truncated ending (gate 2) | 3 |
| Unbalanced code fences (gate 3) | 1 |
| **Blocked only by `moderation_status = 'pending'` (gate 4)** | **9** |
| **Publishable right now** | **0** |

Nine regular drafts clear length, ending, and fences, and are held solely by an unattended moderation queue (61 drafts sit in `pending`; 193 are `approved`, 1 `rejected`). An earlier revision of this spec called approving them the highest-leverage action available. **Inspection of the drafts withdraws that recommendation entirely** — see §1.2a.

### 1.2a The gates measure the wrong thing, and reward the failure they exist to catch

The nine were read, not just counted. Five are far outside the stated dev/AI-builder niche (quantum circuit synthesis, NASA/ESA satellite orbit determination, EV charging via OCPP, ArangoDB recommendation engines, OpenADR smart-grid distribution). The remaining four were audited line by line, and all four are structurally broken:

| Draft | Repeated sentences | Redundant copies | Worst case |
|---|---|---|---|
| #472 Database performance | 6 | 12 | one sentence ×4 |
| #466 Advanced Laravel tutorial | 18 | 18 | duplicated code blocks ×2 |
| #462 High-scale insider knowledge | 15 | 16 | the closing paragraph ×3 — it ends three times |
| #456 Multi-tenant SaaS | 17 | **47** | the same code block ×6 |

Three of the four also assert lived professional experience they cannot have — *"As a senior engineer with over 10 years of experience…"*, *"Last quarter, our team discovered…"*, *"Last year, I worked on an e-commerce application handling over 50 million requests per day"* — published under a real byline. That is the exact EEAT failure Google's HCU targets, and an honesty problem independent of SEO.

**The structural insight: `MIN_WORDS = 1500` is counter-productive as written.** Word count is inflated *by* duplication, so a draft that repeats a code block six times passes the length gate **because** it is padded. The gate rewards the precise failure mode it exists to filter. This also explains the corpus shape in §1.2 — generation produces either truncated stubs (195 drafts under 500 words) or repetitive padding, and the gate admits only the padded ones.

**Revised conclusion: zero of the 255 drafts are publishable today.** There is no quick win in the backlog. Restoring publishing requires fixing generation quality, which is materially larger than this spec first assumed and is now C1's real content.

### 1.2b Root cause of the duplication: an impossible word target

`GenerateAiPost::expandPostContent()` is a "Pass 2" that runs when Pass 1 lands under 1500 words. It sends the **entire article** back to the model and asks for it to be returned expanded to **"4000+ words"** — with the call made as `callOpenAI($messages, 4000, 0.7, false)`, where `4000` is `max_tokens`.

**4000 tokens is roughly 3,000 words of English prose, and far fewer for code-heavy text.** The prompt therefore demands an output 35–50% larger than the budget allows, while also requiring the model to reproduce the original inside that same budget. The instruction is unsatisfiable, and LLMs resolve an unsatisfiable length demand by repeating themselves. That is the duplication in §1.2a; the truncation failures (gate 2) are the same cap cutting output mid-sentence.

The prompt already anticipates the failure — *"DO NOT just repeat content"*, *"not a padded version"* — and tries to instruct it away. Instruction cannot resolve a budget contradiction; only changing the budget or the target can.

Two further defects sit in the same function:

- **A 1500–1999 word dead zone.** Expansion runs only when the draft is under 1500 words, but a hard `throw` rejects anything under 2000. Any Pass 1 landing between 1500 and 1999 words is neither expanded nor kept — it is discarded, wasting the API spend and failing silently.
- **Two different definitions of "long enough."** Generation requires ≥2000 words; the publisher's `MIN_WORDS` is 1500. These must agree.

**Recommended fix (design, not yet planned):** stop regenerating the whole article. Expand *section by section* and append, so the model never re-emits existing text and each call has a bounded, achievable output. Failing that, raise `max_tokens` to genuinely fit the target and lower the target to something honest (~2500 words). Close the dead zone by expanding whenever the draft is under the publish threshold, and unify that threshold with `MIN_WORDS`.

### 1.3 Tutorials are a deeper, older failure

The last tutorial published **2026-05-12** — nearly four months, not four weeks. Of 25 tutorial drafts, **zero** can pass the gates (22 too short, 2 truncated, 1 unbalanced fences). Unblocking tutorials therefore needs generation quality work; no flag flip fixes it.

### 1.4 The local engine is dead and has never worked in production

blog-bot is not running and cannot run: no `pythonw.exe` process, no watchdog process, and the Startup folder is empty — the `.vbs` that launched `blog-bot/watchdog.ps1` at logon is gone, so a reboot ended it permanently. Independently, `blog-bot/.venv/pyvenv.cfg` points at `C:\Users\a\...` (a different Windows profile) and the pre-move path `D:\projects\MyProjects\blog-bot`, and no real Python is installed for the current user (the `python.exe` on PATH is a 0-byte Microsoft Store alias stub).

More telling: production reports `bot:last_seen` as **never**. The heartbeat hand-off that `routes/console.php` is built around has not been observed working in production even once. (A cache flush could explain a lost value, so treat this as unproven rather than disproven — but it must not be assumed working.)

### 1.5 Nothing raised an alarm

Four weeks of silence, a due publisher finding nothing to publish every night, and no notification. **That missing alarm is the most serious defect here** — worse than any single blocked pipeline, because it is why all of the above went unnoticed.

### 1.6 The strategic assumption that failed

The marketplace was built to sell working products at a 10% take rate. Production has **11 published listings, 1 purchase ever, and 1 verified newsletter subscriber**. `BUSINESS_PLAN.md` already names distribution — not product quality — as risk #1, and eleven more demos do not address distribution. The measured numbers make the audience-first thesis (D1) considerably stronger than when it was chosen.

### 1.7 What is actually valuable, and mispriced

Each listing ships a `-prompt.md` build plan (`database/seeders/deliverables/`) — an 8-to-11-step Claude Code sequence — sitting beside a **live WebGL demo that proves the sequence works**. Competing prompt libraries are unverifiable text. This one ships with the receipt. That asset is built, paid for, and currently hidden behind a checkout that has transacted once.

---

## 2. Decisions

Three decisions were made during brainstorming and are fixed for this phase:

| # | Decision | Consequence |
|---|---|---|
| D1 | The marketplace's job for the next 90 days is **audience, not revenue** | Marketplace revenue is expected to be ~$0 this quarter. Monetization moves downstream: list → affiliate → one flagship paid product. |
| D2 | Return cadence is a **weekly flagship drop + a daily email digest** | The habit lives in the inbox. The site is the archive and the SEO surface, not the habit. |
| D3 | Scope is **content engine + marketplace repositioning**; the video blog is **deferred** to its own spec | Revisit video once the list exists and prompt-demand data shows which topics pull. |

A fourth decision follows from the evidence rather than from preference:

| # | Decision | Rationale |
|---|---|---|
| D4 | **The server becomes the primary generation engine; blog-bot is demoted to an optional quality upgrade** | The laptop-first design exists to avoid API cost, and the local `.env` (`AI_PROVIDER=groq`) makes that cost negligible. **But production runs `AI_PROVIDER=anthropic`,** where per-post cost is real rather than a rounding error, so the original cost argument for D4 does not hold as stated. D4 stands on reliability instead: the hand-off has never been observed working in production (§1.4), and the failure mode cost four weeks of unnoticed silence. If cost becomes the binding constraint, switch production to `groq` — that is a one-line env change and a far cheaper lever than depending on a laptop. |

---

## 3. Goals and non-goals

**Goals**

1. Content generation and publishing run again, on the server, without depending on a laptop.
2. A failure of the content pipeline raises an alarm within 24 hours.
3. Topics come from what is actually trending, replacing the hand-maintained 34-entry `blog-bot/topics.yaml`.
4. A stranger can obtain a prompt in exchange for an email address, with no account.
5. A daily digest and a weekly flagship drop reach that list.

**Non-goals (explicitly out of scope)**

- Ratings, reviews, seller onboarding, multi-item cart, live SaaS demo hosting.
- The video blog (deferred to its own spec).
- Rebuilding the checkout pipeline. It stays intact and largely unused; re-pricing later must not require a rebuild.
- Retiring blog-bot. It is demoted, not deleted.

**North-star metrics:** verified newsletter subscribers, and days-since-last-publish held at or below the cadence target.

---

## 4. Architecture

```
ContentSource (10 already seeded) ──cron 6h──> content:scrape-all --async
                                                          │
                                                          ▼
                                                  CollectedContent
                                                          │
                                     ContentDeduplicationService (existing)
                                                          │
                                          content:rank-topics ─── TopicQueueService
                                                          │
                                                    topic queue (projection)
                                                          │
                         ┌────────────────────────────────┴──────────────┐
                         ▼                                               ▼
              newsletter:send-daily                            ai:generate-post
              (digest, frequency='daily')                      (server, groq)
                         │                                               │
                         │                                        content:drip
                         │                                     (1500-word gate)
                         │                                               │
                         └──────────────┬────────────────────────────────┘
                                        ▼
                        weekly flagship: prompt + live demo
                          free, email-gated, announced to list
                                        │
                                        ▼
                        design / bundle tiers (paid upsell, unchanged pipeline)
```

Three components, each independently testable:

- **C1 — Engine restart and failure alarm** (ops + one new command)
- **C2 — Trending topics to queue** (one new service, one new command, two cron lines, no migration)
- **C3 — Repositioning and email capture** (one new controller flow, one new command, data changes)

---

## 5. Component C1 — Engine restart and failure alarm

**Purpose:** make publishing run unattended and make its failure loud.

**Changes, in order of leverage**

1. **Add a duplication gate, and make `MIN_WORDS` count unique content.** Per §1.2a the length gate currently rewards padding. Add a fifth check to the gate predicate: reject a draft whose repeated-sentence ratio exceeds a threshold (sentences over ~60 characters appearing more than once; #456 would fail at 47 redundant copies). Then apply `MIN_WORDS` to *deduplicated* text, so length can no longer be manufactured by repetition. Every one of the nine currently-passing drafts fails this gate — which is the correct outcome.
2. **Add an attribution gate.** Reject drafts asserting first-person professional experience (`as a senior engineer`, `our team discovered`, `last quarter we`, `in my experience`). Generated posts must not claim a career they do not have; this is the EEAT defence the corpus is supposed to have and currently does not.
3. **Fix generation, then re-run the backlog through the new gates.** This is now C1's real content and the precondition for publishing anything. The 255 existing drafts are a test corpus for the gates before they are a content source; expect most to be rejected and archived rather than repaired.
4. **Decide `BLOG_AUTO_PUBLISH` deliberately.** It is `false` on production **by choice**, to keep AI output out of the corpus ahead of an AdSense application (§1.1). Flipping it reverses that decision and is the owner's call. **The evidence now supports leaving it off** until steps 1–3 land: turning generation back on before the gates are fixed adds more of exactly the material that caused the shutdown. Whatever is chosen, add the key to `.env.example` with a comment naming both crons it gates — its absence there is why its effect was invisible.
5. **Tutorials need the same work, and more of it.** Zero of 25 tutorial drafts pass even the current gates (§1.3), and the last publish was 2026-05-12. No flag or approval restores tutorials.
6. Reinstall Python for the current user, delete and rebuild `blog-bot/.venv` (its `pyvenv.cfg` references a nonexistent interpreter), and restore the watchdog's Startup entry. Per D4 this restores an *optional* engine; nothing in the daily path may depend on it.
7. **New command `content:health-check`**, scheduled daily. It fails loudly when any of these holds:
   - no post with `status = published` in the last `POST_INTERVAL_DAYS + 2` days;
   - no tutorial published in the last `TUTORIAL_INTERVAL_DAYS + 3` days;
   - **the count of drafts that pass all four `passesGates()` checks is below a floor (default 3)** — see the correction below;
   - any draft is blocked *only* by `moderation_status = 'pending'`, i.e. the queue is unattended;
   - `content:scrape-all` has not succeeded in 24 hours.

   It logs at `error` level and emails `BACKUP_NOTIFICATION_MAIL_TO` (already configured for `spatie/laravel-backup`, so no new mail config).

> **Design correction from production data.** An earlier draft of this spec thresholded on *raw draft count*. Production has 255 drafts and 0 publishable ones — that signal would have read healthy throughout the entire four-week outage. The health check must count **gate-passing** drafts by calling the same predicate the publisher uses, which requires the `passesGates()` refactor in §8. Counting rows would have reproduced exactly the blindness this command exists to remove.

**Explicitly not changed:** `content:drip`'s gates and intervals. The 1500-word floor and 5/7-day spacing are the defence against a Google HCU penalty and stay exactly as they are.

**Interface:** `content:health-check {--dry-run}`. Exit code 0 healthy, 1 degraded. Depends only on `Post` and `ContentSource`.

---

## 6. Component C2 — Trending topics to queue

**Purpose:** replace a stale hand-written topic list with observed trends.

**What already exists and is merely dormant:** `ContentSource` (trust levels, rate limits, per-source CSS selectors), `CollectedContent`, `ContentAggregation`, `SourceWhitelistService::initializeDefaultSources()` (ten sources: Hacker News, GitHub Trending, ArXiv, Product Hunt, Dev.to, TechCrunch, The Verge, Wired, CSS-Tricks, Smashing), `ScrapeAllSourcesCommand` (`content:scrape-all {--limit=50} {--async}`), `ScrapeSingleSourceJob`, `ContentDeduplicationService`. Four migrations from January 2026. **`routes/console.php` schedules none of it.**

**The one real design problem.** `collected_content` stores no engagement signal — the migration has no points, stars, or comment-count column. Ranking by "HN upvotes" is therefore impossible without new scraping work.

**Resolution: cross-source corroboration is the trend signal.** A subject appearing across several *independent* sources inside a time window is, by definition, trending. Score each cluster as:

```
score = cluster_size × mean(source.trust_level) × recency_decay(published_at)
```

Clusters come from the existing `ContentDeduplicationService`; independence means distinct `content_source_id`, so one prolific source cannot manufacture a trend. This needs no schema change and is more robust than a single site's vote count.

**No schema change.** An earlier draft proposed a nullable `collected_content.signal_score` column as a tie-breaker for sources that expose a vote count. It is cut: no scraper extracts such a number today, so the column would ship empty and unused. If per-source engagement is ever scraped, adding the column then is a one-line additive migration — nothing here forecloses it.

**New units**

- `TopicQueueService` — clusters recent `CollectedContent`, scores clusters, returns the top N as topic candidates (title, suggested category, tags, source URLs for attribution). One public method: `topCandidates(int $limit, int $windowDays): Collection`.
- `content:rank-topics {--limit=10} {--window=7}` — runs the service and prints/persists the queue.

**Scheduling:** `content:scrape-all --async` every 6 hours; `content:rank-topics` daily, before the 09:00 generation slot.

**Consumers:** `ai:generate-post` takes its topic from the queue instead of a static list. `blog-bot/topics.yaml` is retired as the source of truth; the bot, when alive, reads the same queue over the existing HMAC API.

Attribution matters here: generated posts must cite the source URLs that produced the topic cluster. `SourceReference` and `ReferenceTrackingService` already exist for this and must be used — EEAT is the stated defence for the whole corpus.

---

## 7. Component C3 — Repositioning and email capture

**Purpose:** convert the best asset from a $5 SKU into a list-building magnet.

**Tier changes**

| Tier | Now | After |
|---|---|---|
| `code` | Ships no file (`installDeliverable()` returns `null` for it) and is already refused at `DigitalProductController.php:100`. Never sellable. | Unpublished. Deleting a dead row, not retiring a product line. |
| `prompt` | Paid (~$5) | **Free, email-gated.** The magnet. |
| `design` | Paid (~$7) | Unchanged — the single upsell. |
| `bundle` | Paid, real ZIP of design + prompt | Unchanged — the single upsell. |

`MARKETPLACE_REVENUE_SHARE = 90.00` and the whole `ProductPurchase` / webhook / download path stay untouched, so re-pricing later is a data change.

**New: no-account email gate.** The existing free path (`DigitalProductController::purchase`) creates a `ProductPurchase` and requires `auth()`. A registration wall between a stranger and the magnet defeats the purpose. New flow instead:

1. Visitor submits an email on the listing page.
2. `NewsletterService::subscribe($email, null, $frequency)` — existing method, existing double-opt-in token.
3. Verification email carries a **temporary signed URL** (`URL::temporarySignedRoute`, 7-day expiry) to the prompt file on the `private` disk.
4. Verifying both confirms the subscription and delivers the file. One action, one friction point.

Rate-limited like the existing `newsletter.quick-subscribe` route (`throttle:5,1`). No `ProductPurchase` row is created for a gated free prompt — email capture is not a sale, and conflating them would corrupt `sales_count`.

**Daily digest — small, because the plumbing exists.** `newsletter_subscriptions.frequency` is already `enum('daily','weekly','monthly')` and `NewsletterService::sendCampaign($campaign, $frequency)` already takes a frequency. Required: a `generateDailyDigest()` sibling to the existing `generateWeeklyDigest()` (sourced from the C2 topic queue plus the day's published posts), a `newsletter:send-daily` command, one cron line, and a frequency choice on the signup form. The existing weekly digest keeps running for weekly subscribers.

**Weekly drop needs a publish path.** Marketplace listing seeders (`FitTrack`, `LinkFolio`, `Nebula`, `Halo`, `Ascend`, `LandingPacks`) are invoked **only from tests** — `DatabaseSeeder` does not call them and `deploy.sh` seeds only `SiteSettingSeeder`. Today every product reaches production by a manual `php artisan db:seed --class=...`. A weekly cadence built on a manual step will be skipped. Required: a single idempotent `marketplace:sync-listings` command that seeds every listing, called from `deploy.sh`. All the seeders are already idempotent (`firstOrCreate` / `updateOrCreate`), so this is a thin wrapper.

---

## 8. Data model changes

| Change | Table | Note |
|---|---|---|
| Unpublish `code` tiers | `digital_products` | Data change via the sync command, not a migration. |
| `prompt` tiers → `is_free = true` | `digital_products` | Same. |

**No migrations and no new tables in this phase.** The topic queue is derived from `CollectedContent` at rank time rather than persisted as an entity — it is a projection, not a record, and persisting it would create a second thing to keep in sync. Both `digital_products` changes are data, applied idempotently by `marketplace:sync-listings`.

**One refactor is required rather than optional.** `ContentDripPublish` currently keeps `POST_INTERVAL_DAYS`, `TUTORIAL_INTERVAL_DAYS`, `MIN_WORDS` and `passesGates()` all `private`, but `content:health-check` must threshold against exactly the same cadence *and* apply exactly the same four gates (§5). Extract the gate predicate and its constants into a small shared unit — a `PublishGate` service, or a trait — consumed by both commands.

This is not tidiness. If the health check reimplements the gates, the two definitions drift, and the monitor silently stops describing the thing it monitors — which is the same class of failure as the outage itself. Extracting it also makes the gates directly testable in isolation, which they currently are not.

---

## 9. Scheduling

| Command | Cadence | Gated by |
|---|---|---|
| `content:scrape-all --async` | every 6h | — |
| `content:rank-topics` | daily, pre-09:00 | — |
| `ai:generate-post` | daily 09:00 (existing, ≥5-day spacing) | `BLOG_AUTO_PUBLISH` |
| `tutorials:scheduled` | Mondays 09:00 (existing) | `BLOG_AUTO_PUBLISH` |
| `content:drip` | daily 18:00 (existing, unchanged) | — |
| `newsletter:send-daily` | daily | — |
| `newsletter:send-weekly` | Mondays 09:00 (existing) | — |
| `content:health-check` | daily | — |

---

## 10. Error handling

- **Scraping:** per-source failures are isolated in `ScrapeSingleSourceJob`; one dead source must never abort the run. Sources failing repeatedly get `scraping_enabled = false` and are reported by the health check.
- **Empty topic queue:** `ai:generate-post` must skip and log rather than invent a topic. `content:health-check` reports it. A silent fallback to a generic topic is exactly the slop risk being defended against.
- **Digest with nothing to say:** skip the send. An empty daily email costs unsubscribes.
- **Signed URL expiry:** a friendly re-request page, not a 403. The subscriber already paid with their email.
- **Missing `LEMONSQUEEZY_MARKETPLACE_VARIANT_ID`:** the existing graceful message stands; the free prompt path must not depend on Lemon Squeezy at all.

---

## 11. Testing

Existing tests live in `tests/Feature/Marketplace/` and require PostgreSQL on `localhost:9061` (Docker). Current coverage is ~453 LOC against ~39.5k LOC of app code, and the last recorded run errored on every test. **Getting the suite green again is a precondition for this work, not a step within it.**

New tests, one area per component:

- **C1:** `content:health-check` flags a stale corpus, a thin backlog, and stale scraping; stays silent when healthy.
- **C2:** `TopicQueueService` ranks a multi-source cluster above a single-source one; a single prolific source cannot manufacture a trend; an empty window returns empty rather than throwing.
- **C3:** the email gate creates a subscription and no `ProductPurchase`; the signed URL delivers the file and rejects tampering and expiry; `marketplace:sync-listings` is idempotent; the `code` tier disappears from the listing page while `design`/`bundle` remain purchasable.

---

## 12. Rollout order

Each step is independently shippable and leaves the system working.

1. **Fix the gates (duplication + attribution) and re-run the backlog through them.** *No publishing until this lands; there is no quick win.*
2. Green test suite (precondition for everything below).
3. C1 — extract the gate predicate, ship the health check and alarm; decide the flag separately. *Silence becomes impossible again.*
4. C2 — schedule scraping, ship ranking, point generation at the queue. *Fresh topics.*
5. C3a — `marketplace:sync-listings` wired into `deploy.sh`. *Drops become repeatable.*
6. C3b — tier changes and the email gate. *The magnet goes live.*
7. C3c — daily digest. *The habit starts.*

---

## 13. Manual dependencies outside the code

These block delivery and no amount of implementation removes them:

1. **Read a sample of the rejected backlog** once the new gates run, and confirm the reject rate is judged correct before anything is archived at scale.
2. **Decide whether `BLOG_AUTO_PUBLISH` returns to `true`** (§1.1, C1 step 4). Disabled deliberately for the AdSense de-AI-ify; the evidence now supports leaving it off until the gates are fixed.
3. **Install Python** for the current Windows user and rebuild `blog-bot/.venv` (C1, step 4).
4. **Rotate the Telegram bot token.** `blog-bot/bot.log` is ~21 MB and prints the live token in every polling line at roughly 10-second intervals, with no rotation. Add log rotation at the same time, and add `.env.bak` to `blog-bot/.gitignore` (currently only `.env` is ignored).

> **Resolved since the first draft:** `LEMONSQUEEZY_MARKETPLACE_VARIANT_ID` **is already set on production** — the manual task in `CHROME_TASK_LS_VARIANT.md` was completed. The paid upsell can transact; this is no longer a blocker.

---

## 14. Deferred

Video blog (own spec, after list data exists) · live SaaS demo instances · seller onboarding and plagiarism/URL verification · reviews · multi-item cart · AdSense re-application · re-pricing the marketplace for revenue.
