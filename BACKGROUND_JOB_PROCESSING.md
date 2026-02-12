# Background Job Processing Guide

## Overview

The blog post generation system supports **background/async processing** so your application doesn't block while generating content. Jobs are queued and processed by workers.

---

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│  Your Application / Admin Panel / CLI                       │
└──────────────────┬──────────────────────────────────────────┘
                   │ Dispatch Job
                   ▼
┌─────────────────────────────────────────────────────────────┐
│  Queue (Redis)                                              │
│  - Stores job data                                          │
│  - Maintains job order                                      │
│  - Persists jobs across restarts                            │
└──────────────────┬──────────────────────────────────────────┘
                   │ Pick up job
                   ▼
┌─────────────────────────────────────────────────────────────┐
│  Queue Worker (php artisan queue:work)                      │
│  - Processes jobs from queue                                │
│  - Runs in background                                       │
│  - Auto-retries on failure                                  │
│  - Handles timeouts and errors                              │
└──────────────────┬──────────────────────────────────────────┘
                   │ Execute
                   ▼
┌─────────────────────────────────────────────────────────────┐
│  GenerateDeepResearchPostJob                                │
│  - Calls DeepResearchContentService                         │
│  - Gathers research (10-20 sec)                             │
│  - Generates content (2-3 min)                              │
│  - Creates database post (1 sec)                            │
│  Total: 3-5 minutes per post                                │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼
┌─────────────────────────────────────────────────────────────┐
│  Database                                                   │
│  - Post created with all metadata                           │
│  - Tags and categories assigned                             │
│  - Ready for user viewing                                   │
└─────────────────────────────────────────────────────────────┘
```

---

## Queue Configuration

Your system is configured with:

```env
QUEUE_CONNECTION=redis        # Uses Redis for queueing
REDIS_HOST=redis              # Redis service (in Docker)
REDIS_PORT=6379               # Default Redis port
```

Available queues:
- `default` - General purpose jobs
- `content` - Blog post generation (preferred)
- `video` - Video generation
- `email` - Email sending

---

## Usage: Background Generation

### Queue Single Post

```bash
# Queue 1 post (non-blocking)
php artisan content:generate-deep-research-bg

# Returns immediately with "Queued successfully!"
# Post generates in background while you continue working
```

### Queue Multiple Posts

```bash
# Queue 5 posts at once
php artisan content:generate-deep-research-bg --count=5

# All 5 will be processed in sequence by worker
# Takes ~15-25 minutes total (3-5 min per post)
```

### Queue with Specific Topic

```bash
# Queue post on specific topic
php artisan content:generate-deep-research-bg --topic="RAG systems"

# Queue 3 posts on same topic (with variations)
php artisan content:generate-deep-research-bg --count=3 --topic="LLM optimization"
```

### Queue with Auto-Publish

```bash
# Queue and auto-publish when done
php artisan content:generate-deep-research-bg --publish

# vs. Publish manually later
php artisan content:generate-deep-research-bg  # Creates as draft
```

### Advanced Options

```bash
# Delay processing by 60 seconds (useful for scheduling)
php artisan content:generate-deep-research-bg --delay=60

# Use different author
php artisan content:generate-deep-research-bg --author=5

# Don't auto-generate tags
php artisan content:generate-deep-research-bg --no-tags

# Specific category
php artisan content:generate-deep-research-bg --category="AI & LLMs"

# Combine options
php artisan content:generate-deep-research-bg \
  --count=3 \
  --topic="LLM optimization" \
  --author=2 \
  --publish \
  --delay=30
```

---

## Running Queue Workers

### On Local/Development

```bash
# Watch and process jobs in real-time
php artisan queue:work redis --queue=content

