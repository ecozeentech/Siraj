<x-layouts.admin :title="($model->exists ? 'Edit ' : 'New ').$def['singular']">
    <a href="{{ route('admin.'.$def['module'].'.'.$resource.'.index') }}" class="text-sm font-semibold text-forest-700">Back to {{ $def['label'] }}</a>
    <form method="POST" action="{{ $model->exists ? route('admin.'.$def['module'].'.'.$resource.'.update', $model->id) : route('admin.'.$def['module'].'.'.$resource.'.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-4 rounded-2xl border border-line bg-white p-5">
        @csrf
        @if ($model->exists)@method('PUT')@endif
        @foreach ($fields as $field)
            @php($name = $field['name'])
            @switch($field['type'])
                @case('textarea')
                @case('json')
                @case('lines')
                    <x-ui.textarea :name="$name" :label="$field['label']" :value="$values[$name]" :rows="$field['rows'] ?? ($field['type'] === 'textarea' ? 4 : 6)" :hint="$field['hint'] ?? null" dir="{{ !empty($field['rtl']) ? 'rtl' : 'ltr' }}" />
                    @break
                @case('select')
                    <x-ui.select :name="$name" :label="$field['label']" :options="$field['options']" :value="$values[$name]" :placeholder="!empty($field['blank']) ? 'None' : null" :hint="$field['hint'] ?? null" />
                    @break
                @case('checkbox')
                    <label class="flex items-start gap-3"><input type="checkbox" name="{{ $name }}" value="1" @checked(filter_var(old($name, $values[$name]), FILTER_VALIDATE_BOOLEAN)) class="mt-0.5 size-5 rounded-md border-line text-forest-700"><span class="text-sm">{{ $field['label'] }}@if (!empty($field['hint']))<span class="block text-xs text-muted">{{ $field['hint'] }}</span>@endif</span></label>
                    @break
                @case('image')
                    <div><label class="field-label">{{ $field['label'] }}</label><input type="file" name="{{ $name }}" accept="image/jpeg,image/png,image/webp" class="block text-sm">@if ($model->getAttribute($name))<p class="field-hint">A file is already saved. Choose a new one to replace it.</p>@endif</div>
                    @break
                @case('secret')
                    <x-ui.input :name="$name" type="password" :label="$field['label']" :hint="$field['hint'] ?? null" autocomplete="off" />
                    @break
                @default
                    <x-ui.input :name="$name" :type="$field['type'] === 'datetime' ? 'datetime-local' : ($field['type'] === 'number' || $field['type'] === 'money' ? 'number' : ($field['type'] === 'email' ? 'email' : ($field['type'] === 'url' ? 'url' : ($field['type'] === 'date' ? 'date' : 'text'))))" :label="$field['label']" :value="$values[$name]" :hint="$field['hint'] ?? null" :step="$field['step'] ?? ($field['type'] === 'money' ? '0.01' : null)" dir="{{ !empty($field['rtl']) ? 'rtl' : 'ltr' }}" />
            @endswitch
        @endforeach
        <x-ui.button type="submit">Save</x-ui.button>
    </form>
    @if ($model->exists && ($def['delete'] ?? true) !== false)
        <x-ui.confirm :action="route('admin.'.$def['module'].'.'.$resource.'.destroy', $model->id)" method="DELETE" :message="'Delete this '.$def['singular'].'?'" label="Delete" />
    @endif
</x-layouts.admin>
