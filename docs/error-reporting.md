# Error reporting -> Telegram + Jarvis feed

Ported from muslim-stack (commit 875a246) on 2026-09-30. Before this the site
had no error reporter at all: failures only landed in `storage/logs/laravel.log`,
which nobody reads.

```
unhandled exceptions (bootstrap/app.php withExceptions->reportable) --+
failed queue jobs (JobFailed listener, nextgenbeing-queue.service) ----+--> ErrorReporter
                                                                           |-> storage/logs/error-feed.jsonl (Jarvis, before throttling)
                                                                           '-> @im_error_report_bot forum group, "Nextgenbeing" topic
```

## Server .env

```
TELEGRAM_ERROR_BOT_TOKEN=...          # same bot as the other projects; never commit
TELEGRAM_ERROR_CHAT_ID=-1004401296266 # forum group
TELEGRAM_ERROR_TOPIC_ID=4             # Nextgenbeing topic (message_thread_id)
ERROR_REPORT_PROJECT=Nextgenbeing     # Jarvis matches on this exact value
ERROR_REPORT_ENV=production
```

After editing `.env`: `php artisan config:cache` and
`systemctl restart nextgenbeing-queue.service` - the worker is a long-running
process and keeps the old config (and no reporter) until restarted.

Check end to end: `php artisan error:test` - a message must arrive in the
Nextgenbeing topic and one line must be appended to
`storage/logs/error-feed.jsonl`. Running it as root creates the feed file as
root; `chown www-data:www-data storage/logs/error-feed.jsonl*` afterwards, or
php-fpm cannot append and the feed silently stops.

## What is sent

Unhandled exceptions, 5xx, failed jobs. Not sent: 4xx (401/403/404/405/419/422/429)
and validation/auth/not-found exceptions - daily noise that would bury real faults.

Throttling: one message per fingerprint per 10 minutes (repeats are counted and
shown on the next message), 40 messages per hour total, then one warning.

## Jarvis feed

`FeedWriter` appends one JSON line per event to `storage/logs/error-feed.jsonl`
before Telegram throttling, at most once per fingerprint per 60 s, rotates to
`.1` at 2 MB and never throws. Fields:
`{v, id, ts, project, env, kind, type, fingerprint, culprit, message, stack,
release, request, status, runtime, server}`. Set `ERROR_REPORT_FEED_PATH=` (empty)
to disable.

## Code

| File | Role |
|---|---|
| `app/Services/Telemetry/ErrorReporter.php` | filter, context, dispatch |
| `app/Services/Telemetry/ErrorEvent.php` | event + fingerprint + culprit |
| `app/Services/Telemetry/EventFormatter.php` | Telegram HTML |
| `app/Services/Telemetry/Redactor.php` | strips JWTs, passwords, phones, cards |
| `app/Services/Telemetry/Breadcrumbs.php` | last 40 steps (queries, http, jobs), in memory |
| `app/Services/Telemetry/Throttle.php` | dedupe + hourly cap (cache) |
| `app/Services/Telemetry/TelegramTransport.php` | sendMessage |
| `app/Services/Telemetry/FeedWriter.php` | Jarvis feed |
| `app/Providers/TelemetryServiceProvider.php` | wiring to Laravel events |
| `app/Console/Commands/ErrorTestCommand.php` | `php artisan error:test` |

Tests: `tests/Unit/Telemetry/`.

## Known pitfall (2026-09-30)

`bootstrap/cache/config.php` on the server references FilamentTiptapEditor
classes, so loading the cached config with a bare `php -r` fatals. `artisan`
itself boots fine. Use artisan (tinker) for checks, not `php -r` + require.
