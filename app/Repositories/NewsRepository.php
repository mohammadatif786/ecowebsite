<?php

namespace App\Repositories;

use App\Models\News;
use Illuminate\Support\Facades\Storage;
use App\Models\NewsSource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class NewsRepository
{
    public function getAll($request)
    {
        $news = News::query()
            ->with('source')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->country, function ($query, $country) {
                $query->where('country', $country);
            })
            ->when($request->source_id, function ($query, $sourceId) {
                $query->where('source_id', $sourceId);
            })
            ->latest()
            ->get()
            ->map(fn ($item) => $this->format($item));

        $news_source = NewsSource::all();
        return [
            'news' => $news,
            'news_source' => $news_source,
            'filters' => $request->only('search', 'country', 'source_id'),
        ];
    }

    public function format(News $item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'summary' => $item->summary,
            'body' => $item->content,
            'image_object' => $item->image_object,
            'country' => $item->country,
            'countryCode' => $item->country_code,
            'region' => $item->region,
            'category' => $item->category,
            'sourceType' => $item->source_type,
            'sourceName' => $item->source_name,
            'publishedAt' => $item->published_at ? $item->published_at->toISOString() : $item->created_at->toISOString(),
            'isBreaking' => (bool)$item->is_breaking,
            'breakingExpiresAt' => $item->breaking_expires_at ? $item->breaking_expires_at->toISOString() : null,
            'trending' => $item->trending,
            'media' => $item->media ?? [],
            'status' => $item->status,
        ];
    }
    public function store(array $data)
    {
        $mediaData = [];
        $mainImage = null;
        if (isset($data['news_media']) && is_array($data['news_media'])) {
            $info = isset($data['media_info']) ? (json_decode($data['media_info'], true) ?? []) : [];
            foreach ($data['news_media'] as $index => $file) {
                if (!$file instanceof \Illuminate\Http\UploadedFile) {
                    continue;
                }

                $path = $file->store('news_media', 'public');
                $url = asset(Storage::url($path));

                $type = $info[$index]['type'] ?? 'image';
                $mediaData[] = [
                    'type' => $type,
                    'name' => $info[$index]['name'] ?? $file->getClientOriginalName(),
                    'dataUrl' => $url,
                    'size' => $file->getSize()
                ];

                if (!$mainImage && $type === 'image') {
                    $mainImage = $url;
                }
            }
        }

        News::create([
            'user_id' => Auth::id(),
            'user_type' => Auth::user()->roles->first()->name,
            'title' => $data['title'],
            'summary' => $data['summary'],
            'content' => $data['body'],
            'image_object' => $mainImage,
            'country' => $data['country'],
            'country_code' => $data['countryCode'],
            'region' => $data['region'],
            'category' => $data['category'],
            'source_name' => $data['sourceName'] ?? 'LinkUp Desk',
            'source_type' => $data['sourceType'] ?? 'internal',
            'is_breaking' => $data['isBreaking'] ?? false,
            'breaking_expires_at' => $data['breakingExpiresAt'],
            'trending' => $data['trending'] ?? 50,
            'media' => $mediaData,
            'published_at' => now(),
            'status' => true,
        ]);
    }

    public function show($id)
    {
        $news = News::findOrFail($id);
        return $news;
    }

    public function update(array $data, $news)
    {
        $attributes = [];
        if (array_key_exists('title', $data)) $attributes['title'] = $data['title'];
        if (array_key_exists('summary', $data)) $attributes['summary'] = $data['summary'];
        if (array_key_exists('body', $data)) $attributes['content'] = $data['body'];
        if (array_key_exists('country', $data)) $attributes['country'] = $data['country'];
        if (array_key_exists('countryCode', $data)) $attributes['country_code'] = $data['countryCode'];
        if (array_key_exists('region', $data)) $attributes['region'] = $data['region'];
        if (array_key_exists('category', $data)) $attributes['category'] = $data['category'];
        if (array_key_exists('sourceName', $data)) $attributes['source_name'] = $data['sourceName'];

        if (array_key_exists('isBreaking', $data)) {
            $attributes['is_breaking'] = filter_var($data['isBreaking'], FILTER_VALIDATE_BOOLEAN);
        }

        if (array_key_exists('breakingExpiresAt', $data)) $attributes['breaking_expires_at'] = $data['breakingExpiresAt'] ?: null;
        if (array_key_exists('trending', $data)) $attributes['trending'] = $data['trending'];
        if (array_key_exists('status', $data)) $attributes['status'] = filter_var($data['status'], FILTER_VALIDATE_BOOLEAN);

        if (array_key_exists('media_info', $data)) {
            $info = json_decode($data['media_info'], true) ?? [];
            $mediaData = [];
            $newFiles = $data['news_media'] ?? [];
            $fileIndex = 0;
            $mainImage = null;

            foreach ($info as $item) {
                if (!empty($item['isExisting']) && $item['isExisting'] === true) {
                    $mediaData[] = [
                        'type' => $item['type'],
                        'name' => $item['name'],
                        'dataUrl' => $item['dataUrl'],
                        'size' => $item['size'] ?? 0
                    ];
                    if (!$mainImage && $item['type'] === 'image') $mainImage = $item['dataUrl'];
                } else {
                    if (isset($newFiles[$fileIndex])) {
                        $file = $newFiles[$fileIndex];
                        $path = $file->store('news_media', 'public');
                        $url = asset(Storage::url($path));

                        $mediaData[] = [
                            'type' => $item['type'] ?? 'image',
                            'name' => $item['name'] ?? $file->getClientOriginalName(),
                            'dataUrl' => $url,
                            'size' => $file->getSize()
                        ];
                        if (!$mainImage && ($item['type'] ?? 'image') === 'image') $mainImage = $url;
                        $fileIndex++;
                    }
                }
            }

            $attributes['media'] = $mediaData;
            $attributes['image_object'] = $mainImage;
        }

        $news->update($attributes);
    }

    public function delete($id)
    {
        $news = News::findOrFail($id);
        $news->delete();

        return $news;
    }
}
