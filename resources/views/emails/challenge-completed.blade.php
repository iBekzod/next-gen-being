<x-mail::message>
# 🎊 Challenge Completed!

Hello {{ $user->name }},

Excellent work! You've successfully completed a challenge!

<x-mail::panel>
**Challenge:** {{ $challenge->title }}

**Difficulty:** {{ ucfirst($challenge->difficulty) }}

**Reward Points:** +{{ $challenge->reward_points }} points

**Completion Date:** {{ now()->format('M d, Y') }}
</x-mail::panel>

You've demonstrated incredible commitment! Here are your current stats:

- **Challenges Completed:** {{ $completedChallenges }}
- **Total Reward Points:** {{ $totalPoints }}
- **Current Rank:** {{ $userRank }}

Ready for your next challenge?

<x-mail::button :url="config('app.url') . '/challenges'">
Browse More Challenges
</x-mail::button>

Keep pushing yourself to greatness!<br>
{{ config('app.name') }}
</x-mail::message>
