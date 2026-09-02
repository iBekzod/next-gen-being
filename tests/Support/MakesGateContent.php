<?php

namespace Tests\Support;

trait MakesGateContent
{
    /** Har biri ~18 so'zdan iborat, 60 belgidan uzun, TAKRORLANMAYDIGAN jumlalar. */
    private function cleanContent(int $minWords = 1600): string
    {
        $sentences = [];
        $i = 0;
        do {
            $i++;
            $sentences[] = "Bu {$i}-raqamli noyob izohli jumla bo'lib, ishlab chiqarish tizimlarida "
                . "ma'lumotlar bazasi indekslash va so'rov rejalashtirish haqida gapiradi.";
        } while (str_word_count(implode(' ', $sentences)) < $minWords);

        return implode(' ', $sentences);
    }
}