# Output:
# Processing jobs... [Press Ctrl+C to stop]
# Processed: GenerateDeepResearchPostJob
# Post ID 123 created: "RAG systems..."
```

### On Production (Supervisor)

Create `/etc/supervisor/conf.d/nextgenbeing-content-worker.conf`:

```ini
[program:nextgenbeing-content-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/nextgenbeing/artisan queue:work redis --queue=content --tries=3
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/nextgenbeing/storage/logs/worker-content.log
stopwaitsecs=3600
user=www-data
environment=APP_ENV=production
```

Then:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start nextgenbeing-content-worker:*
```

### Process Multiple Queues

```bash
# Process content AND video generation
php artisan queue:work redis --queue=content,video

# Process all queues
php artisan queue:work redis
```

### Daemon Mode (Production)

```bash
# Run in background with auto-restart
php artisan queue:work redis --queue=content --daemon &

# Or use supervisor (recommended)
```

---

## Monitoring Jobs

### Check Queue Size

```bash
# Connect to Redis
redis-cli

# View queue length
LLEN queues:content
# Returns: 3 (3 jobs waiting)

# View failed jobs
LLEN failed_jobs
# Returns: 0 (no failed jobs)

# Exit
exit
```

### View Job Details

```bash
# List all queued jobs
php artisan queue:work redis --queue=content --verbose

# View specific job
redis-cli LRANGE queues:content 0 -1
```

### Check Failed Jobs

```bash
# List failed jobs
php artisan queue:failed

# Retry failed job
php artisan queue:retry 12345

# Retry all failed
php artisan queue:retry all

# Purge failed jobs
php artisan queue:flush
```

### View Logs

```bash
# Real-time logs
tail -f storage/logs/laravel.log | grep -i "research\|post"

# View last 50 lines
tail -50 storage/logs/laravel.log

# Search for specific topic
grep -i "llm optimization" storage/logs/laravel.log
```

---

## Job Status & Lifecycle

### Job States

```
Queued → Processing → Completed
           ↓ (on error)
        Failed → Retry → Completed or Failed Again
```

### Timeline for Single Post

```
0s       - Job dispatched to queue
0-30s    - Waiting in queue (if others ahead)
30s      - Worker picks up job
30s-50s  - Research gathering (10-20 sec)
50s-230s - Content synthesis (2-3 min)
230s+    - Database operations (1-5 sec)
235s     - Job completed, post created
```

### Retry Behavior

```
Attempt 1: Fails
  ↓ Wait 60 seconds
Attempt 2: Fails
  ↓ Wait 300 seconds (5 min)
Attempt 3: Fails
  ↓ Wait 900 seconds (15 min)
Attempt 4: Permanent failure, moved to failed_jobs
```

---

## Workflow Examples

### Example 1: Generate 10 Posts Overnight

```bash
# Queue 10 posts (takes ~30 seconds to queue)
php artisan content:generate-deep-research-bg --count=10 --publish

# Worker processes overnight (10 posts × 3-5 min = 30-50 min total)
# Morning: Check admin panel, 10 new published posts ready
```

### Example 2: Scheduled Daily Generation

In `app/Console/Kernel.php`:

```php
$schedule->command('content:generate-deep-research-bg --count=2 --publish')
    ->dailyAt('09:00')
    ->timezone('America/New_York');
```

Then run scheduler:
```bash
php artisan schedule:work
```

This automatically generates 2 posts every day at 9 AM.

### Example 3: On-Demand from Admin Panel

Create endpoint in controller:

```php
// routes/web.php
Route::post('/admin/generate-blog', function (Request $request) {
    $count = $request->input('count', 1);
    $topic = $request->input('topic');

    for ($i = 0; $i < $count; $i++) {
        GenerateDeepResearchPostJob::dispatch($topic);
    }

    return redirect()->back()->with('success',
        "{$count} posts queued for generation"
    );
});
```

---

## Performance Tuning

### Faster Processing

```bash
# Run 2 workers in parallel (2x speed)
php artisan queue:work redis --queue=content &
php artisan queue:work redis --queue=content &

# Monitor both
tail -f storage/logs/worker-*.log
```

### Handle More Queue Depth

```bash
# Supervisor with 4 workers
[program:nextgenbeing-content-worker]
numprocs=4  # Run 4 workers simultaneously
```

### Reduce Memory Usage

```bash
# Work a limited number of jobs then restart
php artisan queue:work redis --queue=content --max-jobs=100

# Restart worker every hour
php artisan queue:work redis --queue=content --max-time=3600
```

---

## Troubleshooting

### Jobs Not Processing

**Problem**: Jobs queued but not processing

```bash
# Check if worker is running
ps aux | grep "queue:work"

# If not, start it
php artisan queue:work redis --queue=content

# Check Redis connection
redis-cli PING
# Should return: PONG
```

### Jobs Timing Out

**Problem**: "Job timeout" errors

```php
// Increase timeout in job class
// app/Jobs/GenerateDeepResearchPostJob.php
public $timeout = 900;  // 15 minutes instead of 10
```

### Jobs Stuck in Queue

**Problem**: Jobs never complete

```bash
# Check failed jobs
php artisan queue:failed

# Restart all workers
php artisan queue:restart

# Clear queue (WARNING: deletes all jobs)
php artisan queue:flush
```

### Memory Issues

**Problem**: Worker crashes with "Out of memory"

```bash
# Restart worker after each job
php artisan queue:work redis --max-jobs=1

# Or use supervisor to auto-restart
```

---

## Comparison: Synchronous vs Asynchronous

| Aspect | Synchronous | Asynchronous |
|--------|-------------|--------------|
| **Command** | `content:generate-deep-research` | `content:generate-deep-research-bg` |
| **Return time** | 3-5 minutes (blocks) | Immediate (queued) |
| **User experience** | Long wait | Instant feedback |
| **UI updates** | After generation | Can poll/websocket for updates |
| **Best for** | CLI, testing | Production, admin panels |
| **Resource usage** | High (blocks app) | Low (queued) |
| **Error handling** | Immediate feedback | Logged, can be retried |

**Recommendation**: Use **asynchronous (background)** for production.

---

## Best Practices

### 1. Always Use Background for Production

```bash
# ✅ Production
php artisan content:generate-deep-research-bg

# ❌ Don't do this in production
php artisan content:generate-deep-research
```

### 2. Set Up Proper Monitoring

```bash
# Monitor worker health
watch -n 5 'ps aux | grep queue:work'

# Monitor queue depth
watch -n 5 'redis-cli LLEN queues:content'

# Monitor logs
tail -f storage/logs/laravel.log
```

### 3. Schedule Regular Generation

```php
// app/Console/Kernel.php
$schedule->command('content:generate-deep-research-bg --count=5 --publish')
    ->weekly()
    ->mondays()
    ->at('02:00');  // Run at 2 AM Monday
```

### 4. Set Up Alerts

Monitor and alert when:
- Queue depth exceeds threshold
- Jobs fail repeatedly
- Worker crashes
- Redis connection lost

### 5. Regular Cleanup

```bash
# Weekly cleanup of old failed jobs
php artisan queue:failed  # View
php artisan queue:flush   # Clear all
```

---

## Scheduled Batch Generation

### Setup Daily Generation

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Generate 2 posts daily at 9 AM
    $schedule->command('content:generate-deep-research-bg --count=2 --publish')
        ->dailyAt('09:00')
        ->name('daily-blog-posts')
        ->withoutOverlapping();  // Prevent overlapping runs

    // Generate 5 posts weekly on Monday at 6 AM
    $schedule->command('content:generate-deep-research-bg --count=5 --publish')
        ->weeklyOn(Monday, '06:00')
        ->name('weekly-blog-posts');
}
```

### Monitor Scheduled Jobs

```bash
# List all scheduled commands
php artisan schedule:list

# Run scheduler in foreground (development)
php artisan schedule:work

# Run scheduler in background (production)
# Add to crontab:
# * * * * * cd /var/www/nextgenbeing && php artisan schedule:run >> /dev/null 2>&1
```

---

## Summary

| Need | Command |
|------|---------|
| Queue 1 post | `content:generate-deep-research-bg` |
| Queue 5 posts | `content:generate-deep-research-bg --count=5` |
| Auto-publish | `content:generate-deep-research-bg --publish` |
| Start worker | `queue:work redis --queue=content` |
| Check queue | `redis-cli LLEN queues:content` |
| View failed | `queue:failed` |
| Retry failed | `queue:retry all` |
| Monitor logs | `tail -f storage/logs/laravel.log` |

---

**✅ Background processing is production-ready and recommended!**
