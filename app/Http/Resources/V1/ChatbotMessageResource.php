<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatbotMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'session_id' => $this->session_id,
            'question' => $this->question,
            'answer' => $this->answer,
            'rating' => $this->rating,
            'created_at' => $this->created_at,
        ];
    }
}