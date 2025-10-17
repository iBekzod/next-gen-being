#!/bin/bash

# Newsletter System - Complete Implementation Script
# Run this on your server after reviewing the NEWSLETTER_IMPLEMENTATION_PLAN.md

echo "🚀 Setting up Newsletter System for Next Gen Being..."

# Navigate to project directory
cd /var/www/nextgenbeing

# 1. Run migrations
echo "📊 Running database migrations..."
php artisan migrate

# 2. Create remaining email templates
echo "📧 Creating email templates..."

# Premium Teaser Template
cat > resources/views/emails/newsletter/premium-teaser.blade.php << 'EOTEMPLATE'
@extends('emails.newsletter.layouts.base')

@section('content')
<h1>✨ Unlock Exclusive Premium Content</h1>

<p>Hi there!</p>

<p>You're missing out on some incredible insights. Our premium members get access to in-depth articles, tutorials, and exclusive content that can accelerate your learning.</p>

@if($premiumPosts->isNotEmpty())
    <h2>Here's what you're missing this week:</h2>

    @foreach($premiumPosts as $post)
        <div class="post-card" style="position: relative;">
            @if($post->featured_image)
                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" style="width: 100%; height: auto; border-radius: 6px; margin-bottom: 15px; opacity: 0.6;">
            @endif

            <div class="post-meta">
                <span class="category-badge">{{ $post->category->name }}</span>
                <span class="premium-badge">Premium</span>
                <span style="color: #a0aec0;">• {{ $post->read_time }} min read</span>
            </div>

            <h3>🔒 {{ $post->title }}</h3>

            <p style="color: #718096;">{{ Str::limit($post->excerpt, 120) }}</p>

            <p style="font-size: 14px; color: #a0aec0; font-style: italic;">
                Upgrade to read the full article...
            </p>
        </div>
    @endforeach
@endif

<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 40px; margin: 40px 0; text-align: center; color: white;">
    <h2 style="color: white; margin-top: 0;">🎁 Special Offer for You</h2>

    <p style="color: #e0e7ff; font-size: 18px; margin-bottom: 10px;">
        <strong>Get 20% OFF Premium Membership</strong>
    </p>

    <p style="color: #c7d2fe; margin-bottom: 30px;">
        Use code <span style="background-color: rgba(255,255,255,0.2); padding: 4px 12px; border-radius: 4px; font-weight: 600;">NEWSLETTER20</span> at checkout
    </p>

    <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
        <tr>
            <td>
                <a href="{{ isset($subscriptionUrl) ? $subscriptionUrl : url('/subscription/plans') }}"
                   style="display: inline-block; padding: 18px 48px; background-color: white; color: #667eea; text-decoration: none; border-radius: 8px; font-weight: 700; font-size: 18px;">
                    Upgrade Now →
                </a>
            </td>
        </tr>
    </table>

    <p style="color: #c7d2fe; font-size: 14px; margin-top: 20px; margin-bottom: 0;">
        ✓ Cancel anytime • ✓ 30-day money-back guarantee • ✓ Instant access
    </p>
</div>

<div style="background-color: #f7fafc; border-radius: 8px; padding: 30px; margin: 30px 0;">
    <h3 style="margin-top: 0;">What Premium Members Get:</h3>
    <ul style="margin: 10px 0; padding-left: 20px; color: #4a5568;">
        <li style="margin-bottom: 10px;">📚 Access to 50+ premium articles and tutorials</li>
        <li style="margin-bottom: 10px;">🎯 Advanced topics and in-depth guides</li>
        <li style="margin-bottom: 10px;">💾 Downloadable resources and code templates</li>
        <li style="margin-bottom: 10px;">⚡ Early access to new content</li>
        <li style="margin-bottom: 10px;">🎓 Learning paths and structured courses</li>
        <li style="margin-bottom: 10px;">💬 Priority support from our team</li>
    </ul>
</div>

<div style="text-align: center; padding: 20px 0;">
    <p style="font-size: 16px; color: #2d3748; margin-bottom: 15px;">
        <strong>Join 10,000+ premium members who are accelerating their learning.</strong>
    </p>
    <p style="font-size: 14px; color: #718096;">
        "Best investment I made this year. The premium content is gold!" - Sarah K.
    </p>
</div>

@endsection

@section('footer')
<p style="font-size: 12px; color: #a0aec0;">
    Not interested? No problem! You'll continue receiving our free newsletter.
</p>
@endsection
EOTEMPLATE

# Email Wrapper Template
cat > resources/views/emails/newsletter/wrapper.blade.php << 'EOTEMPLATE'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    {!! $content !!}
</body>
</html>
EOTEMPLATE

echo "✅ Email templates created"

# 3. Create Livewire Components
echo "🎨 Creating Livewire components..."

php artisan make:livewire NewsletterSubscribe
php artisan make:livewire NewsletterPreferences

# 4. Create Controller
echo "🎛️  Creating Newsletter Controller..."
php artisan make:controller NewsletterController

# 5. Create Commands
echo "⚙️  Creating automation commands..."
php artisan make:command SendWeeklyNewsletter
php artisan make:command CleanupNewsletterData

# 6. Create Job
echo "📦 Creating newsletter job..."
php artisan make:job SendNewsletterEmail

# 7. Create Mail classes
echo "📮 Creating mailable classes..."
php artisan make:mail NewsletterVerification
php artisan make:mail WeeklyDigest
php artisan make:mail PremiumTeaser

# 8. Create Filament Resources
echo "🎛️  Creating Filament admin resources..."
php artisan make:filament-resource NewsletterSubscription --generate
php artisan make:filament-resource NewsletterCampaign --generate

echo ""
echo "✅ Base structure created!"
echo ""
echo "📝 NEXT STEPS:"
echo "1. Review and customize the generated files in:"
echo "   - app/Http/Livewire/"
echo "   - app/Http/Controllers/NewsletterController.php"
echo "   - app/Console/Commands/"
echo "   - app/Mail/"
echo "   - app/Filament/Resources/"
echo ""
echo "2. Add routes to routes/web.php (see NEWSLETTER_IMPLEMENTATION_PLAN.md)"
echo "3. Update Livewire component views"
echo "4. Test the subscription flow"
echo "5. Schedule the newsletter command in routes/console.php"
echo ""
echo "📚 Full implementation details in: NEWSLETTER_IMPLEMENTATION_PLAN.md"
echo ""
echo "🎉 Newsletter system setup complete!"

EOTEMPLATE

chmod +x setup-newsletter-system.sh

echo "✅ Setup script created: setup-newsletter-system.sh"
