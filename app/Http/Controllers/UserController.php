<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'profile']]);
    }

    public function register(Request $request){

        $validator = Validator::make($request -> all(), [
            'email' => 'required|string|email|unique:users',
            'firstname' => 'required',
            'grade' => 'required',
            'password' => 'required|string|min:6',
            'phone' => 'required|unique:users',
            'school' => 'required',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $user = User::create(array_merge(
            $validator->validate(),
            ['password' => bcrypt($request->password)]
        ));
        return response()->json([
            'message' => 'User Successfully registered',
            'user' => $user
        ], 201);

    }


    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);

        if($validator->fails()){
            return response() ->json($validator->errors(), 422);
        }
        if(!$token=auth()->attempt($validator->validated())){
            return response() ->json(['error'=>'Unauthorized'], 422);
        }

        return $this->createNewToken($token);
    }

    public function createNewToken($token){

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 1440 * 60, //for 60 days
            'user' => auth()->user()
        ]);
    }

    public function profile() {
        return response()->json(auth()->user());
    }
}
