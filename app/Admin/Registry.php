<?php

namespace App\Admin;

use App\Models\Academy\Course;
use App\Models\Academy\CourseCategory;
use App\Models\Academy\CourseLesson;
use App\Models\Academy\Instructor;
use App\Models\Academy\Quiz;
use App\Models\Academy\QuizQuestion;
use App\Models\Ai\AiModel;
use App\Models\Ai\AiProvider;
use App\Models\Ai\AiSource;
use App\Models\Directory\Business;
use App\Models\Directory\BusinessCategory;
use App\Models\Directory\Mosque;
use App\Models\Events\Event;
use App\Models\Events\EventCategory;
use App\Models\Finance\SubscriptionPlan;
use App\Models\Kids\KidsCourse;
use App\Models\Kids\KidsGame;
use App\Models\Kids\KidsLesson;
use App\Models\Kids\KidsStory;
use App\Models\Quran\Reciter;
use App\Models\Quran\TafsirSource;
use App\Models\Quran\TranslationSource;
use App\Models\System\Location;
use App\Models\Worship\AdhkarCategory;
use App\Models\Worship\AdhkarEntry;
use App\Models\Worship\Guide;
use App\Models\Worship\GuideChecklistItem;
use App\Models\Worship\GuideStep;
use App\Models\Worship\TajweedResource;

/**
 * Declarative definitions for the generic admin CRUD screens. Each resource is protected by a permission slug,
 * validated server-side from its field rules, and every change is written to the audit log.
 *
 * Field types: text, textarea, number, select, checkbox, date, datetime, json, url, email, secret, money, image.
 */
final class Registry
{
    public const MODULES = [
        'quran' => ['Quran', 'Translations, tafsir sources, reciters and the verified text.', 'book-open'],
        'worship' => ['Worship', 'Adhkar, tajweed resources and Hajj & Umrah guides.', 'landmark'],
        'academy' => ['Academy', 'Courses, lessons, quizzes and learners.', 'graduation-cap'],
        'kids' => ['Kids Deen World', 'Courses, lessons, stories and games for children.', 'baby'],
        'events' => ['Events', 'Events, categories and attendance.', 'calendar-days'],
        'directories' => ['Directories', 'Mosques, businesses, reviews and reports.', 'map-pin'],
    ];

    public static function find(string $key): array
    {
        return self::all()[$key] ?? abort(404);
    }

    public static function forModule(string $module): array
    {
        return array_filter(self::all(), fn ($r) => $r['module'] === $module);
    }

    private static function f(string $name, string $label, string $type = 'text', string|array $rules = 'nullable|string|max:255', array $extra = []): array
    {
        return ['name' => $name, 'label' => $label, 'type' => $type, 'rules' => $rules] + $extra;
    }

    private static function sort(): array
    {
        return self::f('sort', 'Display order', 'number', 'nullable|integer|min:0|max:65000', ['default' => 0]);
    }

    private static function rel(string $model, string $label = 'name'): array
    {
        return ['model' => $model, 'label' => $label];
    }

