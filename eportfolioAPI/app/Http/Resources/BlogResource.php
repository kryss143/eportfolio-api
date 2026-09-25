<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            // Bug #16: normalize dates to a single ISO-8601 string whether the
            // model came from MongoDB (Carbon) or the mock fallback (string).
            'date' => $this->date ? (string) $this->date : null,
            'readTime' => $this->readTime,
            'slug' => $this->slug,
            'content' => $this->content,
        ];
    }
}
