<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Memorial;
use Illuminate\Http\Request;

class PublicMemorialController extends Controller
{
    public function show(string $slug)
    {
        return response()->json(
            Memorial::where('slug', $slug)
                ->where('status', 'published')
                ->with([
                    'photos',
                    'timelines',
                    'quotes' => fn ($query) => $query
                        ->where('status', 'approved')
                        ->orderBy('sort_order'),
                    'lifeSections',
                ])
                ->firstOrFail()
        );
    }

    public function storePublicQuote(Request $request, Memorial $memorial)
    {
        $data = $request->validate([
            'author_name' => ['required', 'string', 'max:255'],
            'author_relation' => ['nullable', 'string', 'max:255'],
            'text' => ['required', 'string', 'max:2000'],
        ]);

        $quote = $memorial->quotes()->create([
            'author_name' => $data['author_name'],
            'author_relation' => $data['author_relation'] ?? null,
            'text' => $data['text'],
            'status' => 'pending',
            'sort_order' => $memorial->quotes()->count(),
        ]);

        return response()->json([
            'message' => 'Воспоминание отправлено на модерацию',
            'quote' => $quote,
        ], 201);
    }
}

