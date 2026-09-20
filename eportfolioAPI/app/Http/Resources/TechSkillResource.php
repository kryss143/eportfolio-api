<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TechSkillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category?->value ?? $this->category,
            'logo' => $this->logo,
            'label' => $this->label,
        ];
    }
}
