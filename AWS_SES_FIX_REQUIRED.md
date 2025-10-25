# AWS SES Configuration Error - Action Required

## Current Issue

Production newsletter sending is failing with the error:

```
Request to AWS SES API failed. Reason: The request signature we calculated does not match the signature you provided. Check your AWS Secret Access Key and signing method.
```

## Root Cause

The AWS credentials in the production `.env` file are either:
1. **Incorrect** - Wrong Access Key ID or Secret Access Key
2. **Expired** - AWS credentials have been rotated/changed
3. **Region mismatch** - Wrong AWS region configured

## How to Fix

### Step 1: Verify AWS SES Credentials

1. Log into AWS Console: https://console.aws.amazon.com/
2. Navigate to **IAM** → **Users** → Find your SES user
3. Check if credentials are still valid
4. If needed, create new access key

### Step 2: Update Production `.env`

SSH into your production server and update these variables:

```bash
# On production server
cd /var/www/nextgenbeing
sudo nano .env
```

Update these values:

```env
AWS_ACCESS_KEY_ID=your_actual_access_key_id
AWS_SECRET_ACCESS_KEY=your_actual_secret_access_key
AWS_DEFAULT_REGION=us-east-1  # or your SES region (e.g., us-west-2, eu-west-1)
```

### Step 3: Clear Config Cache

```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
```

### Step 4: Test Newsletter Sending

```bash
sudo -u www-data php artisan tinker

# In tinker:
$subscriber = App\Models\NewsletterSubscriber::first();
$campaign = App\Models\NewsletterCampaign::first();
app('App\Services\NewsletterService')->sendCampaign($campaign);
```

## Verification

After fixing, you should **NOT** see this error in logs:

```bash
tail -f storage/logs/laravel.log | grep "Newsletter send failed"
```

## AWS SES Setup Checklist

- [ ] AWS SES is verified (not in sandbox mode)
- [ ] Sender email domain is verified
- [ ] IAM user has `ses:SendEmail` permission
- [ ] Correct AWS region is configured
- [ ] Credentials are not rotated/expired

## Alternative: Use Different Mail Provider

If AWS SES is problematic, consider switching to:

1. **Mailgun** - Easy setup, generous free tier
2. **SendGrid** - Popular alternative
3. **Postmark** - Great for transactional emails

Update `MAIL_MAILER` in `.env` and configure accordingly.

## Related Files

- Configuration: `config/services.php` (line 22-24)
- Newsletter Service: `app/Services/NewsletterService.php`
- Full AWS SES Guide: `AWS_SES_SETUP_GUIDE.md`
