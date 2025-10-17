# AWS SES Setup Guide for Newsletter System

## Overview

This guide will help you configure AWS SES (Simple Email Service) for the Next Gen Being newsletter system.

---

## Prerequisites

- AWS Account
- Domain name (for production use)
- Access to DNS settings

---

## Step 1: AWS SES Setup

### 1.1 Create IAM User for SES

1. Go to **AWS IAM Console** → Users → Create User
2. User name: `nextgenbeing-ses-user`
3. Access type: **Programmatic access** (API keys)
4. Attach policy: `AmazonSESFullAccess`
5. Save the credentials:
   - **Access Key ID**
   - **Secret Access Key**

### 1.2 Verify Email Address (Sandbox Mode - Testing)

**For testing/development:**

1. Go to **AWS SES Console** → Email Addresses → Verify a New Email Address
2. Enter your email: `your-email@example.com`
3. Check inbox and click verification link
4. Status should show: **verified**

**Limitation in Sandbox:**
- Can only send to verified email addresses
- Limited to 200 emails/day
- 1 email/second

### 1.3 Request Production Access (Required for Live Use)

**To send to any email address:**

1. Go to **SES Console** → Account Dashboard
2. Click **Request Production Access**
3. Fill out the form:
   - **Mail Type**: Transactional
   - **Website URL**: https://nextgenbeing.com
   - **Use Case Description**:
     ```
     We are launching an email newsletter system for our content platform.
     Users opt-in via double opt-in verification to receive:
     - Weekly digest of new articles
     - Premium content notifications
     - Account/subscription updates

     All emails include unsubscribe links and comply with CAN-SPAM.
     Expected volume: 5,000 subscribers sending weekly digests.
     ```
   - **Compliance**: Confirm you have processes to handle bounces/complaints
4. Submit request (usually approved within 24 hours)

### 1.4 Verify Domain (Recommended for Production)

**Benefits:**
- Send from any address @yourdomain.com
- Better deliverability
- Professional appearance
- DKIM signing for authentication

**Steps:**

1. Go to **SES Console** → Domains → Verify a New Domain
2. Enter domain: `nextgenbeing.com`
3. Check: **Generate DKIM Settings** ✓
4. AWS will provide DNS records to add:

```
TXT Record for domain verification:
Name: _amazonses.nextgenbeing.com
Value: [provided by AWS]

CNAME Records for DKIM (3 records):
Name: [random]._domainkey.nextgenbeing.com
Value: [provided by AWS]

Name: [random]._domainkey.nextgenbeing.com
Value: [provided by AWS]

Name: [random]._domainkey.nextgenbeing.com
Value: [provided by AWS]
```

5. Add these DNS records to your domain registrar (Cloudflare, GoDaddy, etc.)
6. Wait for verification (usually 15-60 minutes)
7. Status will show: **verified** with green checkmark

---

## Step 2: Configure Laravel Application

### 2.1 Update `.env` File

Open `.env` and update the mail configuration:

```env
# Mail Configuration - AWS SES
MAIL_MAILER=ses
MAIL_FROM_ADDRESS="newsletter@nextgenbeing.com"
MAIL_FROM_NAME="Next Gen Being"

# AWS SES Credentials
AWS_ACCESS_KEY_ID="your-access-key-id"
AWS_SECRET_ACCESS_KEY="your-secret-access-key"
AWS_DEFAULT_REGION="us-east-1"  # Change to your SES region
AWS_SES_REGION="us-east-1"      # Same as above

# Optional: SES Configuration Set (for tracking)
AWS_SES_CONFIGURATION_SET=""
```

**Important AWS SES Regions:**
- `us-east-1` - US East (N. Virginia) - Most common
- `us-west-2` - US West (Oregon)
- `eu-west-1` - Europe (Ireland)
- `eu-central-1` - Europe (Frankfurt)
- `ap-southeast-1` - Asia Pacific (Singapore)

**Choose the region closest to your users for best delivery speed.**

### 2.2 Update `config/mail.php` (If Needed)

Laravel 11 should auto-detect SES, but verify:

```php
'mailers' => [
    'ses' => [
        'transport' => 'ses',
        'options' => [
            'region' => env('AWS_SES_REGION', 'us-east-1'),
        ],
    ],
],

'from' => [
    'address' => env('MAIL_FROM_ADDRESS', 'newsletter@nextgenbeing.com'),
    'name' => env('MAIL_FROM_NAME', 'Next Gen Being'),
],
```

### 2.3 Install AWS SDK (If Not Already Installed)

```bash
composer require aws/aws-sdk-php
```

Check if already installed:
```bash
composer show aws/aws-sdk-php
```

---

## Step 3: Test Email Sending

### 3.1 Test with Tinker

```bash
docker exec -it ngb-app php artisan tinker
```

