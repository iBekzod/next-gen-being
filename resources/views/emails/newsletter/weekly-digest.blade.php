@extends('emails.newsletter.layouts.base')

@section('content')
<h1>This Week's Top Articles</h1>

<p>Here are the most popular articles from {{ config('app.name') }} this week. Dive in!</p>

@if($topPosts->isNotEmpty())
    {{-- Featured Post --}}
    @php $featuredPost = $topPosts->first(); @endphp
    <div class="post-card post-card-featured" style="margin: 30px 0;">
        @if($featuredPost->featured_image)
            <img src="{{ $featuredPost->featured_image }}" alt="{{ $featuredPost->title }}" style="width: 100%; height: auto; border-radius: 6px; margin-bottom: 15px;">
        @endif

        <div class="post-meta">
            <span class="category-badge">{{ $featuredPost->category->name }}</span>
            @if($featuredPost->is_premium)
                <span class="premium-badge">Premium</span>
            @endif
            <span style="color: #a0aec0;">• {{ $featuredPost->read_time }} min read</span>
        </div>

        <h2 style="margin: 15px 0 10px 0;">{{ $featuredPost->title }}</h2>

        <p style="color: #4a5568; margin-bottom: 20px;">{{ $featuredPost->excerpt }}</p>

        <table role="presentation" cellspacing="0" cellpadding="0">
            <tr>
                <td>
                    <a href="{{ url('/posts/' . $featuredPost->slug) }}" class="button">
                        Read Article →
                    </a>
                </td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    {{-- Other Top Posts --}}
    @if($topPosts->count() > 1)
        <h2 style="margin-top: 40px;">More Great Reads</h2>

        @foreach($topPosts->skip(1) as $post)
            <div class="post-card">
                <div class="post-meta">
                    <span class="category-badge">{{ $post->category->name }}</span>
                    @if($post->is_premium)
                        <span class="premium-badge">Premium</span>
                    @endif
                    <span style="color: #a0aec0;">• {{ $post->read_time }} min read</span>
                </div>

                <h3>
                    <a href="{{ url('/posts/' . $post->slug) }}" style="color: #1a202c; text-decoration: none;">
                        {{ $post->title }}
                    </a>
                </h3>

                <p style="font-size: 14px; color: #718096; margin-bottom: 15px;">
                    {{ Str::limit($post->excerpt, 150) }}
                </p>

                <a href="{{ url('/posts/' . $post->slug) }}" style="color: #667eea; font-weight: 600; text-decoration: none;">
                    Read more →
                </a>
            </div>
        @endforeach
    @endif
@endif

{{-- Premium Teaser Section --}}
@if($premiumPosts->isNotEmpty())
    <div class="divider"></div>

    <div style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 8px; padding: 30px; margin: 40px 0; text-align: center;">
        <h2 style="margin-top: 0; color: #92400e;">🌟 Unlock Premium Content</h2>

        <p style="color: #78350f; font-size: 16px;">
            Get access to {{ $premiumPosts->count() }} new premium articles this week, plus 50+ more exclusive insights.
        </p>

        <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 20px auto;">
            <tr>
                <td>
                    <a href="{{ url('/subscription/plans') }}" class="button button-secondary button-large">
                        Upgrade to Premium
                    </a>
                </td>
            </tr>
        </table>

        <p style="font-size: 14px; color: #92400e; margin-bottom: 0;">
            Join 10,000+ premium members • Cancel anytime
        </p>
    </div>

    {{-- Show premium post titles --}}
    <div style="margin: 20px 0;">
        <p style="font-size: 14px; color: #718096; margin-bottom: 15px;">
            <strong>This week's premium articles:</strong>
        </p>
        @foreach($premiumPosts as $premiumPost)
            <p style="margin: 10px 0;">
                🔒 <strong>{{ $premiumPost->title }}</strong><br>
                <span style="font-size: 13px; color: #a0aec0;">
                    {{ $premiumPost->category->name }} • {{ $premiumPost->read_time }} min read
                </span>
            </p>
        @endforeach
    </div>
@endif

{{-- Browse by Category --}}
@if(isset($categories) && $categories->isNotEmpty())
    <div class="divider"></div>

    <h2>Browse by Topic</h2>
    <p style="color: #718096;">Explore more articles in your favorite categories:</p>

    <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 20px 0;">
        @foreach($categories->chunk(2) as $categoryChunk)
            <tr>
                @foreach($categoryChunk as $category)
                    <td style="padding: 10px;">
                        <a href="{{ url('/category/' . $category->slug) }}"
                           style="display: inline-block; padding: 12px 20px; background-color: #edf2f7; color: #2d3748; text-decoration: none; border-radius: 6px; font-weight: 600;">
                            {{ $category->name }}
                        </a>
                    </td>
                @endforeach
            </tr>
        @endforeach
    </table>
@endif

<div class="divider"></div>

{{-- Social Proof / Call to Action --}}
<div style="text-align: center; padding: 30px 0;">
    <p style="font-size: 18px; font-weight: 600; color: #2d3748; margin-bottom: 10px;">
        Enjoying our newsletter?
    </p>
    <p style="color: #718096; margin-bottom: 20px;">
        Forward this to a friend or colleague who might find it useful!
    </p>
    <table role="presentation" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
        <tr>
            <td>
                <a href="{{ config('app.url') }}" class="button">
                    Share {{ config('app.name') }}
                </a>
            </td>
        </tr>
    </table>
</div>

@endsection

@section('footer')
<p style="font-size: 12px; color: #a0aec0;">
    You're receiving this because you subscribed to our weekly newsletter.
</p>
@endsection
