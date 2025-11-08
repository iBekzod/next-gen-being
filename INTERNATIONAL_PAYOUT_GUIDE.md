# International Payout Guide: Alternatives for Uzbekistan & Global Markets

## Overview

This guide explains the international payment options available for blogger payouts, specifically designed for regions where PayPal is not available, including Uzbekistan.

## Supported Payment Methods

### 1. **Wise (TransferWise)** ⭐ RECOMMENDED

**Why Best Choice**:
- ✅ Available in Uzbekistan
- ✅ Lowest fees (0.5-2% typically)
- ✅ Supports 50+ currencies including UZS (Uzbek Som)
- ✅ Direct bank transfers to local Uzbek banks
- ✅ API available for automation
- ✅ Business account with batch payments
- ✅ Transparent exchange rates

**Setup for Bloggers**:
1. Sign up at https://wise.com
2. Complete identity verification
3. Add Uzbek bank account or get Wise balance
4. Share Wise email or bank details with admin

**Setup for Admin (You)**:
1. Create Wise Business account
2. Verify business
3. Add funding source (your bank or LemonSqueezy account)
4. Use Wise Batch Payments for bulk payouts

**Processing Payouts**:
```
Manual Method:
1. Export CSV from admin panel
2. Upload to Wise Batch Payment
3. Approve batch (takes 1-2 business days)
4. Bulk approve in admin with Wise batch ID

Automated Method (API):
- Use Wise API to automate transfers
- See integration guide below
```

**Fees**:
- $2-5 USD → Uzbekistan: ~$0.50-1.00
- $50 USD → Uzbekistan: ~$1.50-2.50
- $500 USD → Uzbekistan: ~$5-10

**Transfer Time**: 1-2 business days

---

### 2. **Payoneer**

**Why Good**:
- ✅ Available in Uzbekistan
- ✅ Mass payout feature
- ✅ Good for international payments
- ✅ Virtual US bank account
- ✅ Integrates with marketplaces

**Setup for Bloggers**:
1. Sign up at https://payoneer.com
2. Complete verification
3. Link local bank account
4. Share Payoneer email

**Setup for Admin**:
1. Create Payoneer business account
2. Set up mass payout service
3. Add funding source

**Fees**:
- Receiving: Free
- Withdrawal to bank: $1.50 per transfer
- Currency conversion: ~2% above mid-market rate

**Transfer Time**: 2-5 business days

---

### 3. **Stripe Payouts**

**Why Good**:
- ✅ Already using Stripe (likely)
- ✅ API-driven automation
- ✅ Good documentation
- ✅ Fast payouts

**Availability in Uzbekistan**:
⚠️ **Limited** - Stripe payouts not directly available to Uzbek banks
**Workaround**: Use Stripe → Wise or Stripe → Payoneer

**Setup**:
1. Enable Stripe Connect for your platform
2. Bloggers create Stripe accounts
3. Automate payouts via API

**Fees**:
- $0.25 per payout + 0.5%
- International: Additional 1%

---

### 4. **Cryptocurrency (USDT/USDC)**

**Why Consider**:
- ✅ Popular in regions with banking restrictions
- ✅ Very low fees ($1-5 regardless of amount)
- ✅ Fast transfers (minutes to hours)
- ✅ No banking intermediaries
- ✅ Stablecoins = no volatility

**Best Stablecoins**:
- **USDT (Tether)**: Most widely accepted
- **USDC (USD Coin)**: More regulated, backed 1:1

**Networks**:
- **TRC20 (Tron)**: ~$1 fee, very fast
- **BEP20 (Binance Smart Chain)**: ~$0.50 fee
- **ERC20 (Ethereum)**: Higher fees ($10-50), avoid unless necessary

**Setup for Bloggers**:
1. Download crypto wallet (Trust Wallet, MetaMask, or Binance)
2. Get wallet address for USDT or USDC (TRC20 recommended)
3. Share address with admin
4. Convert to local currency via:
   - Binance P2P (popular in Uzbekistan)
   - Local exchanges
   - Keep as USDT

