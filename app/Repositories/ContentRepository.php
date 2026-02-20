<?php
namespace App\Repositories;

use App\Models\Content;
use Illuminate\Support\Facades\DB;
class ContentRepository
{
    public function getByLocale(string $locale, ?string $tag = null, ?string $group = null)
    {
        return DB::table('contents')
            ->where('locale', $locale)
            ->when($tag, fn($q) => $q->whereJsonContains('tags', $tag))
            ->when($group, fn($q) => $q->where('key', 'LIKE', "{$group}%"))
            ->pluck('value', 'key');
    }

    public function upsert(array $data)
    {
        return Content::updateOrCreate(
            ['key' => $data['key'], 'locale' => $data['locale']],
            ['value' => $data['value'], 'tags' => $data['tags'] ?? []]
        );
    }

    public function globalSearch(array $filters)
    {
        $query = Content::query();

        if (!empty($filters['locale'])) {
            $query->where('locale', $filters['locale']);
        }

        $query->where(function ($mainGroup) use ($filters) {

            if (!empty($filters['q'])) {
                $term = $filters['q'];
                $mainGroup->where(function ($q) use ($term) {
                    $q->where('key', 'LIKE', "%{$term}%")
                        ->orWhere('value', 'LIKE', "%{$term}%");
                });
            }

            if (!empty($filters['tag'])) {
                $tag = $filters['tag'];
                $mainGroup->where(function ($q) use ($tag) {
                    $q->whereJsonContains('tags', $tag)
                        ->orWhere('tags', 'LIKE', "%\"{$tag}\"%");
                });
            }
        });

        return $query->latest()->limit(50)->get();
    }
    public function findById(int $id)
    {
        return Content::findOrFail($id);
    }
}