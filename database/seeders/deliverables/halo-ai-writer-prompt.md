# Halo — Prompt Plan (build it with Claude Code)

Prompts to build the editorial AI writing assistant UI from the live demo, including the real streaming animation.

**Stack:** single self-contained `index.html` (inline CSS + vanilla JS). Wire the composer to any streaming LLM endpoint later.

---

## 1. Two-tone layout
> Create a single-file AI chat UI `index.html`. Warm paper background `oklch(0.985 0.006 95)`, a dark ink left rail `oklch(0.225 0.014 260)`, and a confident coral accent `oklch(0.605 0.17 27)` (avoid the generic dark-chat look). Rail: logo, "New chat", recent conversation list, user chip. Main: chat header, thread, composer.

## 2. Empty state & suggested prompts
> Add a centered empty state with a halo mark, a headline, and a 2×2 grid of suggested-prompt cards. Clicking a card submits that prompt.

## 3. Streaming responses
> Build the message thread with user (right, ink avatar) and assistant (left, gradient avatar) messages. When a message is sent: show the user bubble, then a typing indicator, then stream the assistant reply token-by-token into the DOM (tokenize the HTML, reveal tags whole and words in small random bursts, with a blinking caret). Auto-scroll during streaming.

## 4. Composer
> Add an auto-growing textarea (max ~120px), Enter-to-send / Shift+Enter for newline, a send button that disables while streaming or empty, and a model badge in the header.

## 5. Wire a backend (optional)
> Replace the canned responses with a fetch to a streaming endpoint (SSE or chunked). Keep the same token-reveal function so the animation is identical for real responses.

---

**Tips**
- The token-by-token reveal is the whole feel — keep the interval ~45ms and vary how many words drip per tick.
- Store conversations client-side first; add persistence later.
- Coral + warm paper is the differentiator; don't default to dark mode.

*Included with your purchase. Questions? Reply to your receipt email.*
