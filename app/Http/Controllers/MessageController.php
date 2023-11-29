<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Image;
use App\Http\Requests\Message\StoreRequest;
use App\Http\Resources\Image\ImageResource;
use App\Http\Resources\Message\MessageResource;
use App\Models\Chat;
use App\Models\Message;
use App\Models\MessageStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    function store(StoreRequest $request, Chat $chat) {
        $data = $request->validated();
        $images = null;
        if (isset($data['images'])) {
            $images = $data['images'];
            unset($data['images']);
        }
        try {
            DB::beginTransaction();
            $message = Message::create([
                'chat_id' => $data['chat_id'],
                'user_id' => auth()->id(),
                'content' => $data['content'],
            ]);
            if (!is_null($images)) {
                foreach ($images as $image) {
                    $name = md5(Carbon::now() . '_' . $image->getClientOriginalName()) . '.' . $image->getClientOriginalExtension();
                    $filePath = Storage::disk('public')->putFileAs('/images', $image, $name);
                    Image::create([
                        'path' => $filePath,
                        'name' => url('/storage/' . $filePath),
                        'message_id' => $message->id,
                    ]);
                }
            }
            foreach ($data['user_ids'] as $user_id) {
                MessageStatus::create([
                    'chat_id' => $data['chat_id'],
                    'message_id' => $message->id,
                    'user_id' => $user_id
                ]);
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ]);
        }
    
        if (!is_null($images)) {
            $messageResource = MessageResource::make($message)->additional(['images' => ImageResource::collection($images)]);
            return $messageResource->resolve();
        } else {
            return MessageResource::make($message)->resolve();
        }
    }
}
