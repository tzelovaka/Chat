<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Chat\StoreRequest;
use App\Http\Resources\Chat\ChatResource;
use App\Http\Resources\Message\MessageResource;
use App\Http\Resources\User\UserResource;
use App\Models\Chat;
use App\Models\User;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index() {
        $users = User::where('id', '!=', auth()->id())->get();
        $users = UserResource::collection($users)->resolve();
        $chats = auth()->user()->chats()->has('messages')->get();
        $chats = ChatResource::collection($chats)->resolve();
        return inertia('Chat/Index', compact('users', 'chats'));
    }

    public function store(StoreRequest $request) {
        $data = $request->validated();
        $userIds = array_merge($data['users'], [auth()->id()]);
        sort($userIds);
        $userIdsStr = implode('-', $userIds);
        try {
            DB::beginTransaction();

            $chat = Chat::firstOrCreate([
                'users' => $userIdsStr
            ], [
                'title' => $data['title']
            ]);

            $chat->user()->sync($userIds);

            DB::commit();

        } catch (\Exception $exception) {
            DB::rollBack();
        }
        return redirect()->route('chats.show', $chat->id);
    }

    function show(Chat $chat) {
        $all_users = User::where('id', '!=', auth()->id())->get();
        $all_users = UserResource::collection($all_users)->resolve();
        $chats = auth()->user()->chats()->has('messages')->get();
        $users = $chat->user()->get();
        $chatIds = collect($chats)->pluck('id')->toArray();
        $latest_messages = Message::whereIn('chat_id', $chatIds)
        ->latest('created_at')
        ->get()
        ->groupBy('chat_id')
        ->map(function ($group) {
        return $group->first();
        })
        ->values()
        ->toArray();
        $chats = ChatResource::collection($chats)->resolve();
        $messages = $chat->messages()->with('user')->get();
        $messages->transform(function ($message) {
            $imageNames = $message->image()->pluck('name')->all();
        
            $message->message .= implode(', ', $imageNames);
        
            return $message;
        });
        $messages = MessageResource::collection($messages)->resolve();
        $users = UserResource::collection($users)->resolve();
        $chat = ChatResource::make($chat)->resolve();
        $auth_id = auth()->id();
//        dd($latest_messages, $chats, $all_users);
        return inertia('Chat/Show', compact('chat', 'users', 'messages', 'all_users', 'chats', 'latest_messages', 'auth_id'));
    }
}
