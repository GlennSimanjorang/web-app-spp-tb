<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Formatter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;

class AuthController extends Controller
{
    public function signIn(Request $request){
        $validator = Validator::make($request->all(), [
            "email" => "required|string",
            "password" => "required|string",
        ]);
        try{
            if($validator->fails()){
                return Formatter::apiResponse(422, "Validation errors", null, $validator->errors());
            }
            $validated = $validator->validated();
            $user = null;

            if(isset($validated["email"])){
                $user = User::query()->where("email", $validated["email"])->first();
            } else {
                return Formatter::apiResponse(400, "Missing input. Please try again");
            }
            if(!$user){
                return Formatter::apiResponse(404, "User not found");
            }
            if (!Hash::check($validated["password"], $user->password)) {
                return Formatter::apiResponse(400, "Credentials not matched. Please try again");
            }
            $expiration = Carbon::now()->addDays();
            $token = $user->createToken("auth_token", ["*"], $expiration)->plainTextToken;
            return Formatter::apiResponse(200, "Successfully logged in", [
                "token" => $token,
                "expired_time" => 86400
            ]);
        }catch (\Illuminate\Validation\ValidationException $exception) {
            return Formatter::apiResponse(422, "Validation error", null, $exception->errors());
        }
    }
    public function signOut()
    {
        Auth::guard("sanctum")->user()->tokens()->delete();
        return Formatter::apiResponse(200, "Logged out");
    }

    public function self()
    {
        $user = Auth::guard("sanctum")->user();
        return Formatter::apiResponse(200, "Self auth data received", $user);
    }
}
