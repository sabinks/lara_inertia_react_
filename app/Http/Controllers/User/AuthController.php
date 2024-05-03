<?php

namespace App\Http\Controllers\User;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Passport\Client;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Validator;


class AuthController extends Controller
{
    private $client;

    public function __construct()
    {
        $this->client = Client::where('password_client', true)->first();
    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'required|email|lowercase|regex:/^\S*$/u',
            'password' => 'required',
        ]);

        $user = User::whereEmail($request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'User record not found!',
            ], 404);
        }
        if ($user->verification_token) {

            return response()->json([
                'message' => 'User email verification pending, please check your email!',
            ], 403);
        }

        if (!$user->is_active) {

            return response()->json([
                'message' => 'User not authorized or deactivated, please contact admin@synergydorm.com for further information!',
            ], 403);
        }
        if (!auth()->attempt($loginData)) {
            return response(['message' => 'Authentication Failed'], 401);
        }
        try {
            $success = [
                'grant_type' => 'password',
                'client_id' => $this->client->id,
                'client_secret' => $this->client->secret,
                'username' => $request->email,
                'password' => $request->password,
                'scope' => '*',
            ];
            $tokenRequest = $request->create('/oauth/token', 'POST', $request->all());
            $request->request->add($success);

            $response = Route::dispatch($tokenRequest);
            $json = (array) json_decode($response->getContent());
            $json['name'] = $user->name;
            $json['email'] = $user->email;
            $json['user_id'] = $user->id;
            $roles = $user->getRoleNames();
            if ($roles->contains('Student')) {
                $json['details'] = $user->student()->first();
                $json['path'] =  '/students/profile_image/';
            }
            $json['domain'] = Config::get('app.url');
            $json['role'] = $roles[0];
            $permissions = [];
            foreach ($roles as $key => $role) {
                $user_role = Role::findByName($role, 'api');
                $permissions = [...$permissions, ...$user_role->permissions->pluck('name')];
            }
            $json['permissions'] =  $permissions;
            $response->setContent(json_encode($json));

            return $response;
        } catch (\Throwable $th) {
            return $th;
        }
    }

    public function refreshToken(Request $request)
    {
        $this->validate($request, [
            'refresh_token' => 'required',
        ]);
        $success = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refresh_token,
            'client_id' => $this->client->id,
            'client_secret' => $this->client->secret,
            'scope' => '*',
        ];
        $request->request->add($success);
        $proxy = Request::create('oauth/token', 'POST');

        return Route::dispatch($proxy);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response([
            'message' => 'Successfully Logged Out'
        ], 200);
    }
    // public function logout(Request $request)
    // {
    //     $accessToken = Auth::user()->token();
    //     DB::table('oauth_refresh_tokens')
    //         ->where('access_token_id', $accessToken->id)
    //         ->update(['revoked' => true]);
    //     $accessToken->revoke();

    //     return response([
    //         'message' => 'Successfully Logged Out'
    //     ], 205);
    // }

    public function userDetail(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'user' => $user
        ]);
    }

    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|lowercase|regex:/^\S*$/u',
            ]);
            if ($validator->fails()) {

                return response($validator->errors(), 422);
            }

            $status = Password::sendResetLink(
                $request->only('email')
            );
            if ($status == 'passwords.sent') {
                return response()->json([
                    'message' => 'Password reset link sent, please check your email address for password reset link!'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Password Forgot: Error occured!'
                ], 200);
            }
        } catch (\Throwable $th) {
            // throw $th;
            Log::info(json_encode($th));
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'email' => 'required|email|lowercase|regex:/^\S*$/u',
                'password' => 'required|min:8'
            ]);
            if ($validator->fails()) {

                return response($validator->errors(), 422);
            }
            $status = Password::reset(
                $request->only('email', 'password', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->setRememberToken(Str::random(60));

                    $user->save();
                    event(new PasswordReset($user));
                }
            );
            if ($status == 'passwords.reset') {
                return response()->json([
                    'message' => 'Password reset completed, please login using new credentials!'
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Password Reset: Error occured!'
                ], 200);
            }
        } catch (\Throwable $th) {
            // throw $th;
            Log::info(json_encode($th));
        }
    }
    public function changePassword(Request $request)
    {
        $user = Auth::user();
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|confirmed|min:8',
                'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        return $fail(__('The current password is incorrect.'));
                    }
                }],
            ]);
            if ($validator->fails()) {

                return response($validator->errors(), 422);
            }

            $data = $request->only('password');
            User::whereId(Auth()->user()->id)->update([
                'password' => Hash::make($data['password'])
            ]);
            return response()->json([
                'message' => 'Password changed, please login again!'
            ], 200);
        } catch (\Throwable $th) {
            // throw $th;
            Log::info(json_encode($th));
        }
    }
    public function getRolePermissions()
    {
        $user_id = Auth::user()->id;
        $user = User::whereId($user_id)->first();
        $response['name'] = $user->name;
        $response['email'] = $user->email;
        $response['user_id'] = $user->id;
        $roles = $user->getRoleNames();
        if ($roles->contains('Client')) {
            $response['details'] = $user->student()->first();
            $response['path'] =  '/students/profile_image/';
        }
        $response['domain'] = Config::get('app.url');
        $response['role'] = $roles[0];
        $permissions = [];
        foreach ($roles as $key => $role) {
            $user_role = Role::findByName($role, 'api');
            $permissions = [...$permissions, ...$user_role->permissions->pluck('name')];
        }
        $response['permissions'] =  $permissions;

        return $response;
    }
    public function getUser()
    {
        $user_id = Auth::user()->id;
        $user = User::whereId($user_id)->first();
        $response['name'] = $user->name;
        $response['email'] = $user->email;
        $response['user_id'] = $user->id;
        $response['is_active'] = $user->is_active;
        $response['data'] = $user->data;
        $response['profile_image'] = $user->profile_image;
        $response['image_path'] = Config::get('app.url') . '/storage/profile_image/';
        $roles = $user->getRoleNames();
        $response['role'] = $roles[0];

        return $response;
    }
}
