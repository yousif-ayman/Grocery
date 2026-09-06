<?php

namespace App\Http\Controllers\Api;

use App\Models\SpecialNote;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\SpecialNoteResource;

class SpecialNoteController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'message' => 'Special notes retrieved successfully',
            'data' => SpecialNoteResource::collection(SpecialNote::all())
        ]);
    }
}
