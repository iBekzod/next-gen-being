<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
      xmlns:atom="http://www.w3.org/2005/Atom"
      xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>{{ config('app.name') }}</title>
        <link>{{ url('/') }}</link>
        <description>Latest posts from {{ config('app.name') }}</description>
        <language>en-us</language>
        <lastBuildDate>{{ $lastBuildDate->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ route('feed.rss') }}" rel="self" type="application/rss+xml" />

        @forelse ($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ route('posts.show', $post->slug) }}</link>
                <guid>{{ route('posts.show', $post->slug) }}</guid>
                <pubDate>{{ $post->published_at->toRfc2822String() }}</pubDate>
                <dc:creator xmlns:dc="http://purl.org/dc/elements/1.1/">{{ $post->author->name }}</dc:creator>
                @if ($post->category)
                    <category>{{ $post->category->name }}</category>
                @endif
                <description>{{ htmlspecialchars($post->excerpt, ENT_QUOTES, 'UTF-8') }}</description>
                <content:encoded><![CDATA[
                    {!! $post->content !!}
                ]]></content:encoded>
            </item>
        @empty
        @endforelse
    </channel>
</rss>