**Setup for Admin**:
1. Create crypto wallet or exchange account
2. Buy USDT/USDC
3. Send to blogger wallets
4. Record transaction hash as reference

**Fees**: $1-5 total regardless of amount

**Risks**:
- Wallet address errors (irreversible)
- Exchange rate fluctuations (minimal with stablecoins)
- Learning curve for non-crypto users

---

### 5. **Bank Wire Transfer (SWIFT)**

**Why Last Resort**:
- ✅ Available everywhere
- ❌ High fees ($25-$50 per transfer)
- ❌ Slow (3-5 business days)
- ❌ Only practical for large amounts ($500+)

**Best For**: Large payouts to reduce fee percentage

**Setup**:
- Bloggers provide SWIFT/BIC code and IBAN
- Process through your bank
- Use for monthly bulk payouts to reduce frequency

**Fees**:
- Sending bank: $25-40
- Intermediate banks: $10-20
- Receiving bank: $5-15
- **Total**: $40-75 per transfer

---

## Recommended Strategy for Your Platform

### Phase 1: Initial Setup (Now)
**Primary Method**: Wise (TransferWise)
- Sign up for Wise Business
- Add as default option for bloggers
- Use manual batch payments

**Secondary Method**: Cryptocurrency (USDT TRC20)
- For tech-savvy bloggers
- Lower fees, faster transfers
- Popular in Uzbekistan

### Phase 2: Scale (After 50+ bloggers)
- Wise API integration for automation
- Add Payoneer as alternative
- Set up scheduled batch payments

### Phase 3: Full Automation (After 200+ bloggers)
- Fully automated via Wise API
- Stripe Connect integration (where available)
- Crypto payments via exchange API

---

## Implementation: Wise Integration

### Manual Process (Quick Start)

1. **Admin: Create Wise Business Account**
   ```
   - Visit https://wise.com/business/
   - Complete business verification
   - Add bank account or card for funding
   ```

2. **Process Weekly Batch**
   ```
   Monday 10 AM:
   1. Login to admin panel
   2. Filter: Status=Pending, Method=Wise
   3. Export CSV
   4. Upload to Wise Batch Payment
   5. Approve in Wise (review transfer details)
   6. Get batch ID from Wise
   7. Back to admin: Bulk approve with Wise batch ID
   8. Done! Payouts arrive in 1-2 days
   ```

### Automated Process (API)

**1. Install Wise PHP SDK**:
```bash
composer require wise/wise-api-php
```

**2. Create Wise Service** (`app/Services/WisePayoutService.php`):
```php
<?php

namespace App\Services;

use App\Models\PayoutRequest;
use Wise\WiseClient;

class WisePayoutService
{
    protected $client;

    public function __construct()
    {
        $this->client = new WiseClient([
            'api_token' => config('services.wise.api_token'),
            'environment' => config('services.wise.environment'), // 'live' or 'sandbox'
        ]);
    }

    public function processBatch(array $payoutRequests)
    {
        $transfers = [];

        foreach ($payoutRequests as $request) {
            $transfers[] = [
                'targetAccount' => $request->user->wise_recipient_id,
                'quoteId' => $this->createQuote($request->amount, 'USD'),
                'customerTransactionId' => 'PAYOUT-' . $request->id,
                'details' => [
                    'reference' => 'Blog earnings for ' . $request->user->name,
                ],
            ];
        }

        $batch = $this->client->createBatchPayment($transfers);

        return $batch;
    }

    protected function createQuote($amount, $currency)
    {
        // Create quote for transfer
        // Implementation details in Wise API docs
    }
}
```

**3. Add to `.env`**:
```env
WISE_API_TOKEN=your_wise_api_token_here
WISE_ENVIRONMENT=live
```

**4. Create Artisan Command**:
```bash
php artisan make:command ProcessWisePayouts
```

