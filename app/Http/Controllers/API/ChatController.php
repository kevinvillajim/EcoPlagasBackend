<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    // Admin endpoints
    public function getConversations(Request $request): JsonResponse
    {
        try {
            $query = Chat::with(['client', 'latestMessage.sender'])
                ->leftJoin('messages', function($join) {
                    $join->on('chats.id', '=', 'messages.chat_id')
                         ->whereRaw('messages.id = (SELECT MAX(id) FROM messages WHERE chat_id = chats.id)');
                })
                ->select('chats.*', 'messages.message as last_message', 'messages.created_at as last_message_time')
                ->orderBy('messages.created_at', 'desc');

            if ($request->has('unread_only') && $request->unread_only) {
                $query->whereHas('messages', function($q) {
                    $q->where('read', false);
                });
            }

            $conversations = $query->get()->map(function($chat) {
                return [
                    'id' => $chat->id,
                    'client_name' => $chat->client->name,
                    'client_email' => $chat->client->email,
                    'status' => $chat->status,
                    'lastMessage' => $chat->last_message,
                    'lastMessageTime' => $chat->last_message_time,
                    'unreadCount' => $chat->messages()->where('read', false)->count()
                ];
            });

            return response()->json([
                'success' => true,
                'conversations' => $conversations,
                'debug' => [
                    'total_chats' => $conversations->count(),
                    'chat_ids' => $conversations->pluck('id'),
                    'client_names' => $conversations->pluck('client_name')
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading conversations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getChatMessages(Request $request, $chatId): JsonResponse
    {
        try {
            $chat = Chat::with(['messages.sender'])->findOrFail($chatId);
            
            $messages = $chat->messages->map(function($message) {
                return [
                    'id' => $message->id,
                    'chat_id' => $message->chat_id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'message' => $message->message,
                    'type' => $message->type,
                    'read' => $message->read,
                    'created_at' => $message->created_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'messages' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendAdminMessage(Request $request, $chatId): JsonResponse
    {
        try {
            $request->validate([
                'message' => 'required|string|max:2000',
                'type' => 'sometimes|in:text,file,image'
            ]);

            $chat = Chat::findOrFail($chatId);
            
            $message = Message::create([
                'chat_id' => $chatId,
                'sender_id' => Auth::id(),
                'message' => $request->message,
                'type' => $request->type ?? 'text',
                'read' => false
            ]);

            $chat->update(['last_activity' => now()]);

            $messageData = [
                'id' => $message->id,
                'chat_id' => $message->chat_id,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender->name,
                'message' => $message->message,
                'type' => $message->type,
                'read' => $message->read,
                'created_at' => $message->created_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'message' => $messageData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markChatAsRead(Request $request, $chatId): JsonResponse
    {
        try {
            $chat = Chat::findOrFail($chatId);
            $chat->markMessagesAsRead();

            return response()->json([
                'success' => true,
                'message' => 'Messages marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking messages as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateChatStatus(Request $request, $chatId): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:open,closed,pending'
            ]);

            $chat = Chat::findOrFail($chatId);
            $chat->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => 'Chat status updated'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating chat status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function searchConversations(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q');
            
            $conversations = Chat::with(['client', 'latestMessage.sender'])
                ->whereHas('client', function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                })
                ->orWhereHas('messages', function($q) use ($query) {
                    $q->where('message', 'like', "%{$query}%");
                })
                ->get()
                ->map(function($chat) {
                    $lastMessage = $chat->latestMessage->first();
                    return [
                        'id' => $chat->id,
                        'client_name' => $chat->client->name,
                        'client_email' => $chat->client->email,
                        'status' => $chat->status,
                        'lastMessage' => $lastMessage?->message,
                        'lastMessageTime' => $lastMessage?->created_at,
                        'unreadCount' => $chat->unreadMessagesCount()
                    ];
                });

            return response()->json([
                'success' => true,
                'conversations' => $conversations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching conversations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Client endpoints
    public function getClientChat(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Get or create chat for client
            $chat = Chat::firstOrCreate(
                ['client_id' => $user->id],
                ['status' => 'open', 'last_activity' => now()]
            );

            $messages = $chat->messages()->with('sender')->get()->map(function($message) {
                return [
                    'id' => $message->id,
                    'chat_id' => $message->chat_id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'message' => $message->message,
                    'type' => $message->type,
                    'read' => $message->read,
                    'created_at' => $message->created_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'messages' => $messages,
                'chat_id' => $chat->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading chat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getClientMessages(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $chat = Chat::where('client_id', $user->id)->first();

            if (!$chat) {
                return response()->json([
                    'success' => true,
                    'messages' => []
                ]);
            }

            $messages = $chat->messages()->with('sender')->get()->map(function($message) {
                return [
                    'id' => $message->id,
                    'chat_id' => $message->chat_id,
                    'sender_id' => $message->sender_id,
                    'sender_name' => $message->sender->name,
                    'message' => $message->message,
                    'type' => $message->type,
                    'read' => $message->read,
                    'created_at' => $message->created_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'messages' => $messages
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading messages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendClientMessage(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'message' => 'required|string|max:2000',
                'type' => 'sometimes|in:text,file,image'
            ]);

            $user = Auth::user();
            
            // Get or create chat for client
            $chat = Chat::firstOrCreate(
                ['client_id' => $user->id],
                ['status' => 'open', 'last_activity' => now()]
            );

            $message = Message::create([
                'chat_id' => $chat->id,
                'sender_id' => $user->id,
                'message' => $request->message,
                'type' => $request->type ?? 'text',
                'read' => false
            ]);

            $chat->update(['last_activity' => now()]);

            $messageData = [
                'id' => $message->id,
                'chat_id' => $message->chat_id,
                'sender_id' => $message->sender_id,
                'sender_name' => $message->sender->name,
                'message' => $message->message,
                'type' => $message->type,
                'read' => $message->read,
                'created_at' => $message->created_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'message' => $messageData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function markClientChatAsRead(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $chat = Chat::where('client_id', $user->id)->first();

            if ($chat) {
                $chat->markMessagesAsRead();
            }

            return response()->json([
                'success' => true,
                'message' => 'Messages marked as read'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error marking messages as read',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Typing indicators
    public function sendTypingIndicator(Request $request, $chatId = null): JsonResponse
    {
        try {
            $request->validate([
                'is_typing' => 'required|boolean'
            ]);

            // For now, just return success - real-time typing would need WebSocket/Pusher
            return response()->json([
                'success' => true,
                'message' => 'Typing indicator sent'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error sending typing indicator',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function sendClientTypingIndicator(Request $request): JsonResponse
    {
        return $this->sendTypingIndicator($request);
    }

    // SSE Streaming endpoints - SIMPLIFIED VERSION
    public function adminChatStream(Request $request)
    {
        // Return proper SSE format with simple ping
        return response("data: " . json_encode([
            'type' => 'ping',
            'timestamp' => now()->toISOString(),
            'message' => 'Connection established'
        ]) . "\n\n", 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'Access-Control-Allow-Origin' => 'http://localhost:5173',
            'Access-Control-Allow-Credentials' => 'true'
        ]);
    }

    public function clientChatStream(Request $request)
    {
        // Return proper SSE format with simple ping
        return response("data: " . json_encode([
            'type' => 'ping',
            'timestamp' => now()->toISOString(),
            'message' => 'Connection established'
        ]) . "\n\n", 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'Access-Control-Allow-Origin' => 'http://localhost:5173',
            'Access-Control-Allow-Credentials' => 'true'
        ]);
    }
}
