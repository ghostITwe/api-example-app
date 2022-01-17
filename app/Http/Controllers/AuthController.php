<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string",
            "email" => "required|email|unique:users",
            "password" => "required|min:8|string|confirmed"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => "Invalid Registration data",
                'errors' => $validator->errors()
            ], 401);
        }

        $user = new User();
        $user->name = $request['name'];
        $user->email = $request['email'];
        $user->password = Hash::make($request['password']);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => ""
        ], 200);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails() || !auth()->attempt($request->only('name', 'password'))) {
            return response()->json([
                'status' => false,
                'message' => "Invalid authorization data"
            ])->setStatusCode(401, 'Invalid authorization data');
        }

        $user = User::query()->where('name', $request['name'])->firstOrFail();

        $token = $user->createToken("auth_token")->plainTextToken;

        return response()->json([
            'status' => true,
            'token' => 'bearer-'.$token
        ])->setStatusCode(200, 'Successful authorization');
    }

    public function logout(Request $request)
    {
       $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout'
        ]);
    }
}
