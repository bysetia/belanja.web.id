<?php

namespace App\Http\Controllers\API;

use App\Models\Event;
use App\Models\UserEvent;
use App\Mail\InvoiceEmail;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        // $limit = $request->input('limit', 6);    // ? batas pagination
        $name = $request->input('name');
        $description = $request->input('description');
        $title = $request->input('title');
        // $image = $request->input('image');
        $date = $request->input('date');
        $time = $request->input('time');
        $location = $request->input('location');

        if ($id) {
            $event = Event::query()->find($id);

            if ($event)
                return ResponseFormatter::success(
                    $event,
                    'Event data retrieved successfully'
                );
            else
                return ResponseFormatter::error(
                    null,
                    'Event data does not exist',
                    404
                );
        }

        $event = Event::query();
        if ($name) {
            $event->where('name', 'like', '%' . $name . '%');
        };

        if ($title) {
            $event->where('title', 'like', '%' . $title     . '%');
        };
        if ($description) {
            $event->where('description', 'like', '%' . $description . '%');
        };
        if ($date) {
            $event->where('date', 'like', '%' . $date . '%');
        };
        if ($time) {
            $event->where('time', 'like', '%' . $time . '%');
        };
        if ($location) {
            $event->where('location', 'like', '%' . $location . '%');
        };

        // return ResponseFormatter::success(
        //     $event->paginate($limit),
        //     'Data list Event berhasil diambil'


        // );

        $events = $event->get(); //paginator limit($limit)->

        return ResponseFormatter::success(
            $events,
            'Event list data successfully fetched'
        );
    }

    public function create_event(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'title' => 'required|string',
            'description' => 'required|string',
            'date' => 'required|date',
            'time' => 'required|string',
            'location' => 'required|string',
            'poster' => 'required|image',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        if ($request->hasFile('poster')) {
            $file = $request->file('poster');
            $path = $file->store('public/eventPosters');
            $path = str_replace('public/', '', $path);
            $posterUrl = config('app.url') . '/ecommerce/storage/app/public/' . $path;
        }

        $event = Event::create([
            'name' => $request->input('name'),
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'date' => $request->input('date'),
            'time' => $request->input('time'),
            'location' => $request->input('location'),
            'poster' => isset($posterUrl) ? $posterUrl : null,
        ]);

        $event = $event->refresh();

        return ResponseFormatter::success($event, 'Event created successfully');
    }


    public function update_event(Request $request, $id)
    {
        $event = Event::find($id);

        if (!$event) {
            return ResponseFormatter::error(null, 'Event data not found', 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'title' => 'string',
            'description' => 'string',
            'date' => 'date',
            'time' => 'string',
            'location' => 'string',
            'poster' => 'image',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error($validator->errors(), 'Validation Error', 422);
        }

        if ($request->has('name')) {
            $event->name = $request->name;
        }

        if ($request->has('title')) {
            $event->title = $request->title;
        }

        if ($request->has('description')) {
            $event->description = $request->description;
        }

        if ($request->has('date')) {
            $event->date = $request->date;
        }

        if ($request->has('time')) {
            $event->time = $request->time;
        }

        if ($request->has('location')) {
            $event->location = $request->location;
        }

        if ($request->hasFile('poster')) {
            $file = $request->file('poster');
            $path = $file->store('public/eventPosters');
            $path = str_replace('public/', '', $path);
            $posterUrl = config('app.url') . '/ecommerce/storage/app/public/' . $path;
            $event->poster = $posterUrl;
        }

        if ($event->save()) {
            $event = $event->refresh();
            return ResponseFormatter::success($event, 'Event updated successfully');
        } else {
            return ResponseFormatter::error(null, 'Event update failed', 500);
        }
    }


    public function delete_event($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return ResponseFormatter::error(
                null,
                'Event data not found',
                404
            );
        }

        $event->delete();

        return ResponseFormatter::success(null, 'Event deleted successfully');
    }

    public function registerAndSendInvoice(Request $request, $eventId)
    {

        $event = Event::find($eventId);

        if (!$event) {
            return ResponseFormatter::error(null, 'Event not found', 404);
        }

        $user = $request->user();

        // Periksa apakah pengguna sudah terdaftar untuk event ini
        if ($user->registeredEvents()->where('events.id', $eventId)->exists()) {
            return ResponseFormatter::error(null, 'User is already registered for this event', 400);
        }

        // Mendaftarkan pengguna pada event (menggunakan relasi many-to-many)
        $user->registeredEvents()->attach($event);

        // Send invoice email
        try {
            Mail::to($user->email)->send(new InvoiceEmail($event, $user));
            return ResponseFormatter::success(null, 'Event registered, invoice sent successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to send invoice', 500);
        }
    }


    public function checkRegistrationStatus(Request $request, $eventId)
    {
        $event = Event::find($eventId);

        if (!$event) {
            return ResponseFormatter::error(null, 'Event not found', 404);
        }

        $user = $request->user();

        if (!$user) {
            return ResponseFormatter::error(null, 'User not authenticated', 401);
        }

        // Cek apakah pengguna terdaftar pada event tertentu
        if ($user->registeredEvents()->where('events.id', $eventId)->exists()) {
            return ResponseFormatter::success(['status' => 'registered'], 'User has registered for the event');
        } else {
            return ResponseFormatter::success(['status' => 'pending'], 'User has not registered for the event');
        }
    }

    public function getRegisteredUsers(Request $request, $eventId)
    {
        $event = Event::find($eventId);
        $userId = $request->input('user_id'); // Ubah 'id' menjadi 'user_id'

        if (!$event) {
            return ResponseFormatter::error(null, 'Event not found', 404);
        }

        if ($userId) {
            $registeredUsers = $event->registeredUsers()
                ->select('users.*', 'user_event.id as registration_id', 'user_event.created_at', 'user_event.updated_at')
                ->where('users.id', $userId) // Filter berdasarkan user_id
                ->take(50)
                ->get();

            return ResponseFormatter::success($registeredUsers, 'List of registered users for the event');
        }

        $registeredUsers = $event->registeredUsers()
            ->select('users.*', 'user_event.id as registration_id', 'user_event.created_at', 'user_event.updated_at')
            ->take(50)
            ->get();

        return ResponseFormatter::success($registeredUsers, 'List of registered users for the event');
    }
}
