<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdvertisementResource;
use App\Models\Advertisement;
use App\Models\News;
use App\Repositories\NewsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    // for getting all news 
    public function getAllNews()
    {
        $news = News::where('status', true)->get();
        if ($news) {
            return response()->json([
                'status' => true,
                'data' => $news
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No news found'
            ], 404);
        }
    }

    // for getting specific news content 
    public function getSpecificNews($id)
    {
        $news = News::where('status', true)->where('id', $id)->get();
        if ($news) {
            return response()->json([
                'status' => true,
                'data' => $news
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No news found'
            ], 404);
        }
    }

    // mirrors Frontend\NewsController::index
    public function newsIndex(Request $request, NewsRepository $repository)
    {
        $data = $repository->getAll($request);

        // same "general" ad query used for the swipe-ads feed elsewhere
        // (IndexControllerService::advertisementList / IndexController)
        $ads = Advertisement::where('status', 1)->where('category', 'general')->get();

        return response()->json([
            'status' => true,
            'data' => [
                'news' => $data['news'],
                'news_source' => $data['news_source']->map(fn ($source) => $source->only(['id', 'type', 'lang', 'source', 'country', 'category'])),
                'ads' => AdvertisementResource::collection($ads),
            ],
        ], 200);
    }

    // mirrors Frontend\NewsController::store
    public function storeNews(Request $request, NewsRepository $repository)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'summary' => 'required|string',
            'body' => 'required|string',
            'country' => 'required|string',
            'countryCode' => 'required|string',
            'region' => 'required|string',
            'category' => 'required|string',
            'sourceName' => 'nullable|string',
            'sourceType' => 'nullable|string',
            'isBreaking' => 'nullable|boolean',
            'breakingExpiresAt' => 'nullable',
            'trending' => 'nullable|integer',
            'news_media' => 'nullable|array',
            'media_info' => 'nullable|string',
        ]);

        $mediaData = [];
        $mainImage = null;
        if ($request->hasFile('news_media')) {
            $info = json_decode($request->media_info, true) ?? [];
            foreach ($request->file('news_media') as $index => $file) {
                $path = $file->store('news_media', 'public');
                $url = asset(Storage::url($path));

                $type = $info[$index]['type'] ?? 'image';
                $mediaData[] = [
                    'type' => $type,
                    'name' => $info[$index]['name'] ?? $file->getClientOriginalName(),
                    'dataUrl' => $url,
                    'size' => $file->getSize(),
                ];

                if (!$mainImage && $type === 'image') {
                    $mainImage = $url;
                }
            }
        }

        $news = News::create([
            'user_id' => Auth::id(),
            'user_type' => 'user',
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'content' => $validated['body'],
            'image_object' => $mainImage,
            'country' => $validated['country'],
            'country_code' => $validated['countryCode'],
            'region' => $validated['region'],
            'category' => $validated['category'],
            'source_name' => $validated['sourceName'] ?? 'LinkUp Desk',
            'source_type' => $validated['sourceType'] ?? 'internal',
            'is_breaking' => $validated['isBreaking'] ?? false,
            'breaking_expires_at' => $validated['breakingExpiresAt'] ?? null,
            'trending' => $validated['trending'] ?? 50,
            'media' => $mediaData,
            'published_at' => now(),
            'status' => true,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'News item created successfully.',
            'data' => $repository->format($news),
        ], 201);
    }

    // mirrors Frontend\NewsController::show
    public function showNews(News $news, NewsRepository $repository)
    {
        return response()->json([
            'status' => true,
            'data' => $repository->format($news),
        ], 200);
    }

    // mirrors Frontend\NewsController::update
    public function updateNews(Request $request, News $news, NewsRepository $repository)
    {
        if ($response = $this->denyIfNotOwner($news)) {
            return $response;
        }

        $validated = $request->validate([
            'title' => 'nullable|string',
            'summary' => 'nullable|string',
            'body' => 'nullable|string',
            'country' => 'nullable|string',
            'countryCode' => 'nullable|string',
            'region' => 'nullable|string',
            'category' => 'nullable|string',
            'sourceName' => 'nullable|string',
            'isBreaking' => 'nullable|boolean',
            'breakingExpiresAt' => 'nullable',
            'trending' => 'nullable|integer',
            'status' => 'nullable|boolean',
            'news_media' => 'nullable|array',
            'media_info' => 'nullable|string',
        ]);

        $repository->update($validated + $request->only(['news_media', 'media_info']), $news);

        return response()->json([
            'status' => true,
            'message' => 'News item updated successfully.',
            'data' => $repository->format($news->fresh()),
        ], 200);
    }

    // mirrors Frontend\NewsController::destroy
    public function destroyNews(News $news)
    {
        if ($response = $this->denyIfNotOwner($news)) {
            return $response;
        }

        $news->delete();

        return response()->json([
            'status' => true,
            'message' => 'News item deleted successfully.',
        ], 200);
    }

    // mirrors Frontend\NewsController::toggleActive
    public function toggleNewsStatus(News $news)
    {
        if ($response = $this->denyIfNotOwner($news)) {
            return $response;
        }

        $news->status = !$news->status;
        $news->save();

        return response()->json([
            'status' => true,
            'message' => 'News status updated successfully.',
            'data' => ['id' => $news->id, 'status' => (bool) $news->status],
        ], 200);
    }

    // mirrors Frontend\NewsController::incrementTrending
    public function incrementNewsTrending(News $news)
    {
        $news->increment('trending');

        return response()->json([
            'status' => true,
            'trending' => $news->trending,
        ], 200);
    }

    private function denyIfNotOwner(News $news)
    {
        if ($news->user_id !== Auth::id()) {
            return response()->json([
                'status' => false,
                'message' => 'You are not authorized to modify this news item.',
            ], 403);
        }

        return null;
    }
}
