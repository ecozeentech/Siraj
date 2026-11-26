<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Academy\Course;
use App\Models\Academy\CourseEnrolment;
use App\Models\Academy\CourseLesson;
use App\Models\Academy\LessonProgress;
use App\Models\Academy\Quiz;
use App\Models\Academy\QuizAttempt;
use App\Notifications\GeneralNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AcademyController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $filter = in_array($request->query('filter'), ['progress', 'completed'], true) ? $request->query('filter') : 'all';
        $enrolments = CourseEnrolment::query()->where('user_id', $user->id)->with('course')->get()->keyBy('course_id');

        $courses = Course::query()->where('is_published', true)->with('category', 'instructor')->withCount(['publishedLessons as lessons_count'])->orderBy('sort')->get()
            ->map(function (Course $c) use ($enrolments) {
                $e = $enrolments[$c->id] ?? null;
                $c->setRelation('enrolment', $e);
                $c->progress = $e ? $e->progress_percent : 0;
                $c->state = ! $e ? 'new' : ($e->completed_at ? 'completed' : 'progress');

                return $c;
            })
            ->when($filter === 'progress', fn ($c) => $c->where('state', 'progress'))
            ->when($filter === 'completed', fn ($c) => $c->where('state', 'completed'))
            ->values();

        return view('app.academy.index', ['courses' => $courses, 'filter' => $filter, 'featured' => Course::query()->where('is_published', true)->orderBy('sort')->first()]);
    }

    public function course(Request $request, Course $course)
    {
        abort_unless($course->is_published, 404);
        $user = $request->user();
        $enrolment = CourseEnrolment::query()->where('user_id', $user->id)->where('course_id', $course->id)->first();
        $lessons = $course->publishedLessons()->get();
        $done = LessonProgress::query()->where('user_id', $user->id)->whereNotNull('completed_at')->whereIn('course_lesson_id', $lessons->pluck('id'))->pluck('course_lesson_id')->flip();
        $quizzes = $course->quizzes()->where('is_published', true)->withCount('questions')->get();
        $passed = QuizAttempt::query()->where('user_id', $user->id)->where('passed', true)->whereIn('quiz_id', $quizzes->pluck('id'))->pluck('quiz_id')->flip();

        return view('app.academy.course', ['course' => $course->load('instructor', 'category'), 'enrolment' => $enrolment, 'lessons' => $lessons, 'done' => $done, 'quizzes' => $quizzes, 'passed' => $passed, 'locked' => $this->isLocked($course, $user)]);
    }

    private function isLocked(Course $course, $user): bool
    {
        return $course->is_premium && ! $user->entitlement('premium_courses', false);
    }

    public function enrol(Request $request, Course $course)
    {
        abort_unless($course->is_published, 404);
        if ($this->isLocked($course, $request->user())) {
            return redirect()->route('app.subscription.index')->with('warning', 'This course is part of SIRAJ Plus.');
        }
        CourseEnrolment::query()->firstOrCreate(['user_id' => $request->user()->id, 'course_id' => $course->id]);
        $first = $course->publishedLessons()->first();

        return $first ? redirect()->route('app.academy.lesson', [$course, $first])->with('status', 'You are enrolled. Bismillah!') : back()->with('status', 'You are enrolled.');
    }

    public function lesson(Request $request, Course $course, CourseLesson $lesson)
    {
        abort_unless($course->is_published && $lesson->course_id === $course->id && $lesson->is_published, 404);
        $user = $request->user();
        $enrolment = CourseEnrolment::query()->where('user_id', $user->id)->where('course_id', $course->id)->first();
        if ($this->isLocked($course, $user) && ! $lesson->is_free_preview) {
            return redirect()->route('app.academy.course', $course)->with('warning', 'This lesson is part of SIRAJ Plus.');
        }
        if (! $enrolment && ! $lesson->is_free_preview) {
            return redirect()->route('app.academy.course', $course)->with('warning', 'Enrol to start this lesson.');
        }
        $lessons = $course->publishedLessons()->get();
        $idx = $lessons->search(fn ($l) => $l->id === $lesson->id);
        $progress = LessonProgress::query()->where('user_id', $user->id)->where('course_lesson_id', $lesson->id)->first();

        return view('app.academy.lesson', [
            'course' => $course, 'lesson' => $lesson->load('resources', 'quizzes'), 'prev' => $lessons[$idx - 1] ?? null, 'next' => $lessons[$idx + 1] ?? null,
            'progress' => $progress, 'enrolled' => (bool) $enrolment, 'index' => $idx + 1, 'total' => $lessons->count(),
        ]);
    }

    public function position(Request $request, CourseLesson $lesson)
    {
        $d = $request->validate(['seconds' => ['required', 'integer', 'min:0', 'max:86400']]);
        abort_unless(CourseEnrolment::query()->where('user_id', $request->user()->id)->where('course_id', $lesson->course_id)->exists(), 403);
        LessonProgress::query()->updateOrCreate(['user_id' => $request->user()->id, 'course_lesson_id' => $lesson->id], ['position_seconds' => $d['seconds']]);

        return response()->json(['ok' => true]);
    }

    public function complete(Request $request, Course $course, CourseLesson $lesson)
    {
        abort_unless($lesson->course_id === $course->id, 404);
        $user = $request->user();
        abort_unless(CourseEnrolment::query()->where('user_id', $user->id)->where('course_id', $course->id)->exists(), 403);
        LessonProgress::query()->updateOrCreate(['user_id' => $user->id, 'course_lesson_id' => $lesson->id], ['completed_at' => now()]);
        $certified = $this->checkCompletion($course, $request->user());

        $next = $course->publishedLessons()->where('sort', '>', $lesson->sort)->first();
        if ($certified) {
            return redirect()->route('app.academy.course', $course)->with('status', 'Masha Allah! You completed the course.');
        }

        return $next ? redirect()->route('app.academy.lesson', [$course, $next])->with('status', 'Lesson completed.') : redirect()->route('app.academy.course', $course)->with('status', 'Lesson completed. Finish the remaining quizzes to earn your certificate.');
    }

    public function quiz(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->is_published, 404);
        $course = $quiz->course;
        abort_unless($course->is_published && CourseEnrolment::query()->where('user_id', $request->user()->id)->where('course_id', $course->id)->exists(), 403);
        $questions = $quiz->questions()->orderBy('sort')->with('answers')->get()->map(function ($q) {
            $q->setRelation('answers', $q->answers->shuffle()->values()); // fresh answer order each attempt

            return $q;
        });
        $best = QuizAttempt::query()->where('user_id', $request->user()->id)->where('quiz_id', $quiz->id)->max('score');

        return view('app.academy.quiz', ['quiz' => $quiz, 'course' => $course, 'questions' => $questions, 'best' => $best]);
    }

    public function submitQuiz(Request $request, Quiz $quiz)
    {
        $course = $quiz->course;
        abort_unless(CourseEnrolment::query()->where('user_id', $request->user()->id)->where('course_id', $course->id)->exists(), 403);
        $questions = $quiz->questions()->with('answers')->get();
        $given = (array) $request->input('answers', []);
        $correct = 0;
        $responses = [];

        foreach ($questions as $q) {
            $valid = $q->answers->pluck('id')->map(fn ($i) => (int) $i)->all();
            $picked = collect((array) ($given[$q->id] ?? []))->map(fn ($i) => (int) $i)->filter(fn ($i) => in_array($i, $valid, true))->unique()->sort()->values()->all();
            $right = $q->answers->where('is_correct', true)->pluck('id')->map(fn ($i) => (int) $i)->sort()->values()->all();
            $ok = $picked !== [] && $picked === $right;
            $correct += $ok ? 1 : 0;
            $responses[$q->id] = ['picked' => $picked, 'correct' => $ok];
        }
        $score = $questions->count() ? (int) round($correct / $questions->count() * 100) : 0;
        $attempt = QuizAttempt::query()->create([
            'user_id' => $request->user()->id, 'quiz_id' => $quiz->id, 'score' => $score, 'correct_count' => $correct, 'question_count' => $questions->count(),
            'passed' => $score >= $quiz->pass_mark, 'responses' => $responses, 'submitted_at' => now(),
        ]);
        if ($attempt->passed) {
            $this->checkCompletion($course, $request->user());
        }

        return redirect()->route('app.academy.quiz.result', $attempt);
    }

    public function result(Request $request, QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === $request->user()->id, 404);
        $quiz = $attempt->quiz()->with('course')->first();
        $questions = $quiz->questions()->orderBy('sort')->with('answers')->get();

        return view('app.academy.result', ['attempt' => $attempt, 'quiz' => $quiz, 'questions' => $questions]);
    }

    /** Marks the course complete and issues a certificate once every lesson is done and every quiz is passed. */
    private function checkCompletion(Course $course, $user): bool
    {
        $enrolment = CourseEnrolment::query()->where('user_id', $user->id)->where('course_id', $course->id)->first();
        if (! $enrolment || $enrolment->completed_at) {
            return false;
        }
        $lessonIds = $course->publishedLessons()->pluck('id');
        $doneLessons = LessonProgress::query()->where('user_id', $user->id)->whereNotNull('completed_at')->whereIn('course_lesson_id', $lessonIds)->count();
        $quizIds = $course->quizzes()->where('is_published', true)->pluck('id');
        $passedQuizzes = QuizAttempt::query()->where('user_id', $user->id)->where('passed', true)->whereIn('quiz_id', $quizIds)->distinct()->count('quiz_id');
        if ($lessonIds->isEmpty() || $doneLessons < $lessonIds->count() || $passedQuizzes < $quizIds->count()) {
            return false;
        }
        $enrolment->update(['completed_at' => now(), 'certificate_code' => 'SIRAJ-'.Str::upper(Str::random(10))]);
        $user->notify(new GeneralNotification('Course completed', "You completed {$course->title}. Your certificate is ready.", route('app.academy.certificate', $enrolment->certificate_code, false), 'academy', 'award'));

        return true;
    }

    public function certificate(Request $request, string $code)
    {
        $e = CourseEnrolment::query()->where('certificate_code', $code)->where('user_id', $request->user()->id)->with('course', 'user')->firstOrFail();

        return view('app.academy.certificate', ['enrolment' => $e]);
    }
}
