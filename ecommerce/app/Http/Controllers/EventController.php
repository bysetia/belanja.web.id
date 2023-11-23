<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Requests\EventRequest;
use App\Http\Controllers\Controller;


class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $query = Event::query();

            return DataTables::of($query)
                ->addColumn('action', function ($item) {
                    return '
                 <div class="text-center">
                        <a class="inline-block border border-corn-500 bg-corn-500 text-white rounded-md px-2 py-1 m-1 transition duration-500 ease select-none hover:bg-corn-400 focus:outline-none focus:shadow-outline" 
                            href="' . route('dashboard.event.edit', $item->id) . '"
                              style="background-color: #E7B10A; color: white;">
                            Edit
                        </a>
                        
                        <form class="inline-block" action="' . route('dashboard.event.destroy', $item->id) . '" method="POST" onsubmit="return confirm(\'Are you sure you want to remove this event?\');">
                        <button class="border border-red-500 bg-red-500 text-white rounded-md px-2 py-1 m-2 transition duration-500 ease select-none hover:bg-red-600 focus:outline-none focus:shadow-outline" >
                            Delete
                        </button>
                        ' . method_field('delete') . csrf_field() . '
                    </form>
                      <a class="inline-block border border-green-500 bg-green-500 text-white rounded-md px-2 py-1 m-1 transition duration-500 ease select-none hover:bg-green-600 focus:outline-none focus:shadow-outline" 
                                href="' . route('dashboard.event.registered-events', $item->id) . '">
                                User Registered
                            </a>
                            </div>
                    ';
                })
                ->addColumn('poster', function ($item) {
                    return '<img src="' . $item->poster . '" width="60">';
                })

                ->rawColumns(['action', 'poster'])
                ->make();
        }
        return view('pages.dashboard.event.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.dashboard.event.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventRequest $request, Event $event)
    {
        $data = $request->all();

        if ($request->hasFile('poster')) {
            $file = $request->file('poster');
            $path = $file->store('public/eventPosters');
            $path = str_replace('public/', '', $path);
            $posterUrl = config('app.url') . '/ecommerce/storage/app/public/' . $path;

            $data['poster'] = $posterUrl;
        }

        Event::create($data);

        return redirect()->route('dashboard.event.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        return view('pages.dashboard.event.edit', [
            'item' => $event
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventRequest $request, Event $event)
    {
        $data = $request->all();

        if ($request->hasFile('poster')) {
            $file = $request->file('poster');
            $path = $file->store('public/eventPosters');
            $path = str_replace('public/', '', $path);
            $posterUrl = config('app.url') . '/ecommerce/storage/app/public/' . $path;

            $data['poster'] = $posterUrl;
        }

        $event->update($data);

        return redirect()->route('dashboard.event.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()->route('dashboard.event.index');
    }

    public function registeredEvents(User $user)
    {
        $registeredEvents = $user->registeredEvents()->get();
        dd($registeredEvents);

        return view('pages.dashboard.event.registered-events', compact('user', 'registeredEvents'));
    }

    public function getRegisteredUsersView(Event $event, Request $request)
    {
        if ($request->ajax()) {
            $registeredUsers = $event->registeredUsers()->get();

            return DataTables::of($registeredUsers)
                ->addColumn('profile_photo_path', function ($user) {
                    return '<img src="' . $user->profile_photo_path . '" alt="Avatar" class="w-10 h-10">';
                })
                ->rawColumns(['profile_photo_path'])
                ->make();
        }

        return view('pages.dashboard.event.registered-events', compact('event'));
    }
}
