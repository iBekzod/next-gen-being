# AWS SES SMTP Configuration Guide

You have SMTP credentials for AWS SES. Here's how to configure them.

## Step 1: Determine Your AWS SES Region

Your SMTP endpoint depends on your AWS SES region:

| Region | SMTP Endpoint |
|--------|---------------|
| US East (N. Virginia) | `email-smtp.us-east-1.amazonaws.com` |
| US West (Oregon) | `email-smtp.us-west-2.amazonaws.com` |
| EU (Ireland) | `email-smtp.eu-west-1.amazonaws.com` |
| EU (Frankfurt) | `email-smtp.eu-central-1.amazonaws.com` |
| Asia Pacific (Singapore) | `email-smtp.ap-southeast-1.amazonaws.com` |

**To find your region:**
1. Log into AWS Console
2. Go to SES Dashboard
3. Look at the top-right corner - it shows your current region
4. Or check where you verified your email/domain

## Step 2: Update Production `.env`

SSH into your production server and edit the `.env` file:

```bash
cd /var/www/nextgenbeing
sudo nano .env
```

Add/update these lines:

```env
# Mail Configuration (AWS SES SMTP)
MAIL_MAILER=smtp
MAIL_HOST=email-smtp.us-east-1.amazonaws.com  # Change region if needed
MAIL_PORT=587
MAIL_USERNAME=AKIAYLO3YEF5OJBYHG43
MAIL_PASSWORD=BPGojHRPzjBgWdRkGHLSPJQh47PhdYAFgNLkEn7Qdq0S
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@nextgenbeing.com
MAIL_FROM_NAME="NextGenBeing"

# Remove or comment out these if present:
# AWS_ACCESS_KEY_ID=
# AWS_SECRET_ACCESS_KEY=
# AWS_DEFAULT_REGION=
```

**Important Notes:**
- Replace `us-east-1` with your actual SES region
- Make sure `noreply@nextgenbeing.com` is verified in SES
- If using a different from address, verify it in SES first

## Step 3: Clear Configuration Cache

```bash
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
```

## Step 4: Test Email Sending

```bash
sudo -u www-data php artisan tinker

# In tinker:
Mail::raw('Test email from NextGenBeing', function($message) {
    $message->to('your-email@example.com')
            ->subject('Test Email');
});
```

If you see `= null` (no error), the email was sent successfully!

## Step 5: Verify No Errors in Logs

```bash
tail -f storage/logs/laravel.log | grep "Newsletter send failed"
```

If nothing appears when you trigger newsletter sending, you're good!

## Troubleshooting

### Error: "554 Message rejected: Email address is not verified"

**Solution:** Verify your sender email in AWS SES:
1. Go to AWS Console → SES → Verified identities
2. Click "Create identity"
3. Choose "Email address"
4. Enter `noreply@nextgenbeing.com`
5. Check your inbox and click verification link

### Error: "Connection timed out"

**Solution:** Wrong region or firewall blocking port 587
- Check your SES region matches the endpoint
- Ensure port 587 is open on your server

### Error: "Authentication failed"

**Solution:** Wrong SMTP credentials
- Double-check username and password
- No spaces or extra characters
- Password is case-sensitive

## Production Checklist

- [ ] AWS SES is out of sandbox mode
- [ ] Sender email is verified in SES
- [ ] Correct SES region SMTP endpoint
- [ ] SMTP credentials are correct
- [ ] Port 587 is accessible
- [ ] Config cache is cleared
- [ ] Test email sent successfully
- [ ] No errors in logs

## Your Current Credentials

```
IAM User: ses-smtp-user.nextgenbeing
SMTP Username: AKIAYLO3YEF5OJBYHG43
SMTP Password: BPGojHRPzjBgWdRkGHLSPJQh47PhdYAFgNLkEn7Qdq0S
```

**Security Note:** These credentials are already committed to this file.
Consider rotating them after setup for security.
