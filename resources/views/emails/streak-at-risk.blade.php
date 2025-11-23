<x-mail::message>
# ⚠️ Your Streak Is At Risk!

Hello {{ $user->name }},

Your {{ $streakType }} streak is about to be lost!

<x-mail::panel>
**Current Streak:** {{ $currentStreak }} days

**Time Until Streak Breaks:** {{ $hoursRemaining }} hours

**Last Activity:** {{ $lastActivityDate->diffForHumans() }}
</x-mail::panel>

Don't lose your streak now! Complete an activity before it's too late.

@if($streakType === 'reading')
<x-mail::button :url="config('app.url') . '/posts'">
Browse Posts to Read
</x-mail::button>
@else
<x-mail::button :url="config('app.url') . '/editor'">
Write Your Next Post
</x-mail::button>
@endif

Act fast, you've got {{ $hoursRemaining }} hours left!<br>
{{ config('app.name') }}
</x-mail::message>
