<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

class Settings
{
    private const CACHE_KEY = 'site.settings';

    private ?array $values = null;

    /**
     * Flattened field definitions keyed by setting key.
     *
     * @return array<string, array<string, mixed>>
     */
    public function definitions(): array
    {
        return collect(config('site.groups'))
            ->flatMap(fn (array $group) => $group['fields'])
            ->all();
    }

    public function all(): array
    {
        if ($this->values !== null) {
            return $this->values;
        }

        $defaults = collect($this->definitions())->map(fn (array $field) => $field['default'] ?? null)->all();

        try {
            $stored = Cache::rememberForever(self::CACHE_KEY, fn () => Schema::hasTable('settings')
                ? Setting::query()->pluck('value', 'key')->all()
                : []);
        } catch (Throwable) {
            $stored = [];
        }

        return $this->values = array_replace($defaults, $stored);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /** A write-only setting, stored encrypted (type "secret" in config/site.php). */
    public function secret(string $key): ?string
    {
        $value = $this->get($key);

        if (blank($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return null;
        }
    }

    public function bool(string $key): bool
    {
        return filter_var($this->get($key), FILTER_VALIDATE_BOOLEAN);
    }

    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }

            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->flush();
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->values = null;
    }

    /**
     * Settings safe to expose to every visitor.
     */
    public function public(): array
    {
        $public = Arr::only($this->all(), collect($this->definitions())->filter(fn ($f) => $f['public'] ?? false)->keys()->all());

        foreach (['logo', 'favicon', 'og_image'] as $image) {
            $public[$image] = Media::url($public[$image] ?? null);
        }

        $public['is_available'] = $this->bool('is_available');
        $public['seasonal_effects'] = $this->bool('seasonal_effects');

        return $public;
    }

    /**
     * Discord IDs allowed into the admin panel (env + settings).
     *
     * @return list<string>
     */
    public function adminDiscordIds(): array
    {
        $fromSettings = preg_split('/[\s,]+/', (string) $this->get('admin_discord_ids'), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique([...config('site.admin_discord_ids', []), ...$fromSettings]));
    }
}
