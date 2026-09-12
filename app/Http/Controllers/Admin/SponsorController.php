<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EventSponsorRequest;
use App\Models\LinkUpEvent;
use App\Models\Sponsor;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SponsorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $events = LinkUpEvent::select('id as value', 'title as label')->get();
        $paginator = Sponsor::query()
            ->filter($request->only('search'))
            ->orderBy('created_at', 'DESC')
            ->with('event')
            ->paginate(10);
        $appURL = config('app.url');
        return Inertia::render('admin/sponsors/Index', [
            'paginator' => $paginator,
            'events'=>$events,
            'filters' => $request->only('search'),
            'appURL' => $appURL,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventSponsorRequest $request)
    {
        $data = $request->all();
        $sponsor = Sponsor::create($data);
        $this->saveImage($request, $sponsor);
        return redirect()->back()->withSuccess('Sponsor Created Successfully');
    }


    /**
     * Update the specified resource in storage.
     */ //EventSponsorRequest
    public function update(EventSponsorRequest $request, Sponsor $sponsor)
    {
        $data = $request->all();
        $sponsor->update($data);
        $this->saveImage($request, $sponsor);
        return redirect()->back()->withSuccess('Sponsor Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sponsor $sponsor)
    {
        $sponsor->delete();
        return redirect()->back()->withSuccess('Sponsor Updated Successfully');
    }

    private function saveImage(Request $request, Sponsor $sponsor)
    {
        if ($request->hasFile('sponsor_image_file')) {
            $path = $request->file('sponsor_image_file')->store('images/eventSponsors', 'public');
            $sponsor->image_object = $path;
            $sponsor->save();
        }
    }
}
