<?php

namespace App\Http\Controllers;

use App\Exceptions\UserNotFoundException;
use App\Exceptions\ValidationErrorException;
use App\Http\Resources\Friend as ResourcesFriend;
use App\Models\Friend;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FriendRequestController extends Controller
{
    public function store()
    {
        try {
            $data = request()->validate([
                'friend_id' => ['required'],
            ]);
        } catch (ValidationException $th) {
            throw new ValidationErrorException(json_encode($th->errors()));
        }
        try {
            User::findOrFail($data['friend_id'])
                ->friends()
                ->syncWithoutDetaching(auth()->user());
        } catch (ModelNotFoundException $th) {
            throw new UserNotFoundException();
        }
        return new ResourcesFriend(
            Friend::where('user_id', auth()->user()->id)
                ->where('friend_id', $data['friend_id'])
                ->first()
        );
    }
}
