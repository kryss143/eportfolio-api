<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SkillResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->_id,
            'proficient' => $this->proficient,
            'familiar' => $this->familiar,
            'authentication' => $this->authentication,
            'architecture' => $this->architecture,
            'toolsPlatforms' => $this->toolsPlatforms,
            'practices' => $this->practices,
            'ai' => $this->ai,
        ];
    }
    
}

?>