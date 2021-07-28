<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class RegistrationController extends Controller
{

    public function store(Request $request)
    {

        try {
            // validate user inputs
            $validateInputs = Validator::make($request->all(), [
                'firstname' => 'required|max:225',
                'lastname' => 'required|max:255',
                'email' => 'required|email|max:255',
                'password' => 'required|min:7',
                'confirm_password' => 'same:password',
                'address' => 'required',
                'phonenumber' => 'required|max:15'
            ]);

            if ($validateInputs->fails()) {
                return response()->json([$validateInputs->errors()], 422);
            }

            //create and store user information in the database
            $newUser = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'address' => $request->address,
                'phonenumber' => $request->phonenumber,
            ]);

            // check if the user was created successfully and return the user
            // details excluding password for security reasons

            if (!$newUser) {
                return response()->json([
                    'error' => 'registration failed'
                ], 400);
            } else {
                return response()->json([
                    'message' => 'registration successfully',
                    'Firstname' => $newUser->firstname,
                    'Lasttname' => $newUser->lastname,
                    'email' => $newUser->email,
                    'Address' => $newUser->address,
                    'Phone Number' => $newUser->phonenumber,
                ], 201);
            }
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->json([
                'error' => $e->getMessage()
            ]);
        }
    }
}
