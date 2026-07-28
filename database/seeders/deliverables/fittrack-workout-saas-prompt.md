# FitTrack — Prompt Plan (build it with Claude Code)

A step-by-step prompt sequence to build the FitTrack workout & habit-tracking SaaS you saw in the live demo. Paste each prompt into Claude Code in order.

**Stack:** Laravel 12 + Blade + Tailwind + Alpine.js, PostgreSQL, Lemon Squeezy for billing.

---

## 0. Scaffold
> Create a new Laravel 12 project called "fittrack". Add Tailwind and Alpine.js. Set up a Postgres connection and a `users` table with the default auth scaffolding (Breeze, Blade stack). Add a dark-mode toggle stored in localStorage.

## 1. Data model
> Add models + migrations: `Workout` (user_id, title, type[cardio|strength|recovery], duration_min, kcal, done_at nullable), `HabitDay` (user_id, date, completed_count, goal_count). Add factories and a seeder with a week of realistic sample data for a demo user.

## 2. Today dashboard
> Build a `/dashboard` page (Blade + Alpine) that shows today's goal as an animated SVG progress ring (percent of workouts completed), and a list of today's workouts as cards with a circular checkbox. Clicking a card toggles `done_at` via a Livewire/Alpine fetch and updates the ring live. Match this palette: deep indigo background, electric-blue accent, green for completed. Use tabular-nums for stats.

## 3. Streaks & stats
> Add a Stats tab: a 7-day bar chart of calories burned (inline SVG, no chart library), current streak counter, and weekly totals. Compute streaks from `HabitDay` server-side.

## 4. Billing (Pro plan)
> Integrate `lemonsqueezy/laravel`. Add a Pro plan ($9/mo) that unlocks unlimited workout history. Gate history older than 14 days behind Pro. Handle the `subscription_created` and `subscription_cancelled` webhooks to flip a `is_pro` flag on the user.

## 5. Polish
> Add empty states, a mobile-responsive sidebar that collapses to a bottom tab bar, keyboard focus states, and `prefers-reduced-motion` handling for the ring animation.

---

**Tips**
- Keep the ring as one reusable Blade component; drive it from a single `percent` prop.
- Do the streak math in a dedicated `StreakService` so it's testable.
- Seed realistic demo data so the dashboard never looks empty in screenshots.

*Included with your purchase. Questions? Reply to your receipt email.*
