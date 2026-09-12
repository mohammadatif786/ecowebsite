<?php

namespace App\Http\Controllers\Admin\LiveStream;

use App\Http\Controllers\Controller;
use App\Models\LinkupLiveGift;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LinkupLiveGiftController extends Controller
{
    public function index(Request $request)
    {
        $search = (string) $request->get('search', '');
        $query = LinkupLiveGift::query();
        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%");
        }
        $gifts = $query->orderByDesc('id')->paginate(20)->appends(['search' => $search]);

        return Inertia::render('admin/liveStream/linkUpLiveGift/Index', [
            'gifts' => $gifts,
            'filters' => [ 'search' => $search ],
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/liveStream/linkUpLiveGift/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'emoji' => 'required',
            'coins' => 'required|integer|min:1',
            'active' => 'required',
        ]);
        LinkupLiveGift::create($data);
        return redirect()->route('admin.linkup-live-gifts.index')->with('message', 'Gift created.');
    }

    public function edit(LinkupLiveGift $linkup_live_gift)
    {
        return Inertia::render('admin/liveStream/linkUpLiveGift/Edit', [
            'gift' => $linkup_live_gift,
        ]);
    }

    public function update(Request $request, LinkupLiveGift $linkup_live_gift)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'emoji' => 'required',
            'coins' => 'required|integer|min:1',
            'active' => 'required',
        ]);
        $linkup_live_gift->update($data);
        return redirect()->route('admin.linkup-live-gifts.index')->with('message', 'Gift updated.');
    }

    public function destroy(LinkupLiveGift $linkup_live_gift)
    {
        $linkup_live_gift->delete();
        return redirect()->route('admin.linkup-live-gifts.index')->with('message', 'Gift deleted.');
    }
}


