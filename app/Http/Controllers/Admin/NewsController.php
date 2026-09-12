<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsFormRequest;
use App\Models\News;
use App\Models\NewsSource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Repositories\NewsRepository;

class NewsController extends Controller
{
    protected $newsRepository;
    public function __construct(NewsRepository $newsRepository)
    {
        $this->newsRepository = $newsRepository;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', News::class);

        $news = $this->newsRepository->getAll($request);

        return Inertia::render('admin/news/Index', [
            'news' => $news['news'],
            'news_source' => $news['news_source'],
            'filters' => $news['filters'],
            'message' => session('message'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', News::class);
        return Inertia::render('admin/news/CreateEdit');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(NewsFormRequest $request)
    {
        $this->authorize('create', News::class);

        $validated = $request->validated();
        $this->newsRepository->store($validated);

        if ($request->boolean('stay_on_page')) {
            return back()->with('message', 'News item created successfully.');
        }

        return redirect()->route('admin.news.index')
            ->with('message', 'News item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $news = $this->newsRepository->show($id);
        $this->authorize('view', $news);
        return Inertia::render('admin/news/NewsView', [
            'news' => $news,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $news = $this->newsRepository->show($id);
        $this->authorize('edit', $news);
        return Inertia::render('admin/news/CreateEdit', [
            'news' => $news,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(NewsFormRequest $request, string $id)
    {
        $news = $this->newsRepository->show($id);

        $this->authorize('update', $news);

        $validated = $request->validated();
        $this->newsRepository->update($validated, $news);

        if ($request->boolean('stay_on_page')) {
            return back()->with('message', 'News item updated successfully.');
        }

        return redirect()->route('admin.news.index')
            ->with('message', 'News item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $news = $this->newsRepository->show($id);

        $this->authorize('delete', $news);

        $this->newsRepository->delete($id);

        return redirect()->route('admin.news.index')
            ->with('message', 'News item deleted successfully.');
    }

    public function toggleActive(News $news)
    {
        $this->authorize('toggleActive', $news);

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
