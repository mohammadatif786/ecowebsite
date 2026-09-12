<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LinkUpEventRequest;
use App\Models\EventCategory;
use App\Models\LinkUpEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MyEventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = LinkUpEvent::where('user_id', Auth::user()->id)->get();
        return Inertia::render('User/MyEvent/Index', [
            'events' => $events
        ]);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = EventCategory::all();
        return Inertia::render('User/MyEvent/CreateEvent', [
            'categories' => $categories
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function store(LinkUpEventRequest $request)
    {
        // dd($request->all());
        $data =  $request->validated();
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('images/events', 'public');
            $data['image_object'] = $path;
        }

        $event = LinkUpEvent::create($data);
        $event->featured_image = $path;
        $event->user_id = Auth::user()->id;
        $event->organizer_name = Auth::user()->name;

        $event->save();
        Logger($path);
        return back()->with(['message', 'Event created Successfully']);
    }

    public function show(LinkUpEvent $event)
    {
        return Inertia::render('User/MyEvent/Show', [
            'event' => $event,
        ]);
    }

    public function edit(LinkUpEvent $myevent)
    {

        $categories = EventCategory::all();
        return Inertia::render('User/MyEvent/EditEvent', [
            'categories' => $categories,
            'event' => $myevent
        ]);
    }


    public function update(LinkUpEvent $myevent, LinkUpEventRequest $request)
    {
        $data =  $request->validated();
        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('images/events', 'public');
            $data['image_object'] = $path;
        }

        $myevent->update($data);
        logger($myevent);
        return back()->with(['message', 'Event Updated Successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = LinkUpEvent::findOrfail($id);
        $data->delete();
        return redirect()->route('frontend.myevents.index');
    }
}
