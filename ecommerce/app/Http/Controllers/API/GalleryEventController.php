<?php

namespace App\Http\Controllers\API;

use App\Models\Event;
use App\Models\EventGallery;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GalleryEventController extends Controller
{
    public function addGallery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'events_id' => 'required|exists:events,id',
            'image' => 'required|image',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        $event = Event::find($request->input('events_id'));

        if (!$event) {
            return ResponseFormatter::error(
                null,
                'Event data not found',
                404
            );
        }

        $file = $request->file('image');
        $path = $file->store('public/eventImages');


        $gallery = EventGallery::create([
            'events_id' => $event->id,
            'url' => $path,
        ]);
        $response = $gallery->toArray();
        $response = [
            'id' => $response['id'],
            'url' => $response['url'],
            'created_at' => $response['created_at'],
            'updated_at' => $response['updated_at'],
        ];

        return ResponseFormatter::success($response, 'Event photo added successfully');
    }

    public function getAllGallery($id = null)
    {
        if ($id) {
            $gallery = EventGallery::find($id);
    
            if (!$gallery) {
                return ResponseFormatter::error(
                    null,
                    'Gallery not found',
                    404
                );
            }
    
            return ResponseFormatter::success($gallery, 'Gallery found');
        }
    
        $galleries = EventGallery::paginate(6);
    
        if ($galleries->isEmpty()) {
            return ResponseFormatter::error(
                null,
                'No galleries found',
                404
            );
        }
    
        $response = $galleries->toArray();
    
        return ResponseFormatter::success($response, 'All photos of the event were successfully taken');
    }


    public function deleteGallery($id)
    {
        $gallery = EventGallery::find($id);

        if (!$gallery) {
            return ResponseFormatter::error(null, 'Photos not found', 404);
        }

        $gallery->delete();

        return ResponseFormatter::success(null, 'Event photo deleted successfully');
    }
    
    public function editGallery(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'events_id' => 'exists:events,id',
            'image' => 'image',
        ]);
    
        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }
    
        $gallery = EventGallery::find($id);
    
        if (!$gallery) {
            return ResponseFormatter::error(
                null,
                'Gallery not found',
                404
            );
        }
    
        if ($request->has('events_id')) {
            $event = Event::find($request->input('events_id'));
            if (!$event) {
                return ResponseFormatter::error(
                    null,
                    'Event data not found',
                    404
                );
            }
            $gallery->events_id = $event->id;
        }
    
        if ($request->hasFile('image')) {
            // Delete old image
            Storage::delete($gallery->url);
    
            // Store new image
            $file = $request->file('image');
            $path = $file->store('public/eventImages');
            $gallery->url = $path;
        }
    
        $gallery->save();
    
        $response = $gallery->toArray();
        $response = [
            'id' => $response['id'],
            'url' => $response['url'],
            'created_at' => $response['created_at'],
            'updated_at' => $response['updated_at'],
        ];
    
        return ResponseFormatter::success($response, 'Event photo edited successfully');
    }

}
