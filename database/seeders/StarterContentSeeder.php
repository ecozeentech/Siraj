<?php

namespace Database\Seeders;

use App\Models\Academy\Course;
use App\Models\Academy\CourseCategory;
use App\Models\Academy\Instructor;
use App\Models\Academy\Quiz;
use App\Models\Kids\KidsCourse;
use App\Models\Kids\KidsGame;
use App\Models\Kids\KidsStory;
use App\Models\Quran\Ayah;
use App\Models\Worship\Guide;
use App\Models\Worship\TajweedResource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Starter curriculum. All Quran text is resolved from the verified `ayahs` table (never typed by hand).
 * Content is intentionally concise, conservative and should be reviewed by qualified scholars before public launch;
 * it can be edited or unpublished from the admin portal.
 */
class StarterContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->academy();
        $this->kids();
        $this->guides();
        $this->tajweed();
    }

    private function ayah(string $ref): string
    {
        return Ayah::findByReference($ref)?->text_uthmani ?? '';
    }

    private function academy(): void
    {
        if (Course::query()->exists()) {
            return;
        }
        $team = Instructor::query()->firstOrCreate(['name' => 'SIRAJ Academy Team'], ['title' => 'Editorial team', 'bio' => 'Courses prepared by the SIRAJ editorial team and reviewed by qualified teachers.']);
        $cat = fn (string $slug) => CourseCategory::query()->where('slug', $slug)->value('id');

        $courses = [
            [
                'title' => 'Quran Studies', 'slug' => 'quran-studies', 'cat' => 'quran-studies', 'level' => 'beginner', 'theme' => 'quran_open', 'premium' => false,
                'summary' => 'Build a deeper connection with the Quran.',
                'description' => 'An introduction to how the Quran is organised, how to approach recitation with respect, and how to study its meaning.',
                'lessons' => [
                    ['How the Quran is organised', 'text', 5, true, "The Quran is divided into **114 surahs** (chapters) of varying length. Surahs are made up of **ayat** (verses).\n\nFor ease of reading across a month, the Quran is also divided into **30 equal parts called juz**. Another traditional division is the *hizb* (60 parts) and *manzil* (7 parts).\n\nSurahs are traditionally described as *Makki* (revealed before the Hijrah) or *Madani* (revealed after it). Tap any surah in the SIRAJ Quran reader to see its type."],
                    ['Etiquette of reciting the Quran', 'text', 6, false, "Scholars mention these etiquettes when approaching the Quran:\n\n1. **Sincerity** — recite seeking the pleasure of Allah.\n2. **Cleanliness** — be in a state of wudu where possible and recite in a clean place.\n3. **Seeking refuge** — begin with *A'udhu billahi minash-shaytanir-rajim*, then *Bismillah* (except at the start of Surah At-Tawbah).\n4. **Reciting with measured pace (tartil)** — Allah says: \"…and recite the Quran with measured recitation.\" (73:4)\n5. **Reflection** — read the translation and tafsir to understand what you recite."],
                    ['Using translation and tafsir well', 'text', 6, false, "A translation conveys the meaning of the Arabic as closely as another language allows; it is not itself the Quran. **Tafsir** (exegesis) explains context, language and rulings, and is written by scholars who rely on the Quran, authentic Sunnah, the Companions' understanding and the Arabic language.\n\nWhen you read a tafsir in SIRAJ, note its author and source shown beneath the text. If something seems unclear or you are looking for a ruling, consult a qualified scholar."],
                ],
                'quiz' => ['Quran Studies check', [
                    ['How many surahs are in the Quran?', ['114', '100', '120', '30'], 0, 'The Quran has 114 surahs.'],
                    ['Into how many juz is the Quran divided for reading across a month?', ['30', '7', '60', '114'], 0, 'There are 30 juz.'],
                    ['Which is the more accurate description of a translation?', ['It conveys the meaning of the Quran in another language', 'It replaces the Arabic Quran', 'It is the same as tafsir', 'It is revealed text'], 0, 'A translation conveys meaning; it is not the Quran itself.'],
                ]],
            ],
            [
                'title' => 'Arabic Language', 'slug' => 'arabic-language', 'cat' => 'arabic-language', 'level' => 'beginner', 'theme' => 'mosque_green', 'premium' => false,
                'summary' => 'Beginner level — letters, vowels and first words.',
                'description' => 'Learn the Arabic alphabet, short vowels and how to read simple words, in preparation for reading the Quran.',
                'lessons' => [
                    ['The Arabic alphabet', 'text', 8, true, "Arabic is written from right to left and has **28 letters**:\n\nا ب ت ث ج ح خ د ذ ر ز س ش ص ض ط ظ ع غ ف ق ك ل م ن ه و ي\n\nMost letters change shape depending on whether they appear at the beginning, middle or end of a word, or stand alone. Six letters (ا د ذ ر ز و) do not connect to the letter that follows them."],
                    ['Short vowels (harakat)', 'text', 6, false, "Short vowels are small marks written above or below letters:\n\n- **Fatha** (  َ ) — a short *a* sound, as in بَ (ba)\n- **Kasra** (  ِ ) — a short *i* sound, as in بِ (bi)\n- **Damma** (  ُ ) — a short *u* sound, as in بُ (bu)\n- **Sukoon** (  ْ ) — no vowel, the letter is pronounced without a vowel sound\n- **Shadda** (  ّ ) — doubles the letter"],
                    ['Joining letters into words', 'text', 7, false, "Practise by joining two or three letters: **بَ + ا = بَا** (ba), **كَ + تَ + بَ = كَتَبَ** (kataba — \"he wrote\").\n\nTip: read slowly, say each letter with its vowel, then blend them. Repeat daily for ten minutes."],
                ],
                'quiz' => ['Arabic basics check', [
                    ['How many letters are in the Arabic alphabet?', ['28', '26', '30', '24'], 0, 'Arabic has 28 letters.'],
                    ['In which direction is Arabic written?', ['Right to left', 'Left to right', 'Top to bottom', 'Bottom to top'], 0, 'Arabic is written right to left.'],
                    ['Which mark indicates a doubled letter?', ['Shadda', 'Sukoon', 'Fatha', 'Kasra'], 0, 'The shadda doubles a letter.'],
                ]],
            ],
            [
                'title' => 'Seerah of the Prophet ﷺ', 'slug' => 'seerah-of-the-prophet', 'cat' => 'seerah-history', 'level' => 'beginner', 'theme' => 'mosque_blue', 'premium' => false,
                'summary' => 'Learn about his life — an accessible overview.',
                'description' => 'A concise overview of the life of Prophet Muhammad ﷺ, from his birth in Makkah to the Hijrah to Madinah.',
                'lessons' => [
                    ['Birth and early life', 'text', 7, true, "Prophet Muhammad ﷺ was born in **Makkah** in the *Year of the Elephant* (around 570 CE). His father, Abdullah, passed away before his birth and his mother, Aminah, when he was six. He was then cared for by his grandfather Abdul-Muttalib and, after his death, by his uncle **Abu Talib**.\n\nHe became known among his people as **Al-Amin** (the trustworthy) and **As-Sadiq** (the truthful). At around age 25 he married **Khadijah bint Khuwaylid** (may Allah be pleased with her)."],
                    ['The first revelation', 'text', 7, false, "At the age of about 40, while in the cave of **Hira** near Makkah, the angel Jibril (Gabriel) came to the Prophet ﷺ with the first revelation: \"Recite in the name of your Lord who created…\" (96:1).\n\nKhadijah (may Allah be pleased with her) was the first to believe, followed by Abu Bakr, Ali and Zayd ibn Harithah (may Allah be pleased with them). The message was first shared quietly, then publicly."],
                    ['The Hijrah to Madinah', 'text', 8, false, "After years of persecution in Makkah, the Muslims were permitted to migrate to **Madinah** (Yathrib). The Prophet ﷺ made the *Hijrah* in 622 CE accompanied by Abu Bakr (may Allah be pleased with him). This event marks **the start of the Islamic (Hijri) calendar**.\n\nIn Madinah the Prophet ﷺ established the first mosque community, built Masjid an-Nabawi and brought together the *Muhajirun* (migrants) and *Ansar* (helpers)."],
                ],
                'quiz' => ['Seerah overview quiz', [
                    ['In which city was the Prophet ﷺ born?', ['Makkah', 'Madinah', 'Taif', 'Jerusalem'], 0, 'He was born in Makkah.'],
                    ['What was the first word revealed in the first revelation?', ['Iqra (Recite)', 'Qul (Say)', 'Ya Ayyuha (O you)', 'Alhamdulillah'], 0, 'Surah Al-Alaq begins with "Iqra".'],
                    ['What event marks the start of the Hijri calendar?', ['The Hijrah to Madinah', 'The Battle of Badr', 'The conquest of Makkah', 'The first revelation'], 0, 'The Hijrah in 622 CE.'],
                ]],
            ],
            [
                'title' => 'Fiqh Made Easy', 'slug' => 'fiqh-made-easy', 'cat' => 'fiqh', 'level' => 'beginner', 'theme' => 'mosque_purple', 'premium' => true,
                'summary' => 'Islamic jurisprudence essentials.',
                'description' => 'The pillars of Islam and faith, purification and the daily prayers, explained simply. Rulings may differ between schools of thought; consult a qualified scholar for personal rulings.',
                'lessons' => [
                    ['The pillars of Islam and Iman', 'text', 6, true, "**Five pillars of Islam:** the testimony of faith (*shahadah*), establishing prayer (*salah*), giving zakat, fasting in Ramadan, and pilgrimage (*Hajj*) to the House for those able.\n\n**Six pillars of Iman (faith):** belief in Allah, His angels, His books, His messengers, the Last Day, and divine decree (*qadar*)."],
                    ['Wudu (ablution)', 'text', 8, false, "A commonly taught sequence of wudu: intention and saying *Bismillah*; washing the hands; rinsing the mouth and nose; washing the face; washing the arms up to and including the elbows; wiping the head; wiping the ears; washing the feet up to and including the ankles. Each obligatory act is done in order and without long gaps.\n\nDetails (such as what breaks wudu) vary slightly between schools; follow a trusted teacher."],
                    ['The five daily prayers', 'text', 8, false, "The five obligatory prayers and their number of *rak'ahs*: **Fajr (2)**, **Dhuhr (4)**, **Asr (4)**, **Maghrib (3)**, **Isha (4)**.\n\nUse SIRAJ Worship to see accurate prayer times for your location and choose your calculation method."],
                ],
                'quiz' => ['Fiqh essentials quiz', [
                    ['How many rak\'ahs are in the obligatory Maghrib prayer?', ['3', '2', '4', '5'], 0, 'Maghrib is three rak\'ahs.'],
                    ['How many pillars of Islam are there?', ['5', '6', '4', '7'], 0, 'There are five pillars of Islam.'],
                    ['How many obligatory rak\'ahs are in Fajr?', ['2', '3', '4', '1'], 0, 'Fajr is two rak\'ahs.'],
                ]],
            ],
        ];

        foreach ($courses as $i => $c) {
            $course = Course::query()->create([
                'course_category_id' => $cat($c['cat']), 'instructor_id' => $team->id, 'title' => $c['title'], 'slug' => $c['slug'],
                'summary' => $c['summary'], 'description' => $c['description'], 'level' => $c['level'], 'cover_theme' => $c['theme'],
                'is_premium' => $c['premium'], 'is_published' => true, 'published_at' => now(), 'sort' => $i,
            ]);
            foreach ($c['lessons'] as $n => [$title, $type, $mins, $free, $body]) {
                $course->lessons()->create(['title' => $title, 'type' => $type, 'duration_minutes' => $mins, 'is_free_preview' => $free, 'body' => $body, 'sort' => $n]);
            }
            [$quizTitle, $questions] = $c['quiz'];
            $quiz = Quiz::query()->create(['course_id' => $course->id, 'title' => $quizTitle, 'pass_mark' => 70]);
            foreach ($questions as $qi => [$text, $options, $correct, $explanation]) {
                $q = $quiz->questions()->create(['body' => $text, 'type' => 'single', 'explanation' => $explanation, 'sort' => $qi]);
                foreach ($options as $oi => $opt) {
                    $q->answers()->create(['body' => $opt, 'is_correct' => $oi === $correct, 'sort' => $oi]);
                }
            }
        }
    }

    private function kids(): void
    {
        if (KidsCourse::query()->exists()) {
            return;
        }
        $letters = [['ا', 'Alif'], ['ب', 'Ba'], ['ت', 'Ta'], ['ث', 'Tha'], ['ج', 'Jim'], ['ح', 'Ha'], ['خ', 'Kha'], ['د', 'Dal']];
        $course = KidsCourse::query()->create(['title' => 'Arabic Letters', 'slug' => 'arabic-letters', 'category' => 'arabic', 'summary' => 'Meet the letters of the Arabic alphabet.', 'age_min' => 4, 'age_max' => 8, 'icon' => 'languages', 'color' => 'blue', 'is_published' => true, 'sort' => 0]);
        foreach (array_chunk($letters, 2) as $n => $pair) {
            $course->lessons()->create([
                'title' => $pair[0][1].' and '.$pair[1][1], 'sort' => $n, 'arabic_text' => $pair[0][0].'   '.$pair[1][0],
                'transliteration' => $pair[0][1].' · '.$pair[1][1],
                'body' => "This is the letter {$pair[0][1]} ({$pair[0][0]}) and the letter {$pair[1][1]} ({$pair[1][0]}). Say each letter out loud three times, then trace it in the air with your finger.",
            ]);
        }

        $short = KidsCourse::query()->create(['title' => 'Little Surahs', 'slug' => 'little-surahs', 'category' => 'quran', 'summary' => 'Listen, read and learn short surahs.', 'age_min' => 5, 'age_max' => 12, 'icon' => 'book-open', 'color' => 'green', 'is_published' => true, 'sort' => 1]);
        foreach ([[112, 'Surah Al-Ikhlas'], [113, 'Surah Al-Falaq'], [114, 'Surah An-Nas']] as $n => [$no, $title]) {
            $text = Ayah::query()->where('surah_id', $no)->orderBy('number_in_surah')->pluck('text_uthmani')->implode('  ');
            $short->lessons()->create(['title' => $title, 'sort' => $n, 'arabic_text' => $text, 'body' => "Listen to {$title} in the Quran reader, then read it slowly with a parent or teacher. Read one ayah at a time and repeat after the reciter.", 'audio_url' => null]);
        }

        $manners = KidsCourse::query()->create(['title' => 'Islamic Manners', 'slug' => 'islamic-manners', 'category' => 'manners', 'summary' => 'Good character in everyday life.', 'age_min' => 4, 'age_max' => 12, 'icon' => 'heart-handshake', 'color' => 'pink', 'is_published' => true, 'sort' => 2]);
        foreach ([
            ['Greeting with Salam', 'Muslims greet each other with "As-salamu alaykum" (peace be upon you). The reply is "Wa alaykumus-salam" (and upon you be peace). Smile and greet people when you meet them.', 'السَّلَامُ عَلَيْكُمْ', 'As-salamu alaykum'],
            ['Bismillah before eating', 'We begin eating by saying "Bismillah" (in the name of Allah), eat with the right hand, and say "Alhamdulillah" when we finish.', 'بِسْمِ ٱللَّهِ', 'Bismillah'],
            ['Respect for parents', 'Allah teaches us to be kind and respectful to our parents. We listen to them, speak gently and help at home.', null, null],
        ] as $n => [$title, $body, $ar, $tr]) {
            $manners->lessons()->create(['title' => $title, 'sort' => $n, 'body' => $body, 'arabic_text' => $ar, 'transliteration' => $tr]);
        }

        $stories = [
            ['The Elephant That Would Not Move', 'the-elephant', 'mosque_green', 'Allah protects His House.', 'Surah Al-Fil (105)',
                "Long ago, a king named Abrahah wanted to destroy the Kaaba in Makkah. He marched toward the city with a great army and a huge elephant.\n\nBut when the army came close, the elephant knelt down and would not go any further. Then Allah sent flocks of birds who dropped small stones on the army, and the army was destroyed.\n\nThe people of Makkah learned that Allah protects His House. This story is told in Surah Al-Fil in the Quran."],
            ['Nuh and the Great Ship', 'nuh-and-the-ship', 'mosque_blue', 'Trust Allah and keep calling to goodness patiently.', 'Surah Hud (11:25-49)',
                "Prophet Nuh (Noah) called his people to worship Allah alone for a very long time. Only a few believed.\n\nAllah told Nuh to build a ship. His people laughed at him, but Nuh kept trusting Allah. When the great flood came, Nuh, the believers and pairs of animals were safe on the ship.\n\nNuh's patience teaches us to keep doing good, even when it is hard."],
            ['Sulayman and the Ant', 'sulayman-and-the-ant', 'mosque_purple', 'Kindness and gratitude.', 'Surah An-Naml (27:18-19)',
                "Prophet Sulayman (Solomon) could understand the speech of animals, by the gift of Allah. One day, his army passed by a valley of ants. An ant called out to the others to go into their homes so they would not be harmed.\n\nSulayman smiled and thanked Allah for His blessings. The story teaches us to be thankful, to be gentle with creatures and to care about others."],
        ];
        foreach ($stories as $n => [$title, $slug, $theme, $moral, $ref, $body]) {
            KidsStory::query()->create(['title' => $title, 'slug' => $slug, 'cover_theme' => $theme, 'moral' => $moral, 'reference' => $ref, 'body' => $body, 'age_min' => 4, 'age_max' => 12, 'is_published' => true, 'sort' => $n]);
        }

        KidsGame::query()->create(['title' => 'Quran Match', 'slug' => 'quran-match', 'type' => 'match', 'summary' => 'Match each Arabic letter with its name.', 'icon' => 'layout-grid', 'color' => 'gold', 'is_published' => true, 'sort' => 0,
            'config' => ['pairs' => array_map(fn ($l) => ['ar' => $l[0], 'en' => $l[1]], array_slice($letters, 0, 6))]]);
        KidsGame::query()->create(['title' => 'Arabic Puzzle', 'slug' => 'arabic-puzzle', 'type' => 'quiz', 'summary' => 'Which letter is it?', 'icon' => 'puzzle', 'color' => 'blue', 'is_published' => true, 'sort' => 1,
            'config' => ['questions' => [
                ['q' => 'Which letter is called Ba?', 'options' => ['ب', 'ت', 'ا'], 'answer' => 0],
                ['q' => 'Which letter is called Alif?', 'options' => ['د', 'ا', 'ج'], 'answer' => 1],
                ['q' => 'Which letter is called Jim?', 'options' => ['ح', 'خ', 'ج'], 'answer' => 2],
                ['q' => 'Which letter is called Dal?', 'options' => ['د', 'ث', 'ب'], 'answer' => 0],
            ]]]);
        KidsGame::query()->create(['title' => 'Good Deeds', 'slug' => 'good-deeds', 'type' => 'quiz', 'summary' => 'Choose the kind thing to do.', 'icon' => 'heart', 'color' => 'green', 'is_published' => true, 'sort' => 2,
            'config' => ['questions' => [
                ['q' => 'What do we say before we start eating?', 'options' => ['Bismillah', 'Nothing', 'Goodbye'], 'answer' => 0],
                ['q' => 'When we meet a Muslim, we say…', 'options' => ['As-salamu alaykum', 'Be quiet', 'Go away'], 'answer' => 0],
                ['q' => 'A friend is sad. What is the kind thing to do?', 'options' => ['Ignore them', 'Comfort them and be kind', 'Laugh at them'], 'answer' => 1],
                ['q' => 'How should we speak to our parents?', 'options' => ['Gently and respectfully', 'Loudly', 'Rudely'], 'answer' => 0],
            ]]]);
    }

    private function guides(): void
    {
        if (Guide::query()->exists()) {
            return;
        }
        $umrah = Guide::query()->create(['slug' => 'umrah-step-by-step', 'title' => 'Umrah — step by step', 'type' => 'umrah', 'summary' => 'The rites of Umrah in order.', 'sort' => 0]);
        $steps = [
            ['Enter ihram at the miqat', "Make the intention for Umrah at the *miqat* (boundary) appropriate to your route, after ghusl (or wudu), wearing ihram clothing — two unstitched white sheets for men; modest everyday clothing for women. Avoid the prohibitions of ihram (such as perfume, cutting hair or nails, and marital relations)."],
            ['Recite the Talbiyah', 'Begin reciting the Talbiyah once in ihram and continue until you begin tawaf.', 'لَبَّيْكَ اللَّهُمَّ لَبَّيْكَ، لَبَّيْكَ لَا شَرِيكَ لَكَ لَبَّيْكَ، إِنَّ الْحَمْدَ وَالنِّعْمَةَ لَكَ وَالْمُلْكَ، لَا شَرِيكَ لَكَ', 'Labbayk Allahumma labbayk, labbayka la sharika laka labbayk. Innal-hamda wan-ni\'mata laka wal-mulk, la sharika lak.', 'Here I am, O Allah, here I am. Here I am, You have no partner, here I am. Verily all praise and blessings are Yours, and all sovereignty. You have no partner.', 'Reported in Sahih al-Bukhari and Sahih Muslim'],
            ['Tawaf around the Kaaba', "Perform seven circuits of the Kaaba, starting and ending at the Black Stone, keeping the Kaaba on your left. Men uncover the right shoulder (idtiba') and walk briskly (raml) in the first three circuits. Make dua freely throughout."],
            ['Pray two rak\'ahs and drink Zamzam', "After tawaf, pray two rak'ahs behind or near the Station of Ibrahim (Maqam Ibrahim) if possible, then drink Zamzam water."],
            ["Sa'i between Safa and Marwa", "Go to Mount Safa and recite the verse below, then walk to Marwa (one lap), then back to Safa (second lap), completing **seven laps** and ending at Marwa. Men jog lightly between the green markers.", $this->ayah('2:158'), null, null, 'Quran 2:158'],
            ['Shave or trim the hair', 'To complete Umrah, men shave the head (halq) or trim (taqsir) the hair evenly; women trim a fingertip length from the ends of their hair. You are now out of ihram.'],
        ];
        foreach ($steps as $n => $s) {
            $umrah->steps()->create(['title' => $s[0], 'body' => $s[1], 'arabic_text' => $s[2] ?? null, 'transliteration' => $s[3] ?? null, 'translation_en' => $s[4] ?? null, 'reference' => $s[5] ?? null, 'sort' => $n]);
        }
        foreach ([
            ['Documents', 'Valid passport (with at least 6 months validity) and Umrah/Hajj visa'], ['Documents', 'Flight and accommodation confirmations'], ['Documents', 'Copies of key documents stored separately'],
            ['Health', 'Required vaccinations as stated by the Saudi Ministry of Health (check current requirements)'], ['Health', 'Personal medicines with prescriptions'],
            ['Ihram', 'Two ihram sheets for men / modest clothing for women'], ['Ihram', 'Unscented toiletries'], ['Ihram', 'Comfortable, non-slip footwear'],
            ['Spiritual', 'Learn the Talbiyah and the steps of Umrah'], ['Spiritual', 'Write your duas and settle debts and obligations'],
        ] as $n => [$cat, $label]) {
            $umrah->checklistItems()->create(['category' => $cat, 'label' => $label, 'sort' => $n]);
        }

        $hajj = Guide::query()->create(['slug' => 'hajj-overview', 'title' => 'Hajj — overview of the days', 'type' => 'hajj', 'summary' => 'The main rites from 8 to 13 Dhul Hijjah.', 'sort' => 1]);
        foreach ([
            ['8 Dhul Hijjah — Yawm at-Tarwiyah', 'Enter ihram for Hajj (if you have not already) and proceed to **Mina**, where you pray Dhuhr, Asr, Maghrib, Isha and Fajr (shortened, without combining times, per the Sunnah).'],
            ['9 Dhul Hijjah — Day of Arafah', 'After sunrise go to **Arafah**. Standing at Arafah is the essence of Hajj. Spend the day in dua, dhikr and Quran. After sunset, leave for Muzdalifah.'],
            ['Night of 9–10 — Muzdalifah', 'Pray Maghrib and Isha combined at Muzdalifah, rest, and pray Fajr there. Collect pebbles for stoning if you wish.'],
            ['10 Dhul Hijjah — Yawm an-Nahr', 'Stone Jamarat al-Aqabah with seven pebbles, offer the sacrifice (hady), shave or trim your hair, then perform Tawaf al-Ifadah and Sa\'i (if required for your type of Hajj).'],
            ['11–13 Dhul Hijjah — Days of Tashriq', 'Stay at Mina and stone the three Jamarat each day after midday (Dhuhr). You may leave on 12th before sunset, or stay until the 13th.'],
            ['Farewell Tawaf', 'Before leaving Makkah, perform Tawaf al-Wada\' (farewell tawaf).'],
        ] as $n => [$title, $body]) {
            $hajj->steps()->create(['title' => $title, 'body' => $body, 'sort' => $n]);
        }
        $hajj->checklistItems()->createMany([
            ['category' => 'Documents', 'label' => 'Confirm your Hajj registration, visa and group details with your operator', 'sort' => 0],
            ['category' => 'Health', 'label' => 'Required vaccinations and a medical check-up', 'sort' => 1],
            ['category' => 'Spiritual', 'label' => 'Study the rites of Hajj with a qualified teacher', 'sort' => 2],
        ]);
    }

    private function tajweed(): void
    {
        if (TajweedResource::query()->exists()) {
            return;
        }
        foreach ([
            ['Noon sakinah & tanween: overview', 'noon-sakinah', 'Noon sakinah', 'The four rules when noon sakinah (نْ) or tanween is followed by a letter.', "There are four rules: **Izhar** (clear pronunciation) before the throat letters ء هـ ع ح غ خ; **Idgham** (merging) before ي ر م ل و ن; **Iqlab** (changing to a *meem* sound) before ب; and **Ikhfa** (concealing) before the remaining 15 letters."],
            ['Qalqalah', 'qalqalah', 'Qalqalah', 'A slight echo on five letters when they are sakin.', "The letters of qalqalah are **ق ط ب ج د** (remembered as *qutb jad*). When one of these letters carries a sukoon, pronounce it with a slight bouncing echo."],
            ['Madd (prolongation)', 'madd', 'Madd', 'Lengthening of vowel sounds.', "Natural madd (*madd asli*) is held for **two counts** and occurs with an alif after fatha, a waw after damma, or a ya after kasra. Other types of madd (connected, separated, necessary) are longer and are studied with a qualified teacher."],
            ['Ghunnah', 'ghunnah', 'Ghunnah', 'The nasal sound on noon and meem mushaddad.', "Ghunnah is a nasal sound held for about **two counts** whenever a *noon* or *meem* carries a shadda (نّ, مّ)."],
        ] as $n => [$title, $slug, $group, $summary, $body]) {
            TajweedResource::query()->create(['title' => $title, 'slug' => $slug, 'rule_group' => $group, 'summary' => $summary, 'body' => $body, 'sort' => $n]);
        }
    }
}