    public static function all(): array
    {
        return once(fn () => [
            // ---- Quran ----
            'translations' => ['module' => 'quran', 'label' => 'Translations', 'singular' => 'translation source', 'permission' => 'quran.manage', 'model' => TranslationSource::class, 'order' => ['sort', 'id'], 'search' => ['name', 'translator'], 'slug' => ['slug', 'name'], 'delete' => false,
                'columns' => [['name', 'Name'], ['translator', 'Translator'], ['language', 'Lang'], ['license', 'Licence'], ['is_active', 'Active', 'bool']],
                'fields' => [self::f('name', 'Name', 'text', 'required|string|max:255'), self::f('translator', 'Translator'), self::f('language', 'Language code', 'text', 'required|string|max:8'), self::f('source', 'Source'), self::f('source_url', 'Source URL', 'url', 'nullable|url|max:255'), self::f('license', 'Licence'), self::f('attribution', 'Attribution text', 'textarea', 'nullable|string|max:2000'), self::f('is_active', 'Active', 'checkbox', 'boolean'), self::sort()],
                'note' => 'Translation text itself is imported from licensed datasets and is not edited here. Deleting sources is disabled; deactivate instead.'],
            'tafsir' => ['module' => 'quran', 'label' => 'Tafsir sources', 'singular' => 'tafsir source', 'permission' => 'quran.manage', 'model' => TafsirSource::class, 'order' => ['sort', 'id'], 'search' => ['name', 'author'], 'slug' => ['slug', 'name'], 'delete' => false,
                'columns' => [['name', 'Name'], ['author', 'Author'], ['language', 'Lang'], ['license', 'Licence'], ['is_active', 'Active', 'bool']],
                'fields' => [self::f('name', 'Name', 'text', 'required|string|max:255'), self::f('author', 'Author'), self::f('language', 'Language code', 'text', 'required|string|max:8'), self::f('source', 'Source'), self::f('source_url', 'Source URL', 'url', 'nullable|url|max:255'), self::f('license', 'Licence'), self::f('attribution', 'Attribution text', 'textarea', 'nullable|string|max:2000'), self::f('is_active', 'Active', 'checkbox', 'boolean'), self::sort()]],
            'reciters' => ['module' => 'quran', 'label' => 'Reciters', 'singular' => 'reciter', 'permission' => 'quran.manage', 'model' => Reciter::class, 'order' => ['sort', 'id'], 'search' => ['name'], 'slug' => ['slug', 'name'],
                'columns' => [['name', 'Name'], ['style', 'Style'], ['audio_url_pattern', 'Audio URL pattern'], ['is_active', 'Active', 'bool']],
                'fields' => [self::f('name', 'Name', 'text', 'required|string|max:255'), self::f('style', 'Style', 'select', 'required|in:murattal,mujawwad,muallim', ['options' => ['murattal' => 'Murattal', 'mujawwad' => 'Mujawwad', 'muallim' => 'Teaching (Muallim)']]), self::f('audio_url_pattern', 'Audio URL pattern', 'text', ['nullable', 'string', 'max:255', 'regex:#^https://.*\{ayah\}.*$#'], ['hint' => 'HTTPS URL containing {ayah}, replaced with the global ayah number (1-6236).']), self::f('source', 'Source / attribution'), self::f('is_active', 'Active', 'checkbox', 'boolean'), self::sort()]],

            // ---- Worship ----
            'adhkar-categories' => ['module' => 'worship', 'label' => 'Adhkar categories', 'singular' => 'adhkar category', 'permission' => 'worship.manage', 'model' => AdhkarCategory::class, 'order' => ['sort', 'id'], 'search' => ['title_en', 'title_ar'], 'slug' => ['slug', 'title_en'],
                'columns' => [['title_en', 'Title'], ['group', 'Group'], ['is_published', 'Published', 'bool'], ['sort', 'Order']],
                'fields' => [self::f('title_ar', 'Title (Arabic)', 'text', 'required|string|max:255', ['rtl' => true]), self::f('title_en', 'Title (English)'), self::f('group', 'Group', 'select', 'required|in:morning,evening,after_prayer,sleep,daily,other', ['options' => ['morning' => 'Morning', 'evening' => 'Evening', 'after_prayer' => 'After prayer', 'sleep' => 'Sleep', 'daily' => 'Daily', 'other' => 'Other']]), self::f('source', 'Source'), self::f('is_published', 'Published', 'checkbox', 'boolean'), self::sort()]],
            'adhkar-entries' => ['module' => 'worship', 'label' => 'Adhkar entries', 'singular' => 'adhkar entry', 'permission' => 'worship.manage', 'model' => AdhkarEntry::class, 'order' => ['adhkar_category_id', 'sort'], 'search' => ['text_ar', 'translation_en'], 'with' => ['category'],
                'columns' => [['text_ar', 'Arabic', 'limit'], ['category.title_en', 'Category'], ['repeat_count', 'Repeat'], ['is_verified', 'Verified', 'bool']],
                'fields' => [self::f('adhkar_category_id', 'Category', 'select', 'required|exists:adhkar_categories,id', ['options' => self::rel(AdhkarCategory::class, 'title_en')]), self::f('text_ar', 'Arabic text', 'textarea', 'required|string|max:4000', ['rtl' => true, 'hint' => 'Paste from the authenticated source. Do not retype or generate religious text.']), self::f('transliteration', 'Transliteration', 'textarea', 'nullable|string|max:4000'), self::f('translation_en', 'English translation', 'textarea', 'nullable|string|max:4000'), self::f('reference', 'Reference (collection and number)'), self::f('repeat_count', 'Repeat count', 'number', 'required|integer|min:1|max:1000', ['default' => 1]), self::f('audio_url', 'Audio URL', 'url', 'nullable|url|max:255'), self::f('is_verified', 'Reviewed by a qualified reviewer', 'checkbox', 'boolean'), self::sort()]],
            'tajweed' => ['module' => 'worship', 'label' => 'Tajweed resources', 'singular' => 'tajweed resource', 'permission' => 'worship.manage', 'model' => TajweedResource::class, 'order' => ['sort', 'id'], 'search' => ['title', 'summary'], 'slug' => ['slug', 'title'],
                'columns' => [['title', 'Title'], ['rule_group', 'Group'], ['is_published', 'Published', 'bool'], ['sort', 'Order']],
                'fields' => [self::f('title', 'Title', 'text', 'required|string|max:255'), self::f('rule_group', 'Rule group'), self::f('summary', 'Summary', 'textarea', 'nullable|string|max:500'), self::f('body', 'Lesson (Markdown)', 'textarea', 'nullable|string|max:60000', ['rows' => 12]), self::f('example_ar', 'Arabic example', 'text', 'nullable|string|max:255', ['rtl' => true]), self::f('audio_url', 'Audio URL', 'url', 'nullable|url|max:255'), self::f('is_published', 'Published', 'checkbox', 'boolean'), self::sort()]],
            'guides' => ['module' => 'worship', 'label' => 'Hajj & Umrah guides', 'singular' => 'guide', 'permission' => 'worship.manage', 'model' => Guide::class, 'order' => ['sort', 'id'], 'search' => ['title'], 'slug' => ['slug', 'title'],
                'columns' => [['title', 'Title'], ['type', 'Type'], ['is_published', 'Published', 'bool'], ['sort', 'Order']],
                'fields' => [self::f('title', 'Title', 'text', 'required|string|max:255'), self::f('type', 'Type', 'select', 'required|in:hajj,umrah,travel,duas', ['options' => ['hajj' => 'Hajj', 'umrah' => 'Umrah', 'travel' => 'Travel', 'duas' => 'Duas']]), self::f('summary', 'Summary', 'textarea', 'nullable|string|max:1000'), self::f('is_published', 'Published', 'checkbox', 'boolean'), self::sort()]],
            'guide-steps' => ['module' => 'worship', 'label' => 'Guide steps', 'singular' => 'guide step', 'permission' => 'worship.manage', 'model' => GuideStep::class, 'order' => ['guide_id', 'sort'], 'search' => ['title', 'body'], 'with' => ['guide'],
                'columns' => [['title', 'Step'], ['guide.title', 'Guide'], ['sort', 'Order']],
                'fields' => [self::f('guide_id', 'Guide', 'select', 'required|exists:guides,id', ['options' => self::rel(Guide::class, 'title')]), self::f('title', 'Title', 'text', 'required|string|max:255'), self::f('body', 'Instructions', 'textarea', 'required|string|max:10000', ['rows' => 6]), self::f('arabic_text', 'Arabic text (dua)', 'textarea', 'nullable|string|max:4000', ['rtl' => true]), self::f('transliteration', 'Transliteration', 'textarea', 'nullable|string|max:4000'), self::f('translation_en', 'English translation', 'textarea', 'nullable|string|max:4000'), self::f('reference', 'Reference'), self::sort()]],
            'guide-checklist' => ['module' => 'worship', 'label' => 'Guide checklists', 'singular' => 'checklist item', 'permission' => 'worship.manage', 'model' => GuideChecklistItem::class, 'order' => ['guide_id', 'category', 'sort'], 'search' => ['label'], 'with' => ['guide'],
                'columns' => [['label', 'Item'], ['category', 'Category'], ['guide.title', 'Guide']],
                'fields' => [self::f('guide_id', 'Guide', 'select', 'required|exists:guides,id', ['options' => self::rel(Guide::class, 'title')]), self::f('category', 'Category', 'text', 'required|string|max:48', ['default' => 'general']), self::f('label', 'Item', 'text', 'required|string|max:255'), self::sort()]],

            // ---- Academy ----
            'course-categories' => ['module' => 'academy', 'label' => 'Course categories', 'singular' => 'course category', 'permission' => 'academy.manage', 'model' => CourseCategory::class, 'order' => ['sort', 'id'], 'search' => ['name'], 'slug' => ['slug', 'name'],
                'columns' => [['name', 'Name'], ['icon', 'Icon'], ['sort', 'Order']],
                'fields' => [self::f('name', 'Name', 'text', 'required|string|max:255'), self::f('description', 'Description'), self::f('icon', 'Lucide icon name', 'text', 'required|string|max:48|regex:/^[a-z0-9-]+$/', ['default' => 'book-open']), self::sort()]],
            'instructors' => ['module' => 'academy', 'label' => 'Instructors', 'singular' => 'instructor', 'permission' => 'academy.manage', 'model' => Instructor::class, 'order' => ['name'], 'search' => ['name'],
                'columns' => [['name', 'Name'], ['title', 'Title']],
                'fields' => [self::f('name', 'Name', 'text', 'required|string|max:255'), self::f('title', 'Title / credentials'), self::f('bio', 'Biography', 'textarea', 'nullable|string|max:3000'), self::f('photo_path', 'Photo', 'image', 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048')]],
            'courses' => ['module' => 'academy', 'label' => 'Courses', 'singular' => 'course', 'permission' => 'academy.manage', 'model' => Course::class, 'order' => ['sort', 'id'], 'search' => ['title'], 'slug' => ['slug', 'title'], 'with' => ['category'],
                'columns' => [['title', 'Title'], ['category.name', 'Category'], ['level', 'Level'], ['is_premium', 'Plus', 'bool'], ['is_published', 'Published', 'bool']],
                'fields' => [self::f('title', 'Title', 'text', 'required|string|max:255'), self::f('course_category_id', 'Category', 'select', 'nullable|exists:course_categories,id', ['options' => self::rel(CourseCategory::class), 'blank' => true]), self::f('instructor_id', 'Instructor', 'select', 'nullable|exists:instructors,id', ['options' => self::rel(Instructor::class), 'blank' => true]), self::f('summary', 'Summary', 'text', 'nullable|string|max:500'), self::f('description', 'Description', 'textarea', 'nullable|string|max:10000', ['rows' => 6]), self::f('level', 'Level', 'select', 'required|in:beginner,intermediate,advanced', ['options' => ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced']]), self::f('cover_theme', 'Cover illustration', 'select', 'required|string|max:24', ['options' => self::coverThemes()]), self::f('is_premium', 'Plus members only', 'checkbox', 'boolean'), self::f('is_published', 'Published', 'checkbox', 'boolean'), self::sort()]],
            'lessons' => ['module' => 'academy', 'label' => 'Lessons', 'singular' => 'lesson', 'permission' => 'academy.manage', 'model' => CourseLesson::class, 'order' => ['course_id', 'sort'], 'search' => ['title'], 'with' => ['course'],
                'columns' => [['title', 'Lesson'], ['course.title', 'Course'], ['type', 'Type'], ['is_published', 'Published', 'bool']],
                'fields' => [self::f('course_id', 'Course', 'select', 'required|exists:courses,id', ['options' => self::rel(Course::class, 'title')]), self::f('title', 'Title', 'text', 'required|string|max:255'), self::f('type', 'Type', 'select', 'required|in:text,video,audio', ['options' => ['text' => 'Text', 'video' => 'Video', 'audio' => 'Audio']]), self::f('body', 'Lesson text (Markdown)', 'textarea', 'nullable|string|max:60000', ['rows' => 12]), self::f('media_url', 'Media URL (video/audio, HTTPS)', 'url', 'nullable|url|max:255'), self::f('duration_minutes', 'Minutes', 'number', 'required|integer|min:1|max:600', ['default' => 5]), self::f('is_free_preview', 'Free preview', 'checkbox', 'boolean'), self::f('is_published', 'Published', 'checkbox', 'boolean'), self::sort()]],
            'quizzes' => ['module' => 'academy', 'label' => 'Quizzes', 'singular' => 'quiz', 'permission' => 'academy.manage', 'model' => Quiz::class, 'order' => ['course_id', 'id'], 'search' => ['title'], 'with' => ['course'],
                'columns' => [['title', 'Quiz'], ['course.title', 'Course'], ['pass_mark', 'Pass %'], ['is_published', 'Published', 'bool']],
                'fields' => [self::f('course_id', 'Course', 'select', 'required|exists:courses,id', ['options' => self::rel(Course::class, 'title')]), self::f('title', 'Title', 'text', 'required|string|max:255'), self::f('pass_mark', 'Pass mark (%)', 'number', 'required|integer|min:1|max:100', ['default' => 70]), self::f('is_published', 'Published', 'checkbox', 'boolean')]],
            'quiz-questions' => ['module' => 'academy', 'label' => 'Quiz questions', 'singular' => 'question', 'permission' => 'academy.manage', 'model' => QuizQuestion::class, 'order' => ['quiz_id', 'sort'], 'search' => ['body'], 'with' => ['quiz'], 'handler' => 'quiz-question',
                'columns' => [['body', 'Question', 'limit'], ['quiz.title', 'Quiz'], ['type', 'Type']],
                'fields' => [self::f('quiz_id', 'Quiz', 'select', 'required|exists:quizzes,id', ['options' => self::rel(Quiz::class, 'title')]), self::f('body', 'Question', 'textarea', 'required|string|max:2000'), self::f('type', 'Type', 'select', 'required|in:single,multiple,true_false', ['options' => ['single' => 'Single answer', 'multiple' => 'Multiple answers', 'true_false' => 'True / false']]), self::f('answers_text', 'Answers', 'textarea', 'required|string|max:4000', ['virtual' => true, 'rows' => 5, 'hint' => 'One answer per line. Start the correct answer(s) with an asterisk (*).']), self::f('explanation', 'Explanation (shown after submitting)', 'textarea', 'nullable|string|max:2000'), self::sort()]],

            // ---- Kids ----
            'kids-courses' => ['module' => 'kids', 'label' => 'Kids courses', 'singular' => 'kids course', 'permission' => 'kids.manage', 'model' => KidsCourse::class, 'order' => ['sort', 'id'], 'search' => ['title'], 'slug' => ['slug', 'title'],
                'columns' => [['title', 'Title'], ['category', 'Category'], ['age_min', 'From age'], ['is_published', 'Published', 'bool']],
                'fields' => [self::f('title', 'Title', 'text', 'required|string|max:255'), self::f('category', 'Category', 'select', 'required|in:quran,arabic,manners,faith', ['options' => ['quran' => 'Quran', 'arabic' => 'Arabic', 'manners' => 'Manners', 'faith' => 'Faith']]), self::f('summary', 'Summary', 'text', 'nullable|string|max:300'), self::f('age_min', 'Minimum age', 'number', 'required|integer|min:2|max:17', ['default' => 4]), self::f('age_max', 'Maximum age', 'number', 'required|integer|min:2|max:17|gte:age_min', ['default' => 12]), self::f('icon', 'Lucide icon', 'text', 'required|string|max:48|regex:/^[a-z0-9-]+$/', ['default' => 'book-open']), self::f('color', 'Colour', 'select', 'required|in:green,gold,blue,pink,purple', ['options' => ['green' => 'Green', 'gold' => 'Gold', 'blue' => 'Blue', 'pink' => 'Pink', 'purple' => 'Purple']]), self::f('is_published', 'Published', 'checkbox', 'boolean'), self::sort()]],
            'kids-lessons' => ['module' => 'kids', 'label' => 'Kids lessons', 'singular' => 'kids lesson', 'permission' => 'kids.manage', 'model' => KidsLesson::class, 'order' => ['kids_course_id', 'sort'], 'search' => ['title'], 'with' => ['course'],
                'columns' => [['title', 'Lesson'], ['course.title', 'Course'], ['sort', 'Order']],
                'fields' => [self::f('kids_course_id', 'Course', 'select', 'required|exists:kids_courses,id', ['options' => self::rel(KidsCourse::class, 'title')]), self::f('title', 'Title', 'text', 'required|string|max:255'), self::f('body', 'Text', 'textarea', 'required|string|max:5000'), self::f('arabic_text', 'Arabic text', 'text', 'nullable|string|max:255', ['rtl' => true]), self::f('transliteration', 'Transliteration', 'text', 'nullable|string|max:255'), self::f('audio_url', 'Audio URL', 'url', 'nullable|url|max:255'), self::sort()]],
            'kids-stories' => ['module' => 'kids', 'label' => 'Kids stories', 'singular' => 'story', 'permission' => 'kids.manage', 'model' => KidsStory::class, 'order' => ['sort', 'id'], 'search' => ['title'], 'slug' => ['slug', 'title'],
                'columns' => [['title', 'Title'], ['reference', 'Source'], ['is_published', 'Published', 'bool']],
                'fields' => [self::f('title', 'Title', 'text', 'required|string|max:255'), self::f('body', 'Story', 'textarea', 'required|string|max:20000', ['rows' => 10, 'hint' => 'Separate paragraphs with a blank line.']), self::f('moral', 'Lesson'), self::f('reference', 'Source (e.g. Quran 12:4 or a hadith reference)'), self::f('age_min', 'Minimum age', 'number', 'required|integer|min:2|max:17', ['default' => 4]), self::f('age_max', 'Maximum age', 'number', 'required|integer|min:2|max:17|gte:age_min', ['default' => 12]), self::f('cover_theme', 'Cover illustration', 'select', 'required|string|max:24', ['options' => self::coverThemes()]), self::f('is_published', 'Published', 'checkbox', 'boolean'), self::sort()]],
            'kids-games' => ['module' => 'kids', 'label' => 'Kids games', 'singular' => 'game', 'permission' => 'kids.manage', 'model' => KidsGame::class, 'order' => ['sort', 'id'], 'search' => ['title'], 'slug' => ['slug', 'title'],
                'columns' => [['title', 'Title'], ['type', 'Type'], ['is_published', 'Published', 'bool']],
                'fields' => [self::f('title', 'Title', 'text', 'required|string|max:255'), self::f('type', 'Type', 'select', 'required|in:match,quiz', ['options' => ['match' => 'Matching pairs', 'quiz' => 'Multiple-choice quiz']]), self::f('summary', 'Summary', 'text', 'nullable|string|max:300'), self::f('config', 'Configuration (JSON)', 'json', 'required|json', ['rows' => 10, 'hint' => 'match: {"pairs":[{"ar":"...","en":"..."}]} · quiz: {"questions":[{"q":"...","options":["a","b"],"answer":0}]}']), self::f('age_min', 'Minimum age', 'number', 'required|integer|min:2|max:17', ['default' => 4]), self::f('age_max', 'Maximum age', 'number', 'required|integer|min:2|max:17|gte:age_min', ['default' => 12]), self::f('icon', 'Lucide icon', 'text', 'required|string|max:48|regex:/^[a-z0-9-]+$/', ['default' => 'gamepad-2']), self::f('color', 'Colour', 'select', 'required|in:green,gold,blue,pink,purple', ['options' => ['green' => 'Green', 'gold' => 'Gold', 'blue' => 'Blue', 'pink' => 'Pink', 'purple' => 'Purple']]), self::f('is_published', 'Published', 'checkbox', 'boolean'), self::sort()]],

            // ---- Events ----
            'event-categories' => ['module' => 'events', 'label' => 'Event categories', 'singular' => 'event category', 'permission' => 'events.manage', 'model' => EventCategory::class, 'order' => ['sort', 'id'], 'search' => ['name'], 'slug' => ['slug', 'name'],
                'columns' => [['name', 'Name'], ['sort', 'Order']], 'fields' => [self::f('name', 'Name', 'text', 'required|string|max:255'), self::sort()]],
            'events' => ['module' => 'events', 'label' => 'Events', 'singular' => 'event', 'permission' => 'events.manage', 'model' => Event::class, 'order' => ['-starts_at'], 'search' => ['title', 'city'], 'slug' => ['slug', 'title'], 'with' => ['category'],
                'columns' => [['title', 'Event'], ['starts_at', 'Starts', 'datetime'], ['mode', 'Mode'], ['price_kobo', 'Price', 'money'], ['status', 'Status', 'badge']],
                'fields' => [self::f('title', 'Title', 'text', 'required|string|max:255'), self::f('event_category_id', 'Category', 'select', 'nullable|exists:event_categories,id', ['options' => self::rel(EventCategory::class), 'blank' => true]), self::f('summary', 'Summary', 'text', 'nullable|string|max:500'), self::f('description', 'Description', 'textarea', 'nullable|string|max:10000', ['rows' => 6]), self::f('starts_at', 'Starts (Africa/Lagos time)', 'datetime', 'required|date'), self::f('ends_at', 'Ends', 'datetime', 'nullable|date|after:starts_at'), self::f('mode', 'Mode', 'select', 'required|in:in_person,online,hybrid', ['options' => ['in_person' => 'In person', 'online' => 'Online', 'hybrid' => 'Hybrid']]), self::f('venue_name', 'Venue'), self::f('address', 'Address'), self::f('city', 'City'), self::f('latitude', 'Latitude', 'number', 'nullable|numeric|between:-90,90', ['step' => 'any']), self::f('longitude', 'Longitude', 'number', 'nullable|numeric|between:-180,180', ['step' => 'any']), self::f('online_url', 'Online meeting URL', 'url', 'nullable|url|max:255'), self::f('capacity', 'Capacity (blank = unlimited)', 'number', 'nullable|integer|min:1|max:1000000'), self::f('price_kobo', 'Ticket price (NGN, 0 = free)', 'money', 'required|numeric|min:0|max:100000000', ['default' => 0]), self::f('cover_theme', 'Cover illustration', 'select', 'required|string|max:24', ['options' => self::coverThemes()]), self::f('organizer_name', 'Organiser'), self::f('status', 'Status', 'select', 'required|in:draft,published,cancelled', ['options' => ['draft' => 'Draft', 'published' => 'Published', 'cancelled' => 'Cancelled']])]],

            // ---- Directories ----
            'business-categories' => ['module' => 'directories', 'label' => 'Business categories', 'singular' => 'business category', 'permission' => 'directory.manage', 'model' => BusinessCategory::class, 'order' => ['sort', 'id'], 'search' => ['name'], 'slug' => ['slug', 'name'],
                'columns' => [['name', 'Name'], ['icon', 'Icon']], 'fields' => [self::f('name', 'Name', 'text', 'required|string|max:255'), self::f('icon', 'Lucide icon', 'text', 'required|string|max:48|regex:/^[a-z0-9-]+$/', ['default' => 'store']), self::sort()]],
            'mosques' => ['module' => 'directories', 'label' => 'Mosques', 'singular' => 'mosque', 'permission' => 'directory.manage', 'model' => Mosque::class, 'order' => ['name'], 'search' => ['name', 'city'], 'slug' => ['slug', 'name'],
                'columns' => [['name', 'Name'], ['city', 'City'], ['verification_status', 'Verification', 'badge'], ['status', 'Status', 'badge']],
                'fields' => [self::f('name', 'Name', 'text', 'required|string|max:255'), self::f('description', 'Description', 'textarea', 'nullable|string|max:3000'), self::f('address', 'Address'), self::f('city', 'City', 'text', 'required|string|max:255'), self::f('state', 'State'), self::f('country', 'Country', 'text', 'required|string|max:80', ['default' => 'Nigeria']), self::f('latitude', 'Latitude', 'number', 'nullable|numeric|between:-90,90', ['step' => 'any']), self::f('longitude', 'Longitude', 'number', 'nullable|numeric|between:-180,180', ['step' => 'any']), self::f('phone', 'Phone', 'text', 'nullable|string|max:40'), self::f('website', 'Website', 'url', 'nullable|url|max:255'), self::f('imam', 'Imam'), self::f('jummah_time', "Jumu'ah time", 'text', 'nullable|string|max:32'), self::f('facilities', 'Facilities (comma separated)', 'tags', 'nullable|string|max:500'), self::f('status', 'Listing status', 'select', 'required|in:draft,published', ['options' => ['draft' => 'Draft', 'published' => 'Published']])],
                'actions' => [['verify', 'Mark details verified', 'directory.verify'], ['unverify', 'Clear verification', 'directory.verify']]],
            'businesses' => ['module' => 'directories', 'label' => 'Businesses', 'singular' => 'business', 'permission' => 'directory.manage', 'model' => Business::class, 'order' => ['-id'], 'search' => ['name', 'city'], 'slug' => ['slug', 'name'], 'with' => ['category'],
                'columns' => [['name', 'Name'], ['city', 'City'], ['halal_status', 'Halal', 'badge'], ['status', 'Status', 'badge']],
                'filters' => [['status', 'Status', ['pending' => 'Pending review', 'published' => 'Published', 'rejected' => 'Rejected']], ['halal_status', 'Halal', ['unverified' => 'Unverified', 'pending' => 'Claim pending', 'verified' => 'Verified', 'rejected' => 'Rejected']]],
                'fields' => [self::f('name', 'Name', 'text', 'required|string|max:255'), self::f('business_category_id', 'Category', 'select', 'nullable|exists:business_categories,id', ['options' => self::rel(BusinessCategory::class), 'blank' => true]), self::f('description', 'Description', 'textarea', 'nullable|string|max:3000'), self::f('address', 'Address'), self::f('city', 'City', 'text', 'required|string|max:255'), self::f('state', 'State'), self::f('country', 'Country', 'text', 'required|string|max:80', ['default' => 'Nigeria']), self::f('latitude', 'Latitude', 'number', 'nullable|numeric|between:-90,90', ['step' => 'any']), self::f('longitude', 'Longitude', 'number', 'nullable|numeric|between:-180,180', ['step' => 'any']), self::f('phone', 'Phone', 'text', 'nullable|string|max:40'), self::f('email', 'Email', 'email', 'nullable|email|max:255'), self::f('website', 'Website', 'url', 'nullable|url|max:255'), self::f('halal_certifier', 'Halal certifier'), self::f('halal_certificate_number', 'Certificate number'), self::f('halal_certificate_expires_at', 'Certificate expiry', 'date', 'nullable|date'), self::f('status', 'Listing status', 'select', 'required|in:pending,published,rejected', ['options' => ['pending' => 'Pending review', 'published' => 'Published', 'rejected' => 'Rejected']])],
                'actions' => [['halal_verify', 'Verify halal certificate', 'directory.verify'], ['halal_reject', 'Reject halal claim', 'directory.verify'], ['halal_clear', 'Clear halal status', 'directory.verify']]],

            // ---- Subscriptions ----
            'plans' => ['module' => 'subscriptions', 'label' => 'Plans', 'singular' => 'plan', 'permission' => 'subscriptions.manage', 'model' => SubscriptionPlan::class, 'order' => ['sort', 'id'], 'search' => ['name'], 'slug' => ['slug', 'name'], 'delete' => false,
                'columns' => [['name', 'Plan'], ['price_kobo', 'Price', 'money'], ['interval', 'Interval'], ['is_active', 'Active', 'bool'], ['is_featured', 'Featured', 'bool']],
                'fields' => [self::f('name', 'Name', 'text', 'required|string|max:255'), self::f('description', 'Description'), self::f('price_kobo', 'Price (NGN)', 'money', 'required|numeric|min:0|max:100000000', ['default' => 0]), self::f('interval', 'Interval', 'select', 'required|in:free,month,year,lifetime', ['options' => ['free' => 'Free', 'month' => 'Monthly', 'year' => 'Yearly', 'lifetime' => 'Lifetime']]), self::f('features', 'Feature bullets (one per line)', 'lines', 'nullable|string|max:3000'), self::f('entitlements', 'Entitlements (JSON)', 'json', 'nullable|json', ['rows' => 6, 'hint' => 'e.g. {"ai_daily_limit":50,"family_children":5,"premium_courses":true,"offline_downloads":true}']), self::f('is_active', 'Available to users', 'checkbox', 'boolean'), self::f('is_featured', 'Highlight as popular', 'checkbox', 'boolean'), self::sort()],
                'note' => 'Plan deletion is disabled to protect payment history; deactivate a plan instead.'],

            // ---- AI ----
            'ai-providers' => ['module' => 'ai', 'label' => 'AI providers', 'singular' => 'AI provider', 'permission' => 'ai.manage', 'model' => AiProvider::class, 'order' => ['id'], 'search' => ['name'], 'slug' => ['slug', 'name'],
                'columns' => [['name', 'Provider'], ['base_url', 'Base URL'], ['api_key', 'API key', 'secret_state'], ['is_active', 'Active', 'bool'], ['is_default', 'Default', 'bool']],
                'fields' => [self::f('name', 'Name', 'text', 'required|string|max:255'), self::f('driver', 'API style', 'select', 'required|in:openai_compatible', ['options' => ['openai_compatible' => 'OpenAI-compatible (/chat/completions)']]), self::f('base_url', 'Base URL', 'url', 'nullable|url|max:255', ['hint' => 'e.g. https://api.openai.com/v1']), self::f('api_key', 'API key', 'secret', 'nullable|string|max:500', ['hint' => 'Stored encrypted. Never shown again; leave blank to keep the current key.']), self::f('is_active', 'Active', 'checkbox', 'boolean'), self::f('is_default', 'Default provider', 'checkbox', 'boolean')]],
            'ai-models' => ['module' => 'ai', 'label' => 'AI models', 'singular' => 'AI model', 'permission' => 'ai.manage', 'model' => AiModel::class, 'order' => ['ai_provider_id', 'id'], 'search' => ['label', 'model_id'], 'with' => ['provider'],
                'columns' => [['label', 'Label'], ['model_id', 'Model ID'], ['provider.name', 'Provider'], ['is_default', 'Default', 'bool'], ['is_active', 'Active', 'bool']],
                'fields' => [self::f('ai_provider_id', 'Provider', 'select', 'required|exists:ai_providers,id', ['options' => self::rel(AiProvider::class)]), self::f('model_id', 'Model ID', 'text', 'required|string|max:255'), self::f('label', 'Label', 'text', 'required|string|max:255'), self::f('input_cost_per_million', 'Input cost (USD per 1M tokens)', 'number', 'required|numeric|min:0|max:100000', ['step' => 'any', 'default' => 0]), self::f('output_cost_per_million', 'Output cost (USD per 1M tokens)', 'number', 'required|numeric|min:0|max:100000', ['step' => 'any', 'default' => 0]), self::f('max_output_tokens', 'Max output tokens', 'number', 'required|integer|min:50|max:8000', ['default' => 800]), self::f('temperature', 'Temperature', 'number', 'required|numeric|min:0|max:1', ['step' => '0.05', 'default' => 0.2]), self::f('is_default', 'Default model', 'checkbox', 'boolean'), self::f('is_active', 'Active', 'checkbox', 'boolean')]],
            'ai-sources' => ['module' => 'ai', 'label' => 'Knowledge sources', 'singular' => 'knowledge source', 'permission' => 'ai.manage', 'model' => AiSource::class, 'order' => ['-id'], 'search' => ['title', 'content', 'reference'],
                'columns' => [['title', 'Title'], ['type', 'Type'], ['reference', 'Reference'], ['is_verified', 'Verified', 'bool'], ['is_active', 'Active', 'bool']],
                'fields' => [self::f('title', 'Title', 'text', 'required|string|max:255'), self::f('type', 'Type', 'select', 'required|in:quran,hadith,book,article,fatwa', ['options' => ['quran' => 'Quran', 'hadith' => 'Hadith', 'book' => 'Book', 'article' => 'Article', 'fatwa' => 'Scholarly opinion']]), self::f('reference', 'Citation (collection, number, page)'), self::f('url', 'Source URL', 'url', 'nullable|url|max:255'), self::f('content', 'Content (exact text from the source)', 'textarea', 'required|string|max:20000', ['rows' => 8, 'hint' => 'Only add text you have the right to use and have checked against the original source.']), self::f('is_verified', 'Checked against the original source', 'checkbox', 'boolean'), self::f('is_active', 'Available to SIRAJ AI', 'checkbox', 'boolean')]],

            // ---- System ----
            'locations' => ['module' => 'worship', 'label' => 'Prayer locations (cities)', 'singular' => 'location', 'permission' => 'worship.manage', 'model' => Location::class, 'order' => ['country', 'name'], 'search' => ['name', 'country'],
                'columns' => [['name', 'City'], ['country', 'Country'], ['timezone', 'Timezone']],
                'fields' => [self::f('name', 'City', 'text', 'required|string|max:255'), self::f('region', 'Region / state'), self::f('country', 'Country', 'text', 'required|string|max:255'), self::f('country_code', 'Country code (ISO-2)', 'text', 'required|string|size:2'), self::f('latitude', 'Latitude', 'number', 'required|numeric|between:-90,90', ['step' => 'any']), self::f('longitude', 'Longitude', 'number', 'required|numeric|between:-180,180', ['step' => 'any']), self::f('timezone', 'IANA timezone', 'text', 'required|timezone:all|max:64')]],
        ]);
    }

    public static function coverThemes(): array
    {
        return ['mosque_green' => 'Green mosque', 'mosque_blue' => 'Blue mosque', 'lanterns' => 'Lanterns', 'dawn' => 'Dawn', 'quran_open' => 'Open Quran', 'mosque_purple' => 'Purple mosque'];
    }
}
