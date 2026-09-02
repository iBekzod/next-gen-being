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

    /**
     * HAQIQIY, kod bilan to'la tutorial — bu saytning asosiy kontent turi.
     *
     * cleanContent() faqat nasrdan iborat, shuning uchun u haqiqiy texnik
     * maqolaning darvozalardan o'tishini hech qachon isbotlamagan. Bu fixture
     * aynan xavfli holatni modellashtiradi: bir nechta fenced misolda
     * TAKRORLANADIGAN import va sozlama qatorlari bor (har biri roppa-rosa
     * ikki marta), lekin bitta blok qayta-qayta qaytarilmagan.
     *
     * Yig'indi metrikasi bunday maqolani `duplicated_content` bilan rad etardi;
     * "eng yomon bitta takror" metrikasi esa 2 - 1 = 1 ball beradi va o'tkazadi.
     */
    private function codeHeavyContent(int $minWords = 1600): string
    {
        // 60 belgidan uzun umumiy qatorlar: takrorlanish metrikasiga kiradi.
        $sharedImport = 'use Illuminate\Support\Facades\Cache; // barcha misollarda bir xil import qatori';
        $sharedConfig = "'retry_after' => env('QUEUE_RETRY_AFTER', 90), // barcha misollarda bir xil sozlama";

        $parts = [];
        $i = 0;

        do {
            $i++;

            $parts[] = "## {$i}-bosqich: navbat ishlovchisini sozlash";
            $parts[] = "Bu {$i}-bosqichda navbat ishlovchisi uchun orqaga chekinish oynasini, "
                . "qayta urinishlar chegarasini va o'lik xatlar navbatini qanday tanlash "
                . "kerakligini ko'rib chiqamiz. Quyidagi {$i}-misol payload hajmi katta "
                . "bo'lganda seriyalash xarajati qanday o'sishini va buni o'lchash uchun "
                . "qaysi metrikani kuzatish kerakligini ko'rsatadi. {$i}-jadvalda "
                . "keltirilgan qiymatlar ishlab chiqarish yuklamasi ostida o'lchangan "
                . "kechikish taqsimotiga asoslanadi.";

            $code = "```php\n";
            // Umumiy import qatori faqat DASTLABKI IKKI misolda uchraydi.
            if ($i <= 2) {
                $code .= $sharedImport . "\n";
            }
            // Umumiy sozlama qatori faqat UCHINCHI va TO'RTINCHI misolda.
            if ($i === 3 || $i === 4) {
                $code .= $sharedConfig . "\n";
            }
            $code .= "\$job{$i} = new ProcessPayment(\$order, ['attempt' => {$i}, 'window' => " . (10 * $i) . "]);\n";
            $code .= "dispatch(\$job{$i})->onQueue('payments-{$i}')->delay(now()->addSeconds(" . (5 * $i) . "));\n";
            $code .= '```';

            $parts[] = $code;
        } while (str_word_count(strip_tags(implode("\n\n", $parts))) < $minWords);

        // Maqola nasr bilan, tinish belgisi bilan tugaydi (truncated darvozasi).
        $parts[] = 'Yakunda navbat sozlamalarini yuklama testi ostida tekshirib, '
            . "o'lchangan qiymatlarga qarab qayta urinish oynasini moslashtiring.";

        return implode("\n\n", $parts);
    }
}
