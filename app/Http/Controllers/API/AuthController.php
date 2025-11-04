<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Mutation;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // telegram_id,username,first_name,last_name
        $telegram_id = $request->telegram_id;
        $username = $request->username;
        $password = bcrypt('password123');
        $name = $request->first_name . ' ' . $request->last_name;
        $email = $username."@masricky.com";

        if(!$telegram_id || !$username || !$request->first_name) {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], 400);
        }

        $user = User::where('telegram_id', $telegram_id)->first();
        if($user) {
            return response()->json([
                'success' => false,
                'message' => 'User already registered',
                'data' => $user->toArray()
                ],200);
            } else {
            $newUser = User::create([
                'telegram_id' => $telegram_id,
                'username' => $username,
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);
            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $newUser->toArray()
            ], 201);
        }
    }

    public function getFromTelegram(Request $request)
    {
        $telegram_id = $request->telegram_id;
        if(!$telegram_id) {
            return response()->json([
                'success' => false,
                'message' => 'Missing telegram_id'
            ], 400);
        }

        $user = User::where('telegram_id', $telegram_id)->first();
        if($user) {
            return response()->json([
                'success' => true,
                'data' => $user->toArray()
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }

    public function getMutasi(Request $request)
    {
        $telegram_id = $request->telegram_id;
        if(!$telegram_id) {
            return response()->json([
                'success' => false,
                'message' => 'Missing telegram_id'
            ], 400);
        }

        $user = User::where('telegram_id', $telegram_id)->first();
        if($user) {
            $mutation = Mutation::where('user_id', $user->id)->get();
            if(!$mutation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum ada mutasi'
                ], 404);
            }else{
            return response()->json([
                'success' => true,
                'data' => $mutation
            ], 200);
            
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
    }
}
