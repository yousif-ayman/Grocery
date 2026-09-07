<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
        ];

        if ($this->relationLoaded('meals')) {
            $data['meals'] = MealResource::collection($this->meals);

            $data['updated_at'] = $this->updated_at;
        }

        if (isset($this->meals_count)) {
            $data['meals_count'] = $this->meals_count;
        }

        return $data;
    }
}