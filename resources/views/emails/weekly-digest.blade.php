<x-mail::message>
# 📬 Your Weekly Digest

Hello {{ $user->name }},

Here's what happened in your community this week!

<x-mail::panel>
## Your Activity
- **Posts Published:** {{ $postsPublished }}
- **Total Views:** {{ $totalViews }}
- **Engagement:** {{ $totalEngagement }} interactions
- **Earnings:** ${{ number_format($earnings, 2) }}
</x-mail::panel>

<x-mail::panel>
## This Week's Highlights
- **Trending Post:** [{{ $trendingPost->title }}]({{ config('app.url') }}/posts/{{ $trendingPost->slug }}) ({{ $trendingPost->views }} views)
- **New Followers:** {{ $newFollowers }}
- **Top Tip Amount:** ${{ number_format($topTipAmount, 2) }}
</x-mail::panel>

<x-mail::panel>
## Community Stats
- **Active Users:** {{ $activeUsers }}
- **New Posts:** {{ $newPosts }}
- **Community Engagement:** {{ $communityEngagement }}%
</x-mail::panel>

<x-mail::button :url="config('app.url') . '/dashboard'">
View Full Dashboard
</x-mail::button>

Thanks for being part of our community!<br>
{{ config('app.name') }}
</x-mail::message>
