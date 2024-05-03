<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VerificationController extends Controller
{
    public function email_verification($id, $verification_token)
    {
        $user = User::whereId($id)->whereVerificationToken($verification_token)->first();

        if (!$user) {

            return response()->json([
                'message' => 'User email not found!',
            ], 404);
        }
        $roles =  $user->getRoleNames();
        if ($roles->contains('Client')) {
            $user->email_verified_at = Carbon::now();
            $user->verification_token = '';
            $user->is_active = true;
        } else if ($roles->contains('Partner')) {
            $user->email_verified_at = Carbon::now();
            $user->verification_token = '';
            $user->is_active = false;
        } else {
            $user->email_verified_at = Carbon::now();
            $user->verification_token = '';
            $user->is_active = true;
        }
        $user->save();

        return response()->json([
            'message' => 'User email verified!',
        ], 200);
    }
}