```php
// Test basic email
Mail::raw('Test email from AWS SES', function ($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});

// Check for errors
echo "Email sent successfully!";
```

### 3.2 Test Newsletter Verification Email

```bash
docker exec -it ngb-app php artisan tinker
```

```php
use App\Services\NewsletterService;

$service = app(NewsletterService::class);
$subscription = $service->subscribe('your-email@example.com');

echo "Verification email sent to: " . $subscription->email;
```

**Check your inbox for:**
- Verification email with "Verify Your Subscription" button
- Email should arrive within 1-2 minutes

### 3.3 Test Weekly Digest (Manual Send)

```bash
docker exec -it ngb-app php artisan newsletter:send-weekly
```

Expected output:
```
🚀 Generating weekly newsletter campaign...
📧 Sending to subscribers...
✅ Newsletter sent to [X] subscribers!
```

---

## Step 4: Monitor Email Delivery

### 4.1 SES Console Monitoring

Go to **SES Console** → Reputation Dashboard

**Key Metrics:**
- **Bounce Rate**: Keep < 5% (ideally < 2%)
- **Complaint Rate**: Keep < 0.1%
- **Sending Rate**: Monitor sending limits
- **Reputation Status**: Should be "Healthy"

### 4.2 Enable SNS Notifications (Recommended)

**Track bounces and complaints automatically:**

1. Go to **SES Console** → Email Addresses → Select verified email
2. Click **Notifications**
3. Set up SNS topics for:
   - **Bounces**
   - **Complaints**
   - **Deliveries** (optional)

**Or create Configuration Set:**

1. **SES Console** → Configuration Sets → Create
2. Name: `newsletter-tracking`
3. Add Event Destinations:
   - SNS for bounces/complaints
   - CloudWatch for metrics

4. Update `.env`:
```env
AWS_SES_CONFIGURATION_SET="newsletter-tracking"
```

### 4.3 Database Monitoring

Check newsletter engagement:

```sql
-- Check recent campaigns
SELECT * FROM newsletter_campaigns
ORDER BY created_at DESC
LIMIT 5;

-- Check send statistics
SELECT
    status,
    COUNT(*) as count,
    AVG(open_rate) as avg_open_rate
FROM newsletter_campaigns
GROUP BY status;

-- Check subscriber growth
SELECT
    DATE(created_at) as date,
    COUNT(*) as new_subscribers
FROM newsletter_subscriptions
WHERE created_at >= NOW() - INTERVAL 30 DAY
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

---

## Step 5: Production Optimization

### 5.1 Increase Sending Limits

**Default Production Limits:**
- 50,000 emails/day
- 14 emails/second

**To increase:**
1. Go to **SES Console** → Account Dashboard
2. Click **Request Sending Limit Increase**
3. Provide details about your sending patterns
4. AWS usually approves within 24 hours

### 5.2 Implement Rate Limiting in Laravel

Update `app/Services/NewsletterService.php`:

```php
use Illuminate\Support\Facades\RateLimiter;

