<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">
    <channel>
        <title>{{ config('app.name') }}</title>
        <link>{{ config('app.url') }}</link>
        <description>{{ setting('site_description') }}</description>
        <language>en-us</language>
        <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>
        <atom:link href="{{ url('/feed.xml') }}" rel="self" type="application/rss+xml"/>

        @foreach($posts as $post)
        <item>
            <title><![CDATA[{{ $post->title }}]]></title>
            <link>{{ route('posts.show', $post->slug) }}</link>
            <description><![CDATA[{{ $post->excerpt }}]]></description>
            <content:encoded><![CDATA[{!! $post->content !!}]]></content:encoded>
            <author>{{ $post->author->email }} ({{ $post->author->name }})</author>
            <category>{{ $post->category->name }}</category>
            <pubDate>{{ $post->published_at->toRssString() }}</pubDate>
            <guid isPermaLink="true">{{ route('posts.show', $post->slug) }}</guid>
        </item>
        @endforeach
    </channel>
</rss>
