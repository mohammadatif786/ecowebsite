<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\NewsSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class NewsController extends Controller
{
    public function index(Request $request)
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
            ->map(function ($item) {
                // Ensure internal data structure matches what Vue expects
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
            });

        $news_source = NewsSource::all();
        return Inertia::render('User/News/Index', [
            'news' => $news,
            'news_source' => $news_source,
            'filters' => $request->only('search', 'country', 'source_id'),
            'message' => session('message'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('User/News/CreateEdit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "title" => 'required|string',
            "summary" => 'required|string',
            "body" => 'required|string',
            "country" => 'required|string',
            "countryCode" => 'required|string',
            "region" => 'required|string',
            "category" => 'required|string',
            "sourceName" => 'nullable|string',
            "sourceType" => 'nullable|string',
            "isBreaking" => 'nullable|boolean',
            "breakingExpiresAt" => 'nullable',
            "trending" => 'nullable|integer',
            "news_media" => 'nullable|array',
            "media_info" => 'nullable|string',
        ]);

        $mediaData = [];
        $mainImage = null;
        if ($request->hasFile('news_media')) {
            $info = json_decode($request->media_info, true) ?? [];
            foreach ($request->file('news_media') as $index => $file) {
                // Store in public/news_media directory
                $path = $file->store('news_media', 'public');
                $url = asset(Storage::url($path));

                $type = $info[$index]['type'] ?? 'image';
                $mediaData[] = [
                    'type' => $type,
                    'name' => $info[$index]['name'] ?? $file->getClientOriginalName(),
                    'dataUrl' => $url,
                    'size' => $file->getSize()
                ];

                // Use the first image as the main image_object (thumbnail)
                if (!$mainImage && $type === 'image') {
                    $mainImage = $url;
                }
            }
        }

        News::create([
            'user_id' => auth()->id(),
            'user_type' => 'user',
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'content' => $validated['body'],
            'image_object' => $mainImage, // Set the main thumbnail
            'country' => $validated['country'],
            'country_code' => $validated['countryCode'],
            'region' => $validated['region'],
            'category' => $validated['category'],
            'source_name' => $validated['sourceName'] ?? 'LinkUp Desk',
            'source_type' => $validated['sourceType'] ?? 'internal',
            'is_breaking' => $validated['isBreaking'] ?? false,
            'breaking_expires_at' => $validated['breakingExpiresAt'],
            'trending' => $validated['trending'] ?? 50,
            'media' => $mediaData,
            'published_at' => now(),
            'status' => true,
        ]);

        return redirect()->route('frontend.news.index')
            ->with('message', 'News item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $news = News::findOrFail($id);
        return Inertia::render('User/News/NewsView', [
            'news' => $news,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $news = News::findOrFail($id);
        return Inertia::render('User/News/CreateEdit', [
            'news' => $news,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $news = News::findOrFail($id);

        $validated = $request->validate([
            "title" => 'nullable|string',
            "summary" => 'nullable|string',
            "body" => 'nullable|string',
            "country" => 'nullable|string',
            "countryCode" => 'nullable|string',
            "region" => 'nullable|string',
            "category" => 'nullable|string',
            "sourceName" => 'nullable|string',
            "isBreaking" => 'nullable|boolean',
            "breakingExpiresAt" => 'nullable',
            "trending" => 'nullable|integer',
            "status" => 'nullable|boolean',
        ]);

        $data = [];
        if (isset($validated['title'])) $data['title'] = $validated['title'];
        if (isset($validated['summary'])) $data['summary'] = $validated['summary'];
        if (isset($validated['body'])) $data['content'] = $validated['body'];
        if (isset($validated['country'])) $data['country'] = $validated['country'];
        if (isset($validated['countryCode'])) $data['country_code'] = $validated['countryCode'];
        if (isset($validated['region'])) $data['region'] = $validated['region'];
        if (isset($validated['category'])) $data['category'] = $validated['category'];
        if (isset($validated['sourceName'])) $data['source_name'] = $validated['sourceName'];
        if (isset($validated['isBreaking'])) $data['is_breaking'] = $validated['isBreaking'];
        if (isset($validated['breakingExpiresAt'])) $data['breaking_expires_at'] = $validated['breakingExpiresAt'];
        if (isset($validated['trending'])) $data['trending'] = $validated['trending'];
        if (isset($validated['status'])) $data['status'] = $validated['status'];

        $news->update($data);

        return redirect()->route('frontend.news.index')
            ->with('message', 'News item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $news = News::findOrFail($id);
        $news->delete();

        return redirect()->route('frontend.news.index')
            ->with('message', 'News item deleted successfully.');
    }

    public function toggleActive(News $news)
    {
        $news->status = !$news->status;
        $news->save();
        return back()->with('message', 'News status updated successfully.');
    }


    // for news source
    public function AddNewsSource(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'lang' => 'required',
            'source_url' => 'required|url',
            'country' => 'required',
            'category' => 'required',
        ]);

        NewsSource::create([
            'type' => $request->type,
            'lang' => $request->lang,
            'source' => $request->source_url,
            'country' => $request->country,
            'category' => $request->category,
        ]);

        return back()->with('message', 'News source added successfully.');
    }

    // for delete news source 
    public function DeleteNewsSource(NewsSource $source)
    {
        $source->delete();
        return back()->with('message', 'News source deleted successfully.');
    }

    public function incrementTrending(News $news)
    {
        $news->increment('trending');
        return response()->json([
            'success' => true,
            'trending' => $news->trending
        ]);
    }
}