public function sendCampaign(NewsletterCampaign $campaign, string $frequency = 'weekly'): int
{
    $subscribers = NewsletterSubscription::dueForNewsletter($frequency)->get();
    $sentCount = 0;

    foreach ($subscribers as $subscriber) {
        // Rate limit: 14 emails per second (SES limit)
        RateLimiter::attempt(
            'newsletter-sending',
            14, // Max 14 per second
            function() use ($campaign, $subscriber, &$sentCount) {
                try {
                    // Send email logic here...
                    $sentCount++;
                } catch (\Exception $e) {
                    Log::error('Newsletter send failed', [
                        'subscriber_id' => $subscriber->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            },
            1 // Per 1 second
        );
    }

    return $sentCount;
}
```

### 5.3 Queue Newsletter Sending

**For large subscriber lists (>1,000), use queues:**

Create job:
```bash
php artisan make:job SendNewsletterEmail
```

Update `NewsletterService.php`:
```php
use App\Jobs\SendNewsletterEmail;

public function sendCampaign(NewsletterCampaign $campaign, string $frequency = 'weekly'): int
{
    $subscribers = NewsletterSubscription::dueForNewsletter($frequency)->get();

    foreach ($subscribers as $subscriber) {
        SendNewsletterEmail::dispatch($campaign, $subscriber);
    }

    return $subscribers->count();
}
```

Configure queue in `.env`:
```env
QUEUE_CONNECTION=database  # or redis for better performance
```

Run queue worker:
```bash
php artisan queue:work --queue=default --tries=3
```

---

## Step 6: Handle Bounces & Complaints

### 6.1 Automatic Bounce Handling

When SES sends bounce notification, mark subscriber as inactive:

Create listener:
```bash
php artisan make:listener HandleSesNotification
```

```php
use App\Models\NewsletterSubscription;

public function handle($event)
{
    $notification = $event->notification;

    if ($notification['notificationType'] === 'Bounce') {
        $email = $notification['bounce']['bouncedRecipients'][0]['emailAddress'];

        NewsletterSubscription::where('email', $email)
            ->update(['is_active' => false]);

        Log::warning('Email bounced', ['email' => $email]);
    }

    if ($notification['notificationType'] === 'Complaint') {
        $email = $notification['complaint']['complainedRecipients'][0]['emailAddress'];

        NewsletterSubscription::where('email', $email)
            ->update(['is_active' => false]);

        Log::warning('Complaint received', ['email' => $email]);
    }
}
```

---

## Troubleshooting

### Issue: "Email address is not verified"

**Solution:**
- In sandbox mode, verify recipient email in SES Console
- Or request production access to send to any email

### Issue: "Maximum sending rate exceeded"

**Solution:**
- You're hitting SES rate limits (14/second in production)
- Implement rate limiting (see Step 5.2)
- Or request limit increase from AWS

### Issue: "AccessDenied" error

**Solution:**
- Check IAM user has `AmazonSESFullAccess` policy
- Verify AWS credentials in `.env` are correct
- Check AWS region matches SES region

### Issue: Emails going to spam

**Solutions:**
1. **Verify domain with DKIM** (Step 1.4)
2. **Add SPF record** to DNS:
   ```
   TXT record for @ or root domain:
   v=spf1 include:amazonses.com ~all
   ```
3. **Add DMARC record**:
   ```
   TXT record for _dmarc.yourdomain.com:
   v=DMARC1; p=quarantine; rua=mailto:dmarc@yourdomain.com
   ```
4. **Warm up your sending** - Start with small batches
5. **Monitor reputation** in SES Console

### Issue: High bounce rate

**Solutions:**
- Clean your email list regularly
- Use double opt-in (already implemented!)
- Remove hard bounces immediately
- Monitor soft bounces and retry

---

## Cost Estimation

**AWS SES Pricing (as of 2024):**
- First 62,000 emails/month: **FREE** (if sent from EC2)
- Additional emails: **$0.10 per 1,000 emails**
- Attachments: $0.12 per GB

**Example Costs:**
- 5,000 subscribers × 4 emails/month = 20,000 emails
- Cost: **$0.00** (within free tier)

- 50,000 subscribers × 4 emails/month = 200,000 emails
- Cost: **$14/month**

**Much cheaper than:**
- SendGrid: $15/month for 40,000 emails
- Mailchimp: $350/month for 50,000 subscribers
- Mailgun: $35/month for 50,000 emails

---

## Security Best Practices

1. **Rotate AWS credentials** every 90 days
2. **Use IAM roles** instead of access keys when possible (on EC2)
3. **Never commit** AWS credentials to git
4. **Monitor** AWS CloudTrail for API calls
5. **Enable MFA** on AWS account
6. **Set up billing alerts** in AWS

---

## Quick Start Checklist

For immediate testing:

- [ ] Create AWS account / Login to AWS
- [ ] Create IAM user with SES access
- [ ] Verify email address in SES Console
- [ ] Copy AWS credentials to `.env`
- [ ] Set `MAIL_MAILER=ses` in `.env`
- [ ] Set `AWS_DEFAULT_REGION` in `.env`
- [ ] Run `composer require aws/aws-sdk-php` (if needed)
- [ ] Test with `php artisan tinker` + send test email
- [ ] Test newsletter verification email
- [ ] Request production access (for live use)
- [ ] Verify domain with DKIM (for production)
- [ ] Add SPF and DMARC records
- [ ] Set up bounce/complaint handling
- [ ] Monitor SES reputation dashboard

---

## Next Steps After SES Setup

1. **Test Weekly Digest**: `php artisan newsletter:send-weekly`
2. **Set Up Cron Job**: For automated weekly sending
3. **Monitor Metrics**: Check open rates, click rates
4. **Grow Subscriber List**: Add CTAs across site
5. **Optimize Content**: A/B test subject lines
6. **Scale Up**: Queue sending for >1,000 subscribers

---

## Support Resources

- **AWS SES Documentation**: https://docs.aws.amazon.com/ses/
- **Laravel Mail Documentation**: https://laravel.com/docs/11.x/mail
- **SES Best Practices**: https://docs.aws.amazon.com/ses/latest/dg/best-practices.html
- **Email Deliverability Guide**: https://aws.amazon.com/ses/deliverability-dashboard/

---

**Your newsletter system is ready to send millions of emails with AWS SES!** 🚀📧

**Estimated Setup Time:** 30 minutes
**Monthly Cost:** $0-14 (depending on volume)
**Deliverability:** 99%+ with proper configuration