```php
// app/Console/Commands/ProcessWisePayouts.php
public function handle()
{
    $pending = PayoutRequest::where('status', 'pending')
        ->where('payout_method', 'wise')
        ->get();

    if ($pending->isEmpty()) {
        $this->info('No pending Wise payouts');
        return;
    }

    $service = new WisePayoutService();
    $batch = $service->processBatch($pending);

    foreach ($pending as $request) {
        $request->update([
            'status' => 'processing',
            'transaction_reference' => 'WISE-BATCH-' . $batch['id'],
        ]);
    }

    $this->info("Processed {$pending->count()} payouts via Wise");
}
```

**5. Schedule** (optional):
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Every Monday at 10 AM
    $schedule->command('wise:process-payouts')
        ->weeklyOn(1, '10:00');
}
```

---

## Blogger Instructions by Method

### For Wise Users

**Email Template to Send Bloggers**:
```
Subject: Set Up Wise for Fast International Payouts

Hi [Blogger Name],

To receive your blog earnings quickly and with low fees, please set up a Wise account:

1. Sign up at https://wise.com (it's free)
2. Complete identity verification (takes 5-10 minutes)
3. Add your local bank account in Uzbekistan
4. Reply with your Wise email address

Payouts via Wise typically arrive in 1-2 business days with fees of only 1-2%.

Questions? Reply to this email.

Best regards,
[Your Platform Name]
```

### For Crypto Users

**Email Template**:
```
Subject: Receive Payouts via Cryptocurrency (USDT)

Hi [Blogger Name],

You've selected cryptocurrency as your payout method. Here's how to set it up:

1. Download Trust Wallet app (iOS/Android)
   Or use: MetaMask, Binance, or any wallet supporting USDT

2. Create wallet and write down your recovery phrase (IMPORTANT!)

3. Find your USDT (TRC20) wallet address:
   - In Trust Wallet: Tap "Tron" → "Receive" → Copy address

4. Reply with your USDT (TRC20) wallet address

⚠️ Important:
- Only share TRC20 address (lowest fees ~$1)
- Double-check address before sending (errors are irreversible)
- Keep recovery phrase safe (never share it)

To convert USDT to UZS:
- Use Binance P2P: https://p2p.binance.com
- Or local exchanges

Questions? Reply to this email.

