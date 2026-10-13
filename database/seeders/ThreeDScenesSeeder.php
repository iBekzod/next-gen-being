<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\SeedsMarketplaceListing;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * "3D Scenes" category (category slug `3d-scenes`): three original three.js hero
 * scenes. Each listing has a live static demo (database/seeders/demos/{slug}.html),
 * a poster image for the grid (database/seeders/posters/{slug}.jpg) so WebGL only
 * ever loads on the item page, and a gated deliverable:
 *
 *  - free scene: `prompt` tier = email-gated magnet, one .md file with the master
 *    prompt AND the full source appended (see {{SOURCE}} in the prompt file);
 *  - paid scenes: one `design` tier at $4 = ZIP with PROMPT.md + scene.html + README.
 *
 * Idempotent: safe to run on every deploy. No ratings or sales are ever seeded.
 */
class ThreeDScenesSeeder extends Seeder
{
    use SeedsMarketplaceListing;

    public const CATEGORY = '3d-scenes';
    public const PRICE = 4;

    /** @return list<array{listing: array, tiers: array}> */
    public static function scenes(): array
    {
        return [
            [
                'listing' => [
                    'slug'        => 'stellar-drift-galaxy-hero',
                    'title'       => 'Stellar Drift — Particle Galaxy Hero',
                    'tagline'     => 'Cursor-reactive galaxy hero, free',
                    'category'    => self::CATEGORY,
                    'tags'        => ['three.js', 'webgl', 'particles', 'hero', 'galaxy', 'free'],
                    'description' => "A free particle galaxy hero for your website: 90,000 GPU stars, a cursor gravity well and click shockwaves, as a Claude prompt plus full source.\n\n".
                        "Stellar Drift draws a whole spiral galaxy in a single call. The shape, the differential rotation and every effect are computed in a vertex shader, so the CPU does almost nothing per frame. Move the pointer and a gravity well swirls the stars around it. Click or tap and a shockwave rolls through the arms. On phones, where there is no hover, the well wanders on its own so the hero never looks dead.\n\n".
                        "Built for real pages, not just for a demo reel:\n".
                        "- Pixel ratio capped, fewer stars on touch screens, and adaptive quality that backs off by itself when frames get slow\n".
                        "- Pauses when scrolled out of view or when the tab is hidden\n".
                        "- Respects prefers-reduced-motion: visitors get one still frame instead of motion\n".
                        "- Lightweight CSS fallback if WebGL or the 3D library is unavailable\n".
                        "- Three palettes (aurora, ember, glacier) wired to swatches\n".
                        "- One HTML file, three.js r160, no build step\n\n".
                        "What you get, free for your email:\n".
                        "- The master prompt to recreate the scene with Claude, Cursor, v0 or Lovable\n".
                        "- Six one-line customisation prompts, a tuning table and a pre-launch checklist\n".
                        "- The complete, commented source file inside the same document",
                ],
                'tiers' => [
                    ['tier' => 'prompt', 'title' => 'Stellar Drift — Prompt + Source', 'price' => 0, 'type' => 'prompt', 'desc' => 'Master prompt, customisation prompts and the full source. Free for your email.'],
                ],
            ],
            [
                'listing' => [
                    'slug'        => 'liquid-glass-blob-hero',
                    'title'       => 'Liquid Glass — Morphing Blob Hero',
                    'tagline'     => 'Morphing liquid-glass hero blob',
                    'category'    => self::CATEGORY,
                    'tags'        => ['three.js', 'webgl', 'shader', 'hero', 'glassmorphism', 'blob'],
                    'description' => "A morphing liquid-glass blob hero: it refracts a living gradient and leans toward your cursor. Claude prompt plus full source.\n\n".
                        "Liquid Glass is a single shader-driven blob. The surface is a displaced icosphere with analytic normals; the glass is faked in the fragment shader by refracting the same gradient that is painted behind it, with chromatic dispersion, an environment reflection, thin-film iridescence and soft key lights. There are no render targets and no post-processing, so it is two draw calls and stays smooth on phones.\n\n".
                        "Built for real pages, not just for a demo reel:\n".
                        "- Pixel ratio capped and adaptive: it lowers resolution by itself if frames get slow\n".
                        "- Pauses when off screen or when the tab is hidden\n".
                        "- Respects prefers-reduced-motion: one still frame, no loop\n".
                        "- CSS gradient fallback if WebGL or the 3D library is unavailable\n".
                        "- Three palettes (dusk, lagoon, ink) wired to swatches\n".
                        "- One HTML file, three.js r160, no build step\n\n".
                        "What you get for $4 (one ZIP, instant download after checkout):\n".
                        "- PROMPT.md: the master prompt, six customisation prompts, a tuning table and a pre-launch checklist\n".
                        "- scene.html: the complete, commented source file, ready to open or embed\n".
                        "- A short README with licence notes",
                ],
                'tiers' => [
                    ['tier' => 'design', 'title' => 'Liquid Glass — Prompt + Source', 'price' => self::PRICE, 'type' => 'template', 'desc' => 'ZIP: the master prompt, customisation prompts and the full source.'],
                ],
            ],
            [
                'listing' => [
                    'slug'        => 'meridian-wire-terrain-flyover',
                    'title'       => 'Meridian — Wireframe Terrain Flyover',
                    'tagline'     => 'Endless wireframe terrain flyover',
                    'category'    => self::CATEGORY,
                    'tags'        => ['three.js', 'webgl', 'shader', 'hero', 'terrain', 'wireframe'],
                    'description' => "A wireframe terrain flyover hero for your website: endless glowing ridges, a banking camera, a low sun and stars. Claude prompt plus full source.\n\n".
                        "Meridian is one static grid. A ridged-noise height field scrolls through it inside the vertex shader, a solid occluder hides the lines behind peaks, and the pointer banks the camera and bends the horizon. The CPU only updates a handful of uniforms per frame.\n\n".
                        "Built for real pages, not just for a demo reel:\n".
                        "- Lower line density and pixel ratio on touch screens, plus adaptive quality if frames get slow\n".
                        "- Pauses when off screen or when the tab is hidden\n".
                        "- Respects prefers-reduced-motion: one still frame, no loop\n".
                        "- CSS fallback (gradient sky and a line floor) if WebGL or the 3D library is unavailable\n".
                        "- Three palettes (Meridian, Verdant, Rose) wired to swatches\n".
                        "- One HTML file, three.js r160, no build step\n\n".
                        "What you get for $4 (one ZIP, instant download after checkout):\n".
                        "- PROMPT.md: the master prompt, six customisation prompts, a tuning table and a pre-launch checklist\n".
                        "- scene.html: the complete, commented source file, ready to open or embed\n".
                        "- A short README with licence notes",
                ],
                'tiers' => [
                    ['tier' => 'design', 'title' => 'Meridian — Prompt + Source', 'price' => self::PRICE, 'type' => 'template', 'desc' => 'ZIP: the master prompt, customisation prompts and the full source.'],
                ],
            ],
        ];
    }

