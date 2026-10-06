@extends('layouts.app')

@section('title', 'Affiliate Disclosure - ' . setting('site_name'))
@section('description', 'How NextGenBeing uses affiliate (partner) links, what we earn from them, and how that affects our recommendations.')
@section('canonical', route('affiliate.disclosure'))
@section('robots', 'index, follow')

@section('content')
<div class="min-h-screen bg-white dark:bg-gray-900">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-8">Affiliate Disclosure</h1>

        <div class="prose prose-lg dark:prose-invert max-w-none">
            <p class="text-gray-600 dark:text-gray-400">Last updated: October 6, 2026</p>

            <p>Some links on {{ setting('site_name', 'NextGenBeing') }} are <strong>affiliate links</strong>. If you click one and later buy a paid plan, the vendor may pay us a commission. <strong>You pay the same price</strong> - sometimes less, when the program includes a discount for new users.</p>

            <h2>How to recognise them</h2>
            <ul>
                <li>Partner links go through an address on our own site that starts with <code>/go/</code> (for example <code>{{ url('/go/elevenlabs') }}</code>) and then forward you to the vendor.</li>
                <li>Articles that contain partner links say so in the article text.</li>
                <li>Partner links are marked <code>rel="sponsored"</code> for search engines.</li>
            </ul>

            <h2>What we record</h2>
            <p>When you click a <code>/go/</code> link we store the link name, the page you came from, the time, your browser's user-agent and a one-way hash of your IP address (never the address itself). We use this only to count which recommendations people find useful. The vendor's own tracking (cookies set on their site) is governed by their privacy policy.</p>

            <h2>How it affects what we write</h2>
            <ul>
                <li>We recommend tools we have used or tested, and we say when something has a weak free plan, a confusing price or a better free alternative.</li>
                <li>A commission never decides whether a tool is mentioned. Many tools we recommend have no affiliate program at all and are linked normally.</li>
                <li>No vendor reviews or approves our articles before publication.</li>
            </ul>

            <p>Questions: <a href="{{ route('contact') }}">contact us</a>.</p>

            <hr>

            <h2 id="uz" lang="uz">Hamkorlik havolalari haqida (o'zbekcha)</h2>
            <div lang="uz">
                <p>Saytdagi ba'zi havolalar <strong>hamkorlik (affiliate) havolalari</strong>. Ular orqali o'tib pullik tarifga obuna bo'lsangiz, xizmat egasi bizga komissiya to'lashi mumkin. <strong>Siz uchun narx o'zgarmaydi</strong> - ba'zan yangi foydalanuvchiga chegirma ham bo'ladi.</p>
                <ul>
                    <li>Bunday havolalar saytimizdagi <code>/go/...</code> manzili orqali o'tadi va keyin xizmat sahifasiga yo'naltiradi.</li>
                    <li>Bosishda faqat havola nomi, qaysi sahifadan kelganingiz, vaqt, brauzer turi va IP manzilingizning qaytarib bo'lmaydigan xeshi saqlanadi.</li>
                    <li>Biz o'zimiz sinab ko'rgan vositalarni tavsiya qilamiz va kamchiliklarini ham ochiq yozamiz. Komissiya vosita maqolaga kiradimi-yo'qmi, shuni hal qilmaydi.</li>
                </ul>
                <p><a href="{{ route('uz.index') }}">O'zbekcha maqolalarga qaytish</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
