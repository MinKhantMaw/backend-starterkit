<?php

namespace App\Modules\SecuritySetting\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SecuritySettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'admin_2fa_enabled' => (bool) $this->resource['admin_2fa_enabled'],
        ];
    }
}
