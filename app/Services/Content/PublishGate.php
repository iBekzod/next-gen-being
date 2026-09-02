<?php

namespace App\Services\Content;

use App\Models\Post;

/**
 * Nashr darvozalarining yagona manbasi.
 *
 * Ilgari bu mantiq ContentDripPublish ichida private edi, shuning uchun
 * monitoring uni qayta ishlata olmasdi. Endi nashr qiluvchi ham,
 * content:health-check ham xuddi shu predikatni chaqiradi — ikkalasi
 * bir-biridan uzoqlashib keta olmaydi.
 */
class PublishGate
{
    public const POST_INTERVAL_DAYS = 5;
    public const TUTORIAL_INTERVAL_DAYS = 7;
    public const MIN_WORDS = 1500;

    /**
     * Post yiqilgan darvozalar ro'yxati. Bo'sh massiv = nashrga tayyor.
     *
     * @return string[]
     */
    public function failures(Post $post): array
    {
        $content = (string) $post->content;
        $failures = [];

        if (str_word_count(strip_tags($content)) < self::MIN_WORDS) {
            $failures[] = 'too_short';
        }

        if (! preg_match('/[.!?]["\')\]]?\s*$/', trim($content))) {
            $failures[] = 'truncated';
        }

        if (substr_count($content, '```') % 2 !== 0) {
            $failures[] = 'unbalanced_fences';
        }

        if ($post->moderation_status === 'pending') {
            $failures[] = 'moderation_pending';
        }

        return $failures;
    }

    public function passes(Post $post): bool
    {
        return $this->failures($post) === [];
    }
}
