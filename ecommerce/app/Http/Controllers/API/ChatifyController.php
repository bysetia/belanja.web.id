<?php

namespace App\Http\Controllers\API;

use App\Models\ChMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Helpers\ResponseFormatter;
use Chatify\Facades\ChatifyMessenger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Pusher\Pusher;

class ChatifyController extends Controller
{
    public function getMessages(Request $request)
    {
        try {
            $query = ChMessage::with(['fromUser.store', 'toUser.store']);
    
            // Mengambil parameter filter dari permintaan
            $fromId = $request->query('from_id');
            $toId = $request->query('to_id');
            $messageId = $request->query('message_id');
            $last = $request->query('last'); // Tambahkan filter "last"
    
            // Jika ada parameter "from_id", filter berdasarkan from_id
            if ($fromId) {
                $query->where('from_id', $fromId);
            }
    
            // Jika ada parameter "to_id", filter berdasarkan to_id
            if ($toId) {
                $query->where('to_id', $toId);
            }
    
            // Jika ada parameter "message_id", filter berdasarkan message_id
            if ($messageId) {
                $query->where('id', $messageId);
            }
    
            // Jika parameter "last" diberikan, ambil pesan terbaru dengan id terbesar
            if ($last) {
                $query->orderBy('created_at', 'desc')->limit(1);
            }
            
            
            $query->orderBy('created_at', 'asc');
    
            // Mengambil daftar pesan berdasarkan filter yang diterapkan
            $messages = $query->get();
    
            // Menggunakan ResponseFormatter Anda untuk menyusun respons
            return ResponseFormatter::success(
                $messages,
                'Berhasil mendapatkan daftar pesan'
            );
        } catch (\Exception $e) {
            // Menggunakan ResponseFormatter Anda untuk menyusun respons gagal
            return ResponseFormatter::error(
                null,
                'Gagal mendapatkan daftar pesan',
                $e->getMessage()
            );
        }
    }

     public function sendMessage(Request $request)
    {
        try {
            // Mendapatkan data pesan dari permintaan
            $messageData = $request->only('sender_id', 'receiver_id', 'message');
    
            // Mengirim pesan menggunakan ChatifyMessenger
            $message = ChatifyMessenger::sendMessage($messageData);
    
            // Memuat relasi "user" dan "store" dari pengirim pesan (fromUser) dan penerima pesan (toUser)
            $message->load(['fromUser.store', 'toUser.store']);
    
            // Inisialisasi koneksi Pusher
            $pusher = new Pusher(
                '5e4f457cbc9817b9c2c8',
                '45c14c7c2757bf940036',
                '1607654',
                [
                    'cluster' => 'ap2',
                    'useTLS' => true,
                ]
            );

            // Mengirim data pesan ke Pusher
            $pusher->trigger('chat-channel', 'new-message', [
                'message' => $message,
            ]);

            // Menggunakan ResponseFormatter Anda untuk menyusun respons
            return ResponseFormatter::success(
                $message,
                'Pesan berhasil dikirim'
            );
        } catch (\Exception $e) {
            // Menggunakan ResponseFormatter Anda untuk menyusun respons gagal
            return ResponseFormatter::error(
                null,
                'Gagal mengirim pesan',
                $e->getMessage()
            );
        }
    }

        
    public function deleteChat($id)
    {
        try {
            // Cari pesan berdasarkan UUID
            $message = ChMessage::where('id', $id)->first();

            if (!$message) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Message not found',
                ], 404);
            }

            // Hapus pesan
            $message->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Message deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete message',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
        public function getChattedUsers(Request $request, $userId)
        {
            try {
                // Mendapatkan parameter filter dari permintaan
                $fromId = $request->query('from_id');
                $toId = $request->query('to_id');
                $messageId = $request->query('message_id');
                $last = $request->query('last');
        
                $query = User::whereIn('id', function ($query) use ($userId) {
                    $query->select(DB::raw('IF(from_id = '. $userId .', to_id, from_id) as user_id'))
                        ->from('ch_messages')
                        ->where('from_id', $userId)
                        ->orWhere('to_id', $userId)
                        ->groupBy('user_id');
                });
        
                // Jika ada parameter "from_id", filter berdasarkan from_id
                if ($fromId) {
                    $query->where('id', $fromId);
                }
        
                // Jika ada parameter "to_id", filter berdasarkan to_id
                if ($toId) {
                    $query->where('id', $toId);
                }
        
                // Tambahkan filter lainnya sesuai kebutuhan
        
                $chattedUsers = $query->where('id', '<>', $userId)
                    ->with('store')
                    ->get();
        
                foreach ($chattedUsers as $user) {
                    $latestMessage = ChMessage::where(function ($query) use ($user, $userId) {
                        $query->where('from_id', $userId)
                              ->where('to_id', $user->id);
                    })->orWhere(function ($query) use ($user, $userId) {
                        $query->where('from_id', $user->id)
                              ->where('to_id', $userId);
                    })->orderBy('created_at', 'desc')
                      ->first();
        
                    if ($latestMessage) {
                        $user->latest_message = [
                            'user_id' => $latestMessage->fromUser->id,
                            'user' => $latestMessage->fromUser->name,
                            'message' => $latestMessage->body,
                            'created_at' => $latestMessage->created_at,
                        ];
                    } else {
                        $user->latest_message = null;
                    }
                }
                
                $pusher = new Pusher(
                    '5e4f457cbc9817b9c2c8',
                    '45c14c7c2757bf940036',
                    '1607654',
                    [
                        'cluster' => 'ap2',
                        'useTLS' => true,
                    ]
                );
        
                // Kirim data chattedUsers ke Pusher
                $pusher->trigger('chatted-users', 'chatted-users-event', [
                    'chattedUsers' => $chattedUsers,
                ]);
        
                // Menggunakan ResponseFormatter Anda untuk menyusun respons
                return ResponseFormatter::success(
                    $chattedUsers,
                    'Berhasil mendapatkan daftar pengguna yang sudah pernah dichat'
                );
            } catch (\Exception $e) {
                // Menggunakan ResponseFormatter Anda untuk menyusun respons gagal
                return ResponseFormatter::error(
                    null,
                    'Gagal mendapatkan daftar pengguna yang sudah pernah dichat',
                    $e->getMessage()
                );
            }
        }


    public function startChat(Request $request, $user_id)
    {
        try {
            // Retrieve the user by their ID (store owner)
            $user = User::findOrFail($user_id);
    
            // Start a chat conversation with the store owner as the receiver
            $messageData = [
                'from_id' => Auth::user()->id,
                'to_id' => $user_id,
                'body' => null, // Set body to null as you don't want to add a message
            ];
            
            // Create a new chat message entry
            $message = ChMessage::create($messageData);
    
            // Load user details for the sender and receiver users
            $user->load(['store']);
    
            // Retrieve all chat messages between the authenticated user and the store owner
            $chatMessages = ChMessage::where(function ($query) use ($user) {
                $query->where('from_id', Auth::user()->id)
                      ->where('to_id', $user->id)
                      ->orWhere('from_id', $user->id)
                      ->where('to_id', Auth::user()->id);
            })->get();
    
            // Construct the response using the ResponseFormatter
            $response = ResponseFormatter::success([
                'user' => $user,
                'chat_list' => [
                    'sender_id' => Auth::user()->id,
                    'receiver_id' => $user->id,
                ],
            ], 'Percakapan baru berhasil dimulai');
    
            return $response;
        } catch (\Exception $e) {
            // Handle the error using the ResponseFormatter
            $response = ResponseFormatter::error('Gagal memulai percakapan baru', $e->getMessage());
    
            return $response->setStatusCode(500);
        }
    }
    
