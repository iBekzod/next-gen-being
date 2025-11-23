<x-mail::message>
# 🚀 Your Post Is Trending!

Hello {{ $user->name }},

Amazing news! One of your posts is trending!

<x-mail::panel>
**Post:** [{{ $post->title }}]({{ config('app.url') }}/posts/{{ $post->slug }})

**Views:** {{ $post->views }}
**Likes:** {{ $post->likes }}
**Comments:** {{ $post->comments }}
**Shares:** {{ $post->shares }}

**Trending Position:** #{{ $position }}
</x-mail::panel>

Your content is resonating with readers! Here are some insights:

- **Top Comment:** "{{ Str::limit($topComment, 100) }}"
- **Top Referrer:** {{ $topReferrer }}
- **Estimated Reach:** {{ $estimatedReach }} people

Keep creating amazing content!

<x-mail::button :url="config('app.url') . '/posts/' . $post->slug">
View Your Trending Post
</x-mail::button>

<x-mail::button :url="config('app.url') . '/dashboard/analytics'">
View Full Analytics
</x-mail::button>

Your hard work is paying off!<br>
{{ config('app.name') }}
</x-mail::message>
