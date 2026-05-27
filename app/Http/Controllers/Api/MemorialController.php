<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Memorial;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MemorialController extends Controller
{
    public function index()
    {
        return Memorial::withCount(['photos', 'timelines', 'quotes', 'lifeSections'])
            ->latest()
            ->paginate(12);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['full_name']);

        unset(
            $data['facts'],
            $data['qualities'],
            $data['photos'],
            $data['timelines'],
            $data['quotes'],
            $data['life_sections']
        );

        $data['user_id'] = $request->user()->id;

        $memorial = Memorial::create($data);

        $this->syncNested($memorial, $request);

        return response()->json(
            $memorial->load(['photos', 'timelines', 'quotes', 'lifeSections']),
            201
        );
    }

    public function show(Memorial $memorial)
    {
        return response()->json(
            $memorial->load(['photos', 'timelines', 'quotes', 'lifeSections'])
        );
    }

    public function update(Request $request, Memorial $memorial)
    {
        $data = $this->validated($request, $memorial);

        if (isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        }

        unset(
            $data['facts'],
            $data['qualities'],
            $data['photos'],
            $data['timelines'],
            $data['quotes'],
            $data['life_sections']
        );

        $memorial->update($data);

        $this->syncNested($memorial, $request);

        return response()->json(
            $memorial->fresh(['photos', 'timelines', 'quotes', 'lifeSections'])
        );
    }

    public function destroy(Memorial $memorial)
    {
        $memorial->delete();

        return response()->json([
            'message' => 'Анкета удалена',
        ]);
    }

    public function publish(Memorial $memorial)
    {
        $memorial->update(['status' => 'published']);

        return response()->json($memorial);
    }

    public function unpublish(Memorial $memorial)
    {
        $memorial->update(['status' => 'draft']);

        return response()->json($memorial);
    }

    private function validated(Request $request, ?Memorial $memorial = null): array
    {
        return $request->validate([
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('memorials', 'slug')->ignore($memorial?->id),
            ],
            'full_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'death_date' => ['nullable', 'date'],
            'main_photo' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string'],
            'biography' => ['nullable', 'string'],
            'closing_text' => ['nullable', 'string'],
            'facts' => ['nullable', 'array'],
            'qualities' => ['nullable', 'array'],
            'photos' => ['nullable', 'array'],
            'timelines' => ['nullable', 'array'],
            'quotes' => ['nullable', 'array'],
            'life_sections' => ['nullable', 'array'],
            'life_sections.*.title' => ['nullable', 'string', 'max:255'],
            'life_sections.*.text' => ['nullable', 'string'],
            'life_sections.*.sort_order' => ['nullable', 'integer'],
        ]);
    }

    private function syncNested(Memorial $m, Request $r): void
    {
        if ($r->has('photos')) {
            $m->photos()->delete();

            foreach ($r->input('photos', []) as $i => $p) {
                if (!empty($p['image'])) {
                    $m->photos()->create([
                        'image' => $p['image'],
                        'caption' => $p['caption'] ?? null,
                        'sort_order' => $p['sort_order'] ?? $i,
                    ]);
                }
            }
        }

        if ($r->has('timelines')) {
            $m->timelines()->delete();

            foreach ($r->input('timelines', []) as $i => $t) {
                if (!empty($t['period']) && !empty($t['description'])) {
                    $m->timelines()->create([
                        'period' => $t['period'],
                        'description' => $t['description'],
                        'sort_order' => $t['sort_order'] ?? $i,
                    ]);
                }
            }
        }

        if ($r->has('quotes')) {
            $m->quotes()->delete();

            foreach ($r->input('quotes', []) as $i => $q) {
                if (!empty($q['author_name']) && !empty($q['text'])) {
                    $m->quotes()->create([
                        'author_name' => $q['author_name'],
                        'author_relation' => $q['author_relation'] ?? null,
                        'text' => $q['text'],
                        'sort_order' => $q['sort_order'] ?? $i,
                    ]);
                }
            }
        }

        if ($r->has('life_sections')) {
            $m->lifeSections()->delete();

            foreach ($r->input('life_sections', []) as $i => $section) {
                if (!empty($section['title']) && !empty($section['text'])) {
                    $m->lifeSections()->create([
                        'title' => $section['title'],
                        'text' => $section['text'],
                        'sort_order' => $section['sort_order'] ?? $i,
                    ]);
                }
            }
        }
    }

    private function uniqueSlug(string $v): string
    {
        $base = Str::slug($v) ?: Str::random(8);
        $slug = $base;
        $i = 2;

        while (Memorial::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}