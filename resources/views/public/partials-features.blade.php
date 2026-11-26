@php
    $features = [
        ['book-open', 'Quran & Tafsir', 'Read the verified Quran with translation, tafsir, audio, bookmarks, notes and goals — available offline.', 'green'],
        ['landmark', 'Prayer tools', 'Accurate prayer times, Qibla compass, adhkar, tasbih and a private prayer tracker.', 'teal'],
        ['graduation-cap', 'Islamic Academy', 'Structured courses with lessons, quizzes and certificates you can follow at your own pace.', 'gold'],
        ['baby', 'Kids Deen World', 'A safe, parent-controlled space with stories, games and early learning for children.', 'pink'],
        ['users', 'Community', 'Share, follow and join groups in a moderated, respectful community.', 'blue'],
        ['calendar-days', 'Events', 'Discover lectures, classes and gatherings near you and register in a tap.', 'purple'],
        ['map-pin', 'Directory', 'Find mosques and businesses, with transparent halal verification status.', 'green'],
        ['calculator', 'Zakat calculator', 'A clear, step-by-step zakat calculation with saved results.', 'gold'],
        ['sparkles', 'SIRAJ AI', 'A source-grounded companion for Islamic knowledge questions — never a substitute for a scholar.', 'teal'],
        ['compass', 'Hajj & Umrah', 'Step-by-step guides, duas and checklists you can keep offline.', 'blue'],
        ['crown', 'SIRAJ Plus', 'Premium courses, higher AI limits and family profiles.', 'gold'],
    ];
@endphp
<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($features as [$icon, $title, $text, $tone])
        <div class="card flex gap-4 p-5">
            <span class="grid size-12 shrink-0 place-items-center rounded-2xl {{ ['green' => 'bg-emerald-100 text-forest-700', 'teal' => 'bg-teal-100 text-teal-700', 'gold' => 'bg-gold-100 text-gold-600', 'pink' => 'bg-pink-100 text-pink-600', 'blue' => 'bg-sky-100 text-sky-700', 'purple' => 'bg-violet-100 text-violet-700'][$tone] }}"><x-dynamic-component :component="'lucide-'.$icon" class="size-6" /></span>
            <div><h3 class="font-bold">{{ $title }}</h3><p class="mt-1 text-sm leading-6 text-muted">{{ $text }}</p></div>
        </div>
    @endforeach
</div>
