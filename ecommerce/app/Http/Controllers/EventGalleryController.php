<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventGallery;

use Yajra\DataTables\DataTables;
use App\Http\Requests\EventGalleryRequest;

class EventGalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        if (request()->ajax()) {
            $query = EventGallery::where('events_id', $event->id);

            return DataTables::of($query)
                ->addColumn('action', function ($item) {
                    return '
                        <form class="inline-block" action="' . route('dashboard.gallery.destroy', $item->id) . '" method="POST">
                        <button class="border border-red-500 bg-red-500 text-white rounded-md px-2 py-1 m-2 transition duration-500 ease select-none hover:bg-red-600 focus:outline-none focus:shadow-outline" >
                            Hapus
                        </button>
                            ' . method_field('delete') . csrf_field() . '
                        </form>';
                })
                ->editColumn('url', function ($item) {
                    return '<img style="max-width: 150px;" src="' . $item->url . '"/>';
                })
                
                ->rawColumns(['action', 'url'])
                ->make();
    }
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.dashboard.event.gallery.create', compact('event'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventGalleryRequest $request, Event $event)
    {
        $files = $request->file('files');

        if ($request->hasFile('files')) {
            foreach ($files as $file) {
                $path = $file->store('public/gallery');

                EventGallery::create([
                    'events_id' => $event->id,
                    'url' => $path
                ]);
            }
        }

        return redirect()->route('dashboard.event.gallery.index', $event->id);
    }

    /**
     * Display the specified resource.
     */
    public function show(EventGallery $eventGallery)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EventGallery $eventGallery)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventGalleryRequest $request, EventGallery $eventGallery)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EventGallery $gallery)
    {
        $gallery->delete();

        return redirect()->route('dashboard.event.gallery.index', $gallery->events_id);
    }
}
