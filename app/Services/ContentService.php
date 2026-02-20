<?php

namespace App\Services;

use App\Repositories\ContentRepository;
use Illuminate\Support\Facades\Cache;

class ContentService
{
    public function __construct(protected ContentRepository $repository)
    {
    }

    public function getContentForDelivery(string $locale, ?string $tag = null, ?string $group = null): array
    {
        $version = Cache::get("content_version_{$locale}", 1);

        $cacheKey = "v{$version}:content:{$locale}:group:" . ($group ?? 'all') . ":tag:" . ($tag ?? 'all');

        return Cache::remember($cacheKey, 3600, function () use ($locale, $tag, $group) {
            return $this->repository->getByLocale($locale, $tag, $group)->toArray();
        });
    }

    public function storeContent(array $data)
    {
        $content = $this->repository->upsert($data);
        $this->clearContentCache($data['locale']);

        return $content;
    }

    public function searchContent(array $filters): \Illuminate\Support\Collection
    {
        return $this->repository->globalSearch($filters);
    }
    public function getContentDetail(int $id)
    {
        return $this->repository->findById($id);
    }

    protected function clearContentCache(string $locale): void
    {
        Cache::forever("content_version_{$locale}", Cache::get("content_version_{$locale}", 1) + 1);
    }
}