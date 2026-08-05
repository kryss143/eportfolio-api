<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{

    public function toArray(Request $request) : array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'technologies' => $this->technologies,
            'status' => $this->status,
            'githubLink' => $this->githubLink,
            'demoLink' => $this->demoLink,
            'image' => $this->image,
            'outcome' => $this->outcome,
            'metrics' => $this->metrics,
            'featured' => $this->featured,
        ];
    }

}

?>