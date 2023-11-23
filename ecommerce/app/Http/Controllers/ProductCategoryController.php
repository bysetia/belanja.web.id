<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductCategory;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\ProductCategoryRequest;
use Carbon\Carbon;


class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (request()->ajax()) {
            $query = ProductCategory::query();

            return DataTables::of($query)
                ->addColumn('action', function ($item) {
                    return '
                        <a class="inline-block border border-corn-500 bg-corn-500 text-white rounded-md px-2 py-1 m-1 transition duration-500 ease select-none hover:bg-corn-400 focus:outline-none focus:shadow-outline" 
                            href="' . route('dashboard.category.edit', $item->id) . '"
                            style="background-color: #E7B10A; color: white;">
                            Edit
                        </a>
                        <form class="inline-block" action="' . route('dashboard.category.destroy', $item->id) . '" method="POST" onsubmit="return confirm(\'Are you sure you want to delete this category??\');">
                        <button class="border border-red-500 bg-red-500 text-white rounded-md px-2 py-1 m-2 transition duration-500 ease select-none hover:bg-red-600 focus:outline-none focus:shadow-outline" >
                            Delete
                        </button>
                        ' . method_field('delete') . csrf_field() . '
                    </form>';
                })
                ->editColumn('price', function ($item) {
                    return number_format($item->price);
                })
                ->addColumn('photo', function ($item) {
                    return '<img src="' . $item->photo . '" width="50" height="50">';
                })
                ->addColumn('banner', function ($item) {
                    return '<img src="' . $item->banner . '" width="100" height="100">';
                })
                ->editColumn('created_at', function ($item) {
                    return Carbon::parse($item->created_at)->toDateTimeString();
                })
                ->rawColumns(['action', 'photo', 'banner', 'created_at'])
                ->make();
        }

        return view('pages.dashboard.category.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.dashboard.category.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductCategoryRequest $request)
    {
        $data = $request->all();
        // Check if an image is uploaded

        if ($request->hasFile('photo')) {
            // Store the uploaded image in the 'public/photoCategory' disk
            $imagePath = $request->file('photo')->store('photoCategory', 'public');

            // Get the full URL of the stored image
            $imageUrl = config('app.url') . 'ecommerce/storage/app/public/' . $imagePath;


            // Add the image URL to the $data array
            $data['photo'] = $imageUrl;
        }
        if ($request->hasFile('banner')) {
            // Store the uploaded image in the 'public/photoCategory' disk
            $imagePath = $request->file('banner')->store('bannerCategory', 'public');

            // Get the full URL of the stored image
            $imageUrl = config('app.url') . 'ecommerce/storage/app/public/' . $imagePath;

            // Add the image URL to the $data array
            $data['banner'] = $imageUrl;
        }

        ProductCategory::create($data);

        return redirect()->route('dashboard.category.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductCategory $category)
    {
        return view('pages.dashboard.category.edit', [
            'item' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductCategoryRequest $request, ProductCategory $category)
    {
        $data = $request->all();
        if ($request->hasFile('photo')) {
            // Store the uploaded image in the 'public/photoCategory' disk
            $imagePath = $request->file('photo')->store('photoCategory', 'public');

            // Get the full URL of the stored image
            $imageUrl = config('app.url') . 'ecommerce/storage/app/public/' . $imagePath;


            // Add the image URL to the $data array
            $data['photo'] = $imageUrl;
        }
        if ($request->hasFile('banner')) {
            // Store the uploaded image in the 'public/photoCategory' disk
            $imagePath = $request->file('banner')->store('bannerCategory', 'public');

            // Get the full URL of the stored image
            $imageUrl = config('app.url') . 'ecommerce/storage/app/public/' . $imagePath;

            // Add the image URL to the $data array
            $data['banner'] = $imageUrl;
        }
        $category->update($data);

        return redirect()->route('dashboard.category.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $category)
    {
        $category->delete();

        return redirect()->route('dashboard.category.index');
    }
}
