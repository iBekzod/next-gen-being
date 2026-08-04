# Nexus Protocol — Build Prompts (Crypto / Web3 WebGL Landing)

A step-by-step sequence of Claude Code prompts to reproduce this exact single-file landing page.

## 1. Scaffold the document
"Create one self-contained `index.html` (doctype through `</html>`). In the head put charset, viewport, a title, and exactly this script tag: the three.js r128 min.js from cdnjs. No other external requests. System font stack only. No emoji anywhere — all icons are inline SVG with stroke=currentColor, stroke-width 1.8."

## 2. Design tokens
"Add a `:root` block of OKLCH custom properties on a tinted obsidian background. Accent is electric violet at hue 262 (#7B5CFF) blending into a cyan (#35E5FF, hue 205). Define glass fills, hairline border colors, ink/muted/faint text. Headlines use system-ui, font-weight 800, letter-spacing -0.04em, clamp sizing."

## 3. Backdrop layers
"Add fixed full-screen `<canvas id=webgl>` and `<canvas id=fallback>` at z-index 0, a `.veil` radial-gradient overlay at z-index 1, and a `.page` wrapper at z-index 2."

## 4. Glass nav
"Build a fixed nav with backdrop-blur and a hairline bottom border: an inline-SVG hexagon-node logo reading Nexus, three anchor links (Protocol, Network, Developers), and a gradient Launch App CTA button."

## 5. Hero
"Build `<header class=hero>` min-height 100svh, centered. Include an eyebrow pill (Mainnet live, 1.2s finality), a kinetic h1 with two lines — 'Own your assets.' / 'Own your future.' — each wrapped `<span class=line><span>text</span></span>`, a lead paragraph, two CTAs (Start Building, Explore the Network), and a scroll-to-explore hint pinned to the bottom."

## 6. Features + stats
"Add a three-card glass grid for Sub-Second Finality, Non-Custodial Wallet, and Cross-Chain Bridges, each with a line-icon tile, real persuasive copy, and a hover lift. Below it add a four-cell stat row: 1.2s finality, $0.0004 gas, 28,000 TPS, $4.6B settled."

## 7. Interactive network explorer
"Add a tabbed 'Live network' section with three tabs (Consensus, Settlement, Developers). Each panel shows a heading, description, a checklist with SVG check icons, and a rotating concentric-ring viz with a headline metric. Wire real JS: clicking a tab toggles aria-selected and swaps the active panel."

## 8. CTA band + footer
"Add a glass CTA band headlined 'Fast, final, and fully on-chain.' with two buttons, then a footer with the logo, a tagline, three link columns (Protocol, Build, Company), and a legal copy row."

## 9. WebGL scene — init3D()
"Using window.THREE, render a glowing wireframe icosahedron at center with a custom fresnel-rim ShaderMaterial (violet-to-cyan), a wireframe overlay, 14 orbiting satellite node spheres, line edges from center to nodes and between neighbors, and additive-blended data packets that travel along the edges. Add background star particles. Drive rotation, scale, and rim color from scroll progress (0..1) plus scroll velocity; lerp the mouse to camera position for parallax. Animate with THREE.Clock."

## 10. Fallback + motion gating
"Implement initFallback() that draws an animated multi-blob canvas aurora when THREE or WebGL is unavailable. Feature-detect WebGL and call the right one. Add a setTimeout ~140ms after load that adds `.go` to the h1 for the staggered reveal, an IntersectionObserver mapping `.rv` to `.in`, and honor prefers-reduced-motion by rendering a single static frame with no rAF loop. Keep everything in one closed script, no console errors, roughly 14-24 KB."