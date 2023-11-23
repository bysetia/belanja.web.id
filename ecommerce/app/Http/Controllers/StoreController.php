<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\StoreRequest;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $query = Store::with('user'); // Include the 'user' relation
            return DataTables::of($query)
                ->addColumn('user_name', function ($item) {
                    return $item->user->name; // Access the user's name via the relation
                })
                ->addColumn('action', function ($item) {
                    return '
                            <form class="inline-block" action="' . route('dashboard.store.destroy', $item->id) . '" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this store?\');">
                        <button class="border border-red-500 bg-red-500 text-white rounded-md px-2 py-1 m-2 transition duration-500 ease select-none hover:bg-red-600 focus:outline-none focus:shadow-outline" >
                            Delete
                        </button>
                        ' . method_field('delete') . csrf_field() . '
                    </form>';
                })
                ->editColumn('created_at', function ($item) {
                    return Carbon::parse($item->created_at)->toDateTimeString();
                })
                ->addColumn('logo', function ($item) {
                    $imagePath = $item->logo ? asset($item->logo) : asset('images/logo.svg');
                    return '<div style="width: 50px; height: 50px; border-radius: 50%; overflow: hidden; display: flex; justify-content: center; align-items: center;">
                                <img src="' . $imagePath . '" width="50" height="50" style="object-fit: cover;">
                            </div>';
                })
                ->rawColumns(['action', 'logo'])
                ->make();
        }
        return view('pages.dashboard.store.index');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Store $store)
    {
        return view('pages.dashboard.store.edit', [
            'item' => $store
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRequest $request, Store $store)
    {
        $data = $request->all();

        $store->update($data);

        return redirect()->route('dashboard.store.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Store $store)
    {
        //
    }
}