Best regards,
[Your Platform Name]
```

---

## Fee Comparison

| Method | Fee Structure | $50 Payout | $500 Payout | Time | Best For |
|--------|--------------|-----------|-------------|------|----------|
| **Wise** | 0.5-2% + $0.50 | $1.50 | $10-15 | 1-2 days | Everyone (Recommended) |
| **Payoneer** | Free receive + $1.50 withdrawal | $1.50 | $1.50 | 2-5 days | Medium/Large payouts |
| **Stripe** | $0.25 + 0.5% + 1% intl | $1.00 | $8.00 | 1-2 days | Where available |
| **Crypto (USDT)** | ~$1-3 flat | $1.50 | $1.50 | Minutes-Hours | Tech-savvy users |
| **Bank Wire** | $40-75 flat | $40+ (80%!) | $40-75 (8-15%) | 3-5 days | Large payouts only |

**Winner for Uzbekistan**: Wise for most users, Crypto for tech-savvy

---

## Tax & Compliance

### For Uzbekistan

**Blogger Requirements**:
- Income over certain threshold must be declared
- Consult local tax advisor
- Keep records of all payouts

**Platform Requirements**:
- Track all payouts for accounting
- Export CSV quarterly for records
- May need to provide payout statements to bloggers

### International Best Practices

1. **Track Everything**: Use admin panel export feature
2. **Provide Statements**: Bloggers can download payout history
3. **Annual Reporting**: Export full year for tax preparation
4. **1099 Forms** (if applicable): For US-based bloggers over $600/year

---

## Troubleshooting

### Wise Issues

**Problem**: Blogger can't create Wise account
**Solution**: Check if Uzbekistan restrictions, use Payoneer or crypto instead

**Problem**: Transfer delayed
**Solution**: Usually resolves within 1-2 business days, check Wise status

### Crypto Issues

**Problem**: Blogger sent wrong network address
**Solution**: ⚠️ Funds may be lost! Always verify network (TRC20, BEP20, etc.)

**Problem**: High fees
**Solution**: Use TRC20 network (~$1) instead of ERC20 (~$30)

### General Issues

**Problem**: Blogger doesn't receive payout
**Solution**: Check transaction reference, verify account details, contact payment provider

---

## Migration from Old System

If you had existing `bank_transfer` or `paypal` entries:

**Migration included "bank_transfer" in new constraint** - old records still work!

To update existing payouts:
```sql
-- Update old bank_transfer to wise (if they were actually Wise)
UPDATE payout_requests
SET payout_method = 'wise'
WHERE payout_method = 'bank_transfer'
AND notes LIKE '%wise%';
```

---

## Monthly Workflow Example

**Scenario**: 100 bloggers in Uzbekistan, various payment preferences

**Week 1 (Wise - 60 bloggers)**:
- Monday 10 AM: Filter Wise payouts
- Export CSV
- Upload to Wise batch payment
- Approve (15 minutes)
- Bulk approve in admin
- **Time**: 20 minutes, **Fees**: ~$150 total

**Week 2 (Crypto - 30 bloggers)**:
- Wednesday: Filter crypto payouts
- Process USDT transfers via exchange or wallet
- Record transaction hashes
- Bulk approve in admin
- **Time**: 30 minutes, **Fees**: ~$30 total

**Week 3 (Payoneer - 10 bloggers)**:
- Friday: Filter Payoneer payouts
- Upload to Payoneer mass payout
- Approve
- **Time**: 15 minutes, **Fees**: ~$15 total

**Monthly Total**:
- **Time**: ~1.5 hours for 100 bloggers
- **Fees**: ~$195 total ($1.95 per blogger average)
- **Compare to**: Bank wires would cost $4000-7500!

---

## Recommendation for Your Platform

### Immediate Setup (This Week)

1. **Create Wise Business Account**
   - Primary method for 80% of bloggers
   - Lowest friction, highest trust

2. **Enable Crypto (USDT TRC20)**
   - For 10-15% tech-savvy bloggers
   - Create admin wallet on Binance or Trust Wallet

3. **Keep Bank Wire**
   - For outliers or special cases

### Update Blogger Onboarding

Add to blogger signup/settings:
```
Payment Preferences:
□ Wise (Recommended) - 1-2 days, low fees
□ Crypto (USDT) - Minutes, lowest fees
□ Payoneer - 2-5 days
□ Bank Wire - 3-5 days, high fees (large amounts only)
```

### Communication to Bloggers

**Subject**: New International Payment Options Available

```
Hi Bloggers,

Good news! We've added more payout options including:

✅ Wise (TransferWise) - RECOMMENDED
   • Available in Uzbekistan
   • 1-2 day transfers
   • Only 1-2% fees

✅ Crypto (USDT)
   • For tech-savvy users
   • Instant transfers
   • ~$1 flat fee

Update your payment preferences in your dashboard under:
Settings → Payment Methods

Questions? Reply to this email.

Happy blogging!
[Your Platform]
```

---

## Support Resources

**Wise**:
- Business signup: https://wise.com/business
- API docs: https://api-docs.wise.com
- Support: https://wise.com/help

**Payoneer**:
- Business signup: https://payoneer.com/business
- Mass payout: https://payoneer.com/mass-payout
- Support: https://payoneer.com/support

**Crypto Resources**:
- Trust Wallet: https://trustwallet.com
- Binance P2P (Uzbekistan): https://p2p.binance.com
- USDT guide: https://tether.to/en/how-it-works

---

## Conclusion

**Best Strategy for Uzbekistan**:
1. **Primary**: Wise (80% of bloggers) - Lowest friction, good fees
2. **Secondary**: Crypto USDT (15% of bloggers) - Tech-savvy, lowest fees
3. **Backup**: Payoneer (5% of bloggers) - Alternative

**Expected Costs**:
- Per $100 payout via Wise: ~$2
- Per $100 payout via crypto: ~$1
- **Average**: ~$1.50 per payout (vs $40-75 for bank wires!)

Start with Wise manual batches this week, automate later as volume grows.
