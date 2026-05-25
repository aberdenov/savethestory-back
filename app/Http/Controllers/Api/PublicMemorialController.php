<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Memorial;

class PublicMemorialController extends Controller
{
    public function show(string $slug)
    {
        return response()->json(Memorial::where('slug', $slug)->where('status', 'published')
            ->with(['photos', 'timelines', 'quotes', 'lifeSections'])
            ->firstOrFail());
    }
}

