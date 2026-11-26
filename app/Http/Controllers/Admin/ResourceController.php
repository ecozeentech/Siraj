<?php

namespace App\Http\Controllers\Admin;

use App\Admin\Registry;
use App\Http\Controllers\Controller;
use App\Models\Academy\QuizAnswer;
use App\Models\Directory\Business;
use App\Models\Directory\Mosque;
use App\Services\Admin\AuditLogger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $def = $this->definition($request);
        $model = $def['model'];
        $q = $model::query();
        if (! empty($def['with'])) {
            $q->with($def['with']);
        }
        if ($term = trim((string) $request->query('q'))) {
            $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';
            $q->where(function ($w) use ($def, $like) {
                foreach ($def['search'] as $col) {
                    $w->orWhere($col, 'like', $like);
                }
            });
        }
        foreach ($def['filters'] ?? [] as [$col]) {
            if ($request->filled('f.'.$col)) {
                $q->where($col, $request->query('f')[$col]);
            }
        }
        foreach ($def['order'] as $col) {
            $q->orderBy(ltrim($col, '-'), str_starts_with($col, '-') ? 'desc' : 'asc');
        }

        return view('admin.resource.index', ['def' => $def, 'resource' => $request->route('resource'), 'rows' => $q->paginate(20)->withQueryString()]);
    }

    public function create(Request $request)
    {
        $def = $this->definition($request);
        $model = new ($def['model']);

        return view('admin.resource.form', ['def' => $def, 'resource' => $request->route('resource'), 'model' => $model, 'values' => $this->values($model, $def), 'fields' => $this->fields($def)]);
    }

    public function store(Request $request)
    {
        $def = $this->definition($request);
        $model = new ($def['model']);
        $data = $this->validated($request, $def, $model);
        $model->fill(collect($data)->reject(fn ($v, $k) => $this->virtual($def, $k))->all());
        $this->slug($def, $model, $data);
        if (Schema::hasColumn($model->getTable(), 'created_by')) {
            $model->created_by ??= Auth::guard('admin')->id();
        }
        $model->save();
        $this->after($def, $model, $data);
        AuditLogger::log('admin.'.$request->route('resource').'.created', $model, 'Created '.$def['singular'], $this->safe($data));

        return redirect()->route('admin.'.$def['module'].'.'.$request->route('resource').'.index')->with('status', ucfirst($def['singular']).' saved.');
    }

    public function edit(Request $request, int $id)
    {
        $def = $this->definition($request);
        $model = $this->find($def, $id);

        return view('admin.resource.form', ['def' => $def, 'resource' => $request->route('resource'), 'model' => $model, 'values' => $this->values($model, $def), 'fields' => $this->fields($def)]);
    }

    public function update(Request $request, int $id)
    {
        $def = $this->definition($request);
        $model = $this->find($def, $id);
        $data = $this->validated($request, $def, $model);
        $model->fill(collect($data)->reject(fn ($v, $k) => $this->virtual($def, $k))->all());
        $this->slug($def, $model, $data);
        $model->save();
        $this->after($def, $model, $data);
        AuditLogger::log('admin.'.$request->route('resource').'.updated', $model, 'Updated '.$def['singular'], $this->safe($data));

        return redirect()->route('admin.'.$def['module'].'.'.$request->route('resource').'.index')->with('status', ucfirst($def['singular']).' updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $def = $this->definition($request);
        abort_if(($def['delete'] ?? true) === false, 403, 'This record type cannot be deleted. Deactivate it instead.');
        $model = $this->find($def, $id);
        try {
            $model->delete();
        } catch (QueryException) {
            return back()->with('error', 'This record is still in use and cannot be deleted.');
        }
        AuditLogger::log('admin.'.$request->route('resource').'.deleted', $model, 'Deleted '.$def['singular']);

        return back()->with('status', ucfirst($def['singular']).' deleted.');
    }

    public function action(Request $request, int $id, string $action)
    {
        $def = $this->definition($request);
        $allowed = collect($def['actions'] ?? [])->firstWhere(0, $action);
        abort_unless($allowed, 404);
        abort_unless(Auth::guard('admin')->user()->hasPermission($allowed[2]), 403);
        $model = $this->find($def, $id);
        $admin = Auth::guard('admin')->id();

        if ($model instanceof Mosque && $action === 'verify') {
            $model->update(['verification_status' => 'verified', 'verified_at' => now(), 'verified_by' => $admin]);
        } elseif ($model instanceof Mosque && $action === 'unverify') {
            $model->update(['verification_status' => 'unverified', 'verified_at' => null, 'verified_by' => null]);
        } elseif ($model instanceof Business && $action === 'halal_verify') {
            if (! filled($model->halal_certificate_number) && ! filled($model->halal_certifier)) {
                return back()->with('error', 'Record the certifier or certificate number before marking this business halal verified.');
            }
            $model->update(['halal_status' => 'verified', 'verified_at' => now(), 'verified_by' => $admin]);
        } elseif ($model instanceof Business && $action === 'halal_reject') {
            $model->update(['halal_status' => 'rejected', 'verified_at' => null, 'verified_by' => $admin]);
        } elseif ($model instanceof Business && $action === 'halal_clear') {
            $model->update(['halal_status' => 'unverified', 'verified_at' => null, 'verified_by' => null]);
        }
        AuditLogger::log('admin.'.$request->route('resource').'.'.$action, $model, $allowed[1]);

        return back()->with('status', $allowed[1].'.');
    }

    private function definition(Request $request): array
    {
        $def = Registry::find($request->route('resource'));
        abort_unless($def['module'] === $request->route('module'), 404);
        abort_unless(Auth::guard('admin')->user()->hasPermission($def['permission']), 403);

        return $def;
    }

    private function find(array $def, int $id): Model
    {
        return $def['model']::query()->findOrFail($id);
    }

    private function fields(array $def): array
    {
        return array_map(function (array $field) {
            $options = $field['options'] ?? [];
            if (isset($options['model'])) {
                $field['options'] = $options['model']::query()->orderBy($options['label'])->limit(500)->pluck($options['label'], 'id')->all();
            }

            return $field;
        }, $def['fields']);
    }

    private function values(Model $model, array $def): array
    {
        $out = [];
        foreach ($def['fields'] as $field) {
            $name = $field['name'];
            if (! empty($field['virtual'])) {
                $out[$name] = ($def['handler'] ?? null) === 'quiz-question' && $model->exists
                    ? $model->answers()->get()->map(fn ($a) => ($a->is_correct ? '* ' : '').$a->body)->implode("\n") : '';

                continue;
            }
            $value = $model->exists ? $model->getAttribute($name) : null;
            $out[$name] = match ($field['type']) {
                'money' => $model->exists ? number_format(((int) $value) / 100, 2, '.', '') : ($field['default'] ?? '0'),
                'datetime' => $value ? Carbon::parse($value)->timezone('Africa/Lagos')->format('Y-m-d\TH:i') : '',
                'date' => $value ? Carbon::parse($value)->toDateString() : '',
                'json' => $value === null || $value === '' ? '' : json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'lines' => is_array($value) ? implode("\n", $value) : '',
                'tags' => is_array($value) ? implode(', ', $value) : '',
                'checkbox' => (bool) ($value ?? ($field['default'] ?? false)),
                'secret', 'image' => '',
                default => $value ?? ($field['default'] ?? ''),
            };
        }

        return $out;
    }

    private function validated(Request $request, array $def, Model $model): array
    {
        $rules = [];
        $input = $request->all();
        foreach ($def['fields'] as $field) {
            if ($field['type'] === 'checkbox') {
                $input[$field['name']] = $request->boolean($field['name']);
            }
            if ($field['type'] === 'secret' && ! $request->filled($field['name'])) {
                continue;
            }
            if ($field['type'] === 'image' && ! $request->hasFile($field['name'])) {
                continue;
            }
            $rules[$field['name']] = $field['rules'];
        }
        $data = validator($input, $rules)->validate();

        $out = [];
        foreach ($def['fields'] as $field) {
            $name = $field['name'];
            if (! array_key_exists($name, $data) && $field['type'] !== 'checkbox') {
                continue;
            }
            $out[$name] = match ($field['type']) {
                'checkbox' => (bool) ($data[$name] ?? false),
                'money' => (int) round(((float) $data[$name]) * 100),
                'number' => $data[$name] === null || $data[$name] === '' ? null : $data[$name],
                'datetime' => filled($data[$name] ?? null) ? Carbon::parse($data[$name], 'Africa/Lagos')->utc() : null,
                'json' => filled($data[$name] ?? null) ? json_decode($data[$name], true) : null,
                'lines' => array_values(array_filter(array_map('trim', preg_split('/\r\n|\n/', (string) ($data[$name] ?? ''))))),
                'tags' => array_values(array_filter(array_map('trim', explode(',', (string) ($data[$name] ?? ''))))),
                'image' => $request->file($name)->store('admin/'.$request->route('resource'), 'public'),
                default => $data[$name] ?? null,
            };
        }
        if (($def['handler'] ?? null) === 'quiz-question') {
            $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\n/', (string) ($out['answers_text'] ?? '')))));
            $correct = collect($lines)->contains(fn ($l) => str_starts_with($l, '*'));
            if (count($lines) < 2 || ! $correct) {
                throw ValidationException::withMessages(['answers_text' => 'Give at least two answers, and mark the correct one with a leading asterisk.']);
            }
        }

        return $out;
    }

    private function slug(array $def, Model $model, array $data): void
    {
        if (empty($def['slug']) || filled($model->getAttribute($def['slug'][0]))) {
            return;
        }
        [$column, $from] = $def['slug'];
        $base = Str::slug((string) ($data[$from] ?? $model->getAttribute($from))) ?: 'item';
        $slug = $base;
        $i = 2;
        while ($model->newQuery()->where($column, $slug)->when($model->exists, fn ($q) => $q->whereKeyNot($model->getKey()))->exists()) {
            $slug = $base.'-'.$i++;
        }
        $model->setAttribute($column, $slug);
    }

    private function after(array $def, Model $model, array $data): void
    {
        if (! empty($data['is_default'])) {
            $query = $model->newQuery()->whereKeyNot($model->getKey());
            if ($model->getAttribute('ai_provider_id')) {
                $query->where('ai_provider_id', $model->ai_provider_id);
            }
            $query->update(['is_default' => false]);
        }
        if (Schema::hasColumn($model->getTable(), 'published_at') && $model->getAttribute('is_published') && ! $model->published_at) {
            $model->forceFill(['published_at' => now()])->save();
        }
        if (($def['handler'] ?? null) === 'quiz-question') {
            $model->answers()->delete();
            $sort = 0;
            foreach (preg_split('/\r\n|\n/', (string) $data['answers_text']) as $line) {
                $line = trim($line);
                if ($line === '') {
                    continue;
                }
                $correct = str_starts_with($line, '*');
                QuizAnswer::query()->create(['quiz_question_id' => $model->id, 'body' => trim($correct ? substr($line, 1) : $line), 'is_correct' => $correct, 'sort' => $sort++]);
            }
        }
    }

    private function virtual(array $def, string $key): bool
    {
        foreach ($def['fields'] as $field) {
            if ($field['name'] === $key) {
                return ! empty($field['virtual']);
            }
        }

        return false;
    }

    private function safe(array $data): array
    {
        unset($data['api_key']);

        return $data;
    }
}
