<x-mail::message>
# You Received a Tip! 🎉

Hello {{ $recipient->name }},

Great news! You just received a tip from a reader.

<x-mail::panel>
**Tip Amount:** ${{ number_format($amount, 2) }}

@if($message)
**Message from Tipper:** {{ $message }}
@endif

@if($post)
**On Post:** [{{ $post->title }}]({{ config('app.url') }}/posts/{{ $post->slug }})
@endif
</x-mail::panel>

This is a wonderful way to connect with your readers and grow your earnings. Thank you for creating great content!

Your total earnings: ${{ number_format($totalEarnings, 2) }}

<x-mail::button :url="config('app.url') . '/dashboard/earnings'">
View Your Earnings
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