public function getChatMessages(Request $request, $user_id)
{
    try {
        // Make sure the user with $user_id exists in the database
        $otherUser = User::findOrFail($user_id);

        // Load authenticated user's store
        $currentUser = Auth::user();
        $currentUser->load('store');

        // Load other user's store
        $otherUser->load('store');

        // Retrieve all messages from the conversation between two users
        $messages = ChMessage::where(function ($query) use ($user_id, $currentUser) {
            $query->where('from_id', $currentUser->id)
                  ->where('to_id', $user_id);
        })->orWhere(function ($query) use ($user_id, $currentUser) {
            $query->where('from_id', $user_id)
                  ->where('to_id', $currentUser->id);
        })->orderBy('created_at', 'asc')
          ->get();

        // Organize the response in the desired format
        $formattedMessages = [];
        foreach ($messages as $message) {
            if ($message->body !== null) {
                $formattedMessages[] = [
                    'user_id' => $message->fromUser->id,
                    'message' => $message->body,
                    'created' => $message->created_at,
                ];
            }
        }

        // Determine the latest message and its created timestamp
        $latestMessage = collect($formattedMessages)->isEmpty() ? null : collect($formattedMessages)->last();
        $latestTimestamp = $latestMessage ? $latestMessage['created'] : null;

        // Create the response array
        $response = [
            'messages' => $formattedMessages,
            'last_message' => [
                'message' => $latestMessage ? $latestMessage['message'] : null,
                'created' => $latestTimestamp,
            ],
            'other_user' => [
                'user_id' => $otherUser->id,
                'user_data' => $otherUser,
            ],
            'current_user' => [
                'user_id' => $currentUser->id,
                'user_data' => $currentUser,
            ],
        ];

        // Trigger the Pusher event
        $pusher = new Pusher(
            '5e4f457cbc9817b9c2c8',
            '45c14c7c2757bf940036',
            '1607654',
            [
                'cluster' => 'ap2',
                'useTLS' => true,
            ]
        );

        $pusher->trigger('chat', 'new-chat-messages-event', [
            'response' => $response,
        ]);

        // Use Laravel's response() function to return the JSON response
        return ResponseFormatter::success(
            $response,
            'Successfully retrieved chat messages'
        );
    } catch (\Exception $e) {
        // Use Laravel's response() function to return the JSON error response
        return ResponseFormatter::error(
            null,
            'Failed to retrieve chat messages',
            $e->getMessage(),
            500
        );
    }
}


}
