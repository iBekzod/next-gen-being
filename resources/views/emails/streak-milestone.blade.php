<x-mail::message>
# 🔥 Streak Milestone Reached!

Hello {{ $user->name }},

Congratulations! You've reached an amazing milestone!

<x-mail::panel>
**Streak Type:** {{ ucfirst($streakType) }}

**Current Streak:** {{ $currentStreak }} days

**Longest Streak:** {{ $longestStreak }} days

**Achievement Unlocked:** 🏆
</x-mail::panel>

You're building amazing consistency! Keep up the momentum to reach even higher streaks.

Your streaks:
- **Reading:** {{ $readingStreak }} days
- **Writing:** {{ $writingStreak }} days

<x-mail::button :url="config('app.url') . '/dashboard/streaks'">
View Your Streaks
</x-mail::button>

Stay focused and keep the streak alive!<br>
{{ config('app.name') }}
</x-mail::message>
