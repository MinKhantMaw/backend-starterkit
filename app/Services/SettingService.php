<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::where('key', $key)->first();

        return $setting?->value ?? $default;
    }

    public function getBoolean(string $key, bool $default = false): bool
    {
        $value = $this->get($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function set(string $key, mixed $value, ?string $type = null): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $this->stringValue($value),
                'type' => $type,
            ],
        );
    }

    private function stringValue(mixed $value): ?string
    {
        return match (true) {
            $value === null => null,
            is_bool($value) => $value ? 'true' : 'false',
            is_scalar($value) => (string) $value,
            default => json_encode($value),
        };
    }
}
