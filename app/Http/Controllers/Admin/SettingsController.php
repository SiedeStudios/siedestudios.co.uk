<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Media;
use App\Support\Settings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function edit(Settings $settings, ?string $group = null): Response
    {
        $groups = config('site.groups');
        $group ??= array_key_first($groups);
        abort_unless(isset($groups[$group]), 404);

        $values = $settings->all();
        $fields = collect($groups[$group]['fields'])->map(function (array $field, string $key) use ($values) {
            $value = $values[$key] ?? null;

            return [
                'key' => $key,
                'type' => $field['type'],
                'label' => $field['label'],
                'help' => $field['help'] ?? null,
                'options' => isset($field['options']) ? config('site.'.$field['options']) : null,
                'value' => match ($field['type']) {
                    'toggle' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                    'image' => null,
                    default => $value,
                },
                'preview' => $field['type'] === 'image' ? Media::url($value) : null,
            ];
        })->values();

        return Inertia::render('Admin/Settings', [
            'groups' => collect($groups)->map(fn ($g, $key) => ['key' => $key, 'label' => $g['label']])->values(),
            'group' => ['key' => $group, 'label' => $groups[$group]['label'], 'description' => $groups[$group]['description'] ?? null],
            'fields' => $fields,
            'context' => $group === 'payments' ? ['ipn_url' => route('paypal.ipn')] : ($group === 'access' ? [
                'env_ids' => config('site.admin_discord_ids'),
                'me' => auth()->user()->discord_id,
            ] : null),
        ]);
    }

    public function update(Request $request, Settings $settings, string $group): RedirectResponse
    {
        $definition = config("site.groups.{$group}");
        abort_unless($definition, 404);

        $fields = $definition['fields'];
        $rules = collect($fields)->mapWithKeys(fn ($f, $key) => [$key => $f['rules'] ?? ['nullable']])->all();

        foreach ($fields as $key => $field) {
            if ($field['type'] === 'image') {
                $rules["remove_{$key}"] = ['boolean'];
            }
            if (($field['options'] ?? null) === 'currencies') {
                $rules[$key][] = 'in:'.implode(',', config('site.currencies'));
            }
        }

        $data = $request->validate($rules);
        $current = $settings->all();
        $save = [];

        foreach ($fields as $key => $field) {
            if ($field['type'] === 'image') {
                if ($request->boolean("remove_{$key}") || $request->hasFile($key)) {
                    Media::delete($current[$key] ?? null);
                    $save[$key] = null;
                }
                if ($request->hasFile($key)) {
                    $save[$key] = $request->file($key)->store('branding', 'public');
                }

                continue;
            }

            $save[$key] = $field['type'] === 'toggle' ? ($request->boolean($key) ? '1' : '0') : ($data[$key] ?? null);
        }

        $settings->set($save);

        return back()->with('success', $definition['label'].' settings saved.');
    }
}
