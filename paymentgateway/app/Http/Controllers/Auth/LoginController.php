<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validateLoginInput = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validateLoginInput->fails()) {
            return response()->json($validateLoginInput->errors(), 422);
        }

        if(! $token = auth()->attempt($validateLoginInput->validated()))
        {
            return response()->json(['Auth error' => 'Unauthorized'], 401);
        }

        return $this->generateToken($token);

    }

    protected function generateToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
