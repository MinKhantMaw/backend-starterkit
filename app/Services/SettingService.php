<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingService
{
    public function all(?string $group = null)
    {
        return Setting::query()->when($group, fn ($query) => $query->where('group', $group))->orderBy('key')->get();
    }

    public function upsert(array $settings): mixed
    {
        $result = DB::transaction(function () use ($settings) {
            foreach ($settings as $setting) {
                if (($setting['type'] ?? 'string') === 'json' && is_array($setting['value'] ?? null)) {
                    $setting['value'] = json_encode($setting['value'], JSON_THROW_ON_ERROR);
                }
                Setting::query()->updateOrCreate(['key' => $setting['key']], $setting + ['updated_by' => auth()->id()]);
            }

            return $this->all();
        });
        Cache::forget('cms.public-settings');

        return $result;
    }

    public function publicSettings()
    {
        return Cache::remember('cms.public-settings', 3600, fn () => Setting::query()->where('is_public', true)
            ->get()->mapWithKeys(fn (Setting $setting) => [$setting->key => $this->cast($setting)]));
    }

    private function cast(Setting $setting): mixed
    {
        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOL),
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value ?? 'null', true),
            default => $setting->value,
        };
    }
}