    public function run(): void
    {
        foreach (self::scenes() as $scene) {
            $listing = $this->seedMarketplaceListing($scene['listing'], $scene['tiers']);
            if (! $listing) {
                continue; // needs a seller user; UserSeeder runs first
            }

            $slug = $scene['listing']['slug'];

            // firstOrCreate inside the trait never refreshes copy, so keep the
            // listing text, tags and poster in sync with this file on every run.
            $poster = database_path("seeders/posters/{$slug}.jpg");
            $thumb = null;
            if (is_file($poster)) {
                $thumb = "demos/{$slug}/poster.jpg";
                Storage::disk('public')->put($thumb, file_get_contents($poster));
            }

            $listing->forceFill(array_filter([
                'title'       => $scene['listing']['title'],
                'tagline'     => $scene['listing']['tagline'],
                'description' => $scene['listing']['description'],
                'category'    => $scene['listing']['category'],
                'tags'        => $scene['listing']['tags'],
                'thumbnail'   => $thumb,
            ], fn ($v) => $v !== null))->save();
        }
    }

    /**
     * Scenes ship their own deliverables:
     *  - prompt tier: the prompt .md with the demo source embedded;
     *  - design tier: a ZIP (PROMPT.md + scene.html + README.txt).
     */
    protected function installDeliverable(string $slug, string $tier): ?string
    {
        $demo = database_path("seeders/demos/{$slug}.html");
        $prompt = database_path("seeders/deliverables/{$slug}-prompt.md");
        if (! is_file($demo) || ! is_file($prompt)) {
            return null;
        }

        $promptText = file_get_contents($prompt);
        $source = file_get_contents($demo);

        if ($tier === 'prompt') {
            $dest = "deliverables/{$slug}/prompt.md";
            Storage::disk('private')->put($dest, str_replace('{{SOURCE}}', rtrim($source), $promptText));

            return $dest;
        }

        if ($tier === 'design') {
            $tmp = tempnam(sys_get_temp_dir(), 'ngb3d');
            $zip = new \ZipArchive();
            if ($zip->open($tmp, \ZipArchive::OVERWRITE) !== true) {
                return null;
            }
            $zip->addFromString('PROMPT.md', $promptText);
            $zip->addFromString('scene.html', $source);
            $zip->addFromString('README.txt',
                "NextGenBeing 3D Scenes\n\n".
                "- scene.html : the complete, self-contained scene. Open it in a browser, or embed it in an iframe.\n".
                "- PROMPT.md  : the master prompt to recreate or customise it with Claude, plus tuning notes.\n\n".
                "Licence: use it in your own and your clients' projects. Do not resell the prompt or the source as a template pack.\n".
                "Needs internet access to load three.js from cdnjs.cloudflare.com.\n\n".
                "Thanks for your purchase.\n");
            $zip->close();

            $dest = "deliverables/{$slug}/pack.zip";
            Storage::disk('private')->put($dest, file_get_contents($tmp));
            @unlink($tmp);

            return $dest;
        }

        return null;
    }
}
